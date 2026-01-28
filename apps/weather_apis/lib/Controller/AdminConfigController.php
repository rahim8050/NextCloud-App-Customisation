<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Controller;

use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Service\IntegrationConfig;
use OCA\WeatherApis\Service\LogSanitizer;
use OCA\WeatherApis\Service\WeatherApiClientInterface;
use OCA\WeatherApis\Service\WeatherApiException;
use OCA\WeatherApis\Settings\AdminSettings;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PasswordConfirmationRequired;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Response;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

final class AdminConfigController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private readonly AppConfig $appConfig,
		private readonly IntegrationConfig $integrationConfig,
		private readonly WeatherApiClientInterface $weatherApiClient,
		private readonly IUserSession $userSession,
		private readonly IGroupManager $groupManager,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
	}

	#[AuthorizedAdminSetting(settings: AdminSettings::class)]
	#[PasswordConfirmationRequired]
	public function generateCredentials(): JSONResponse {
		if ($response = $this->ensureAdmin()) {
			return $this->withNoStore($response);
		}

		$clientId = $this->resolveClientId();
		$hmacSecret = $this->generateHmacSecretB64();
		$this->integrationConfig->setCredentials($clientId, $hmacSecret);

		return $this->withNoStore(new JSONResponse([
			'status' => 'ok',
			'ok' => true,
			'message' => 'Generated credentials. Shown once.',
			'clientId' => $clientId,
			'hmacSecret' => $hmacSecret,
		]));
	}

	#[AuthorizedAdminSetting(settings: AdminSettings::class)]
	#[PasswordConfirmationRequired]
	public function rotateHmac(): JSONResponse {
		if ($response = $this->ensureAdmin()) {
			return $this->withNoStore($response);
		}

		$clientId = $this->resolveClientId();
		$hmacSecret = $this->generateHmacSecretB64();
		$this->integrationConfig->setCredentials($clientId, $hmacSecret);

		return $this->withNoStore(new JSONResponse([
			'status' => 'ok',
			'ok' => true,
			'message' => 'Rotated secret. Shown once.',
			'clientId' => $clientId,
			'hmacSecret' => $hmacSecret,
		]));
	}

	#[AuthorizedAdminSetting(settings: AdminSettings::class)]
	public function getConfig(): JSONResponse {
		if ($response = $this->ensureAdmin()) {
			return $response;
		}

		$clientId = $this->integrationConfig->getClientIdOrNull() ?? '';
		$hmacSecretSet = $this->integrationConfig->getSecretB64OrNull() !== null;

		return new JSONResponse([
			'baseUrl' => $this->appConfig->getBaseUrl(),
			'clientId' => $clientId,
			'timeoutSeconds' => $this->appConfig->getTimeoutSeconds(),
			'devAllowHttp' => $this->appConfig->isDevAllowHttp(),
			'allowlistHosts' => $this->appConfig->getAllowlistHosts(),
			'hasApiKey' => $this->appConfig->hasApiKey(),
			'hasHmacSecret' => $hmacSecretSet,
			'hmacRotation' => [
				'hasPrevious' => false,
				'previousExpiresAt' => null,
			],
			'integrationStatus' => $this->integrationConfig->getStatus(),
		]);
	}

	#[AuthorizedAdminSetting(settings: AdminSettings::class)]
	public function testConnection(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$user = $this->userSession->getUser();
		if ($user === null || !$this->groupManager->isAdmin($user->getUID())) {
			return $this->buildTestConnectionError('forbidden', 'Admin access required.', Http::STATUS_FORBIDDEN);
		}

		$status = $this->integrationConfig->getStatus();
		$message = $status['message'] ?? 'Configuration status unavailable.';

		if (!($status['ok'] ?? false)) {
			$this->logger->info(
				'Weather API integration config is invalid',
				LogSanitizer::sanitizeContext([
					'code' => $status['code'] ?? 'unknown',
					'requestId' => $requestId,
					'legacyPresent' => $status['legacyPresent'] ?? false,
				]),
			);

			return $this->buildTestConnectionError(
				(string)($status['code'] ?? 'invalid_argument'),
				$message,
				Http::STATUS_BAD_REQUEST,
			);
		}

		try {
			$expiresIn = $this->weatherApiClient->testConnection($requestId);
		} catch (WeatherApiException $exception) {
			$code = $exception->getErrorCode();
			$this->logger->warning(
				'Weather API test connection failed',
				LogSanitizer::sanitizeContext([
					'requestId' => $requestId,
					'code' => $code,
					'reason' => $exception->getReason() ?? '',
				]),
			);
			return $this->buildTestConnectionError(
				$code,
				$exception->getMessage(),
				$this->httpStatusForCode($code),
			);
		} catch (\Throwable $exception) {
			$this->logger->warning(
				'Weather API test connection failed',
				LogSanitizer::sanitizeContext([
					'requestId' => $requestId,
					'code' => 'backend_error',
				]),
			);
			return $this->buildTestConnectionError(
				'backend_error',
				'Backend request failed.',
				Http::STATUS_BAD_REQUEST,
			);
		}

		$finalMessage = 'Connection successful.';
		if (!empty($status['warning'])) {
			$finalMessage .= ' Legacy keys detected; remove them after migration.';
		}

		return $this->buildTestConnectionSuccess($finalMessage, $expiresIn);
	}

	#[AuthorizedAdminSetting(settings: AdminSettings::class)]
	public function diagnostics(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$user = $this->userSession->getUser();
		if ($user === null || !$this->groupManager->isAdmin($user->getUID())) {
			return $this->buildErrorResponse('forbidden', 'Admin access required.', $requestId, Http::STATUS_FORBIDDEN);
		}

		$results = [
			'token' => ['ok' => false],
			'status' => ['ok' => false],
			'png' => ['ok' => false],
		];

		$hasFailures = false;

		try {
			$expiresIn = $this->weatherApiClient->testConnection($requestId);
			$results['token'] = [
				'ok' => true,
				'expires_in' => $expiresIn,
			];
		} catch (WeatherApiException $exception) {
			$results['token'] = $this->diagnosticsErrorFromException('token', $exception, $requestId);
			$hasFailures = true;
		} catch (\Throwable $exception) {
			$this->logger->warning(
				'Weather API diagnostics token failed',
				LogSanitizer::sanitizeContext([
					'requestId' => $requestId,
				]),
			);
			$results['token'] = $this->buildDiagnosticsErrorResult('backend_error', 'Backend request failed.');
			$hasFailures = true;
		}

		if (($results['token']['ok'] ?? false) === true) {
			try {
				$statusData = $this->weatherApiClient->nextcloudStatus($requestId);
				$results['status'] = [
					'ok' => true,
					'http' => Http::STATUS_OK,
					'server_time' => is_string($statusData['server_time'] ?? null) ? $statusData['server_time'] : null,
					'version' => is_string($statusData['version'] ?? null) ? $statusData['version'] : null,
					'capabilities' => is_array($statusData['capabilities'] ?? null) ? $statusData['capabilities'] : new \stdClass(),
				];
			} catch (WeatherApiException $exception) {
				$results['status'] = $this->diagnosticsErrorFromException('status', $exception, $requestId);
				$hasFailures = true;
			} catch (\Throwable $exception) {
				$this->logger->warning(
					'Weather API diagnostics status failed',
					LogSanitizer::sanitizeContext([
						'requestId' => $requestId,
					]),
				);
				$results['status'] = $this->buildDiagnosticsErrorResult('backend_error', 'Backend request failed.');
				$hasFailures = true;
			}

			try {
				$this->weatherApiClient->nextcloudPreviewPng($requestId);
				$results['png'] = [
					'ok' => true,
					'http' => Http::STATUS_OK,
				];
			} catch (WeatherApiException $exception) {
				$results['png'] = $this->diagnosticsErrorFromException('png', $exception, $requestId);
				$hasFailures = true;
			} catch (\Throwable $exception) {
				$this->logger->warning(
					'Weather API diagnostics preview failed',
					LogSanitizer::sanitizeContext([
						'requestId' => $requestId,
					]),
				);
				$results['png'] = $this->buildDiagnosticsErrorResult('backend_error', 'Backend request failed.');
				$hasFailures = true;
			}
		} else {
			$results['status'] = $this->buildDiagnosticsErrorResult('skipped', 'Skipped: token step failed.');
			$results['png'] = $this->buildDiagnosticsErrorResult('skipped', 'Skipped: token step failed.');
			$hasFailures = true;
		}

		$message = $hasFailures ? 'Diagnostics completed with failures.' : 'Diagnostics passed.';

		return new JSONResponse([
			'status' => 'ok',
			'ok' => true,
			'message' => $message,
			'data' => $results,
		]);
	}

	#[AuthorizedAdminSetting(settings: AdminSettings::class)]
	#[NoCSRFRequired]
	public function previewPng(): Response {
		$requestId = $this->resolveRequestId();
		$user = $this->userSession->getUser();
		if ($user === null || !$this->groupManager->isAdmin($user->getUID())) {
			return $this->buildErrorResponse('forbidden', 'Admin access required.', $requestId, Http::STATUS_FORBIDDEN);
		}

		try {
			$content = $this->weatherApiClient->nextcloudPreviewPng($requestId);
		} catch (WeatherApiException $exception) {
			$this->logger->warning(
				'Weather API diagnostics preview failed',
				LogSanitizer::sanitizeContext([
					'requestId' => $requestId,
					'code' => $exception->getErrorCode(),
					'reason' => $exception->getReason() ?? '',
				]),
			);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $exception) {
			$this->logger->warning(
				'Weather API diagnostics preview failed',
				LogSanitizer::sanitizeContext([
					'requestId' => $requestId,
					'code' => 'backend_error',
				]),
			);
			return $this->buildErrorResponse(
				'backend_error',
				'Backend request failed.',
				$requestId,
				Http::STATUS_BAD_REQUEST,
			);
		}

		$response = new DataDisplayResponse($content, Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->addHeader('Cache-Control', 'no-store');
		$response->addHeader('Content-Disposition', 'inline; filename="preview.png"');
		return $response;
	}

	private function resolveClientId(): string {
		$clientId = $this->integrationConfig->getClientIdOrNull();
		if ($clientId !== null && $clientId !== '') {
			return $clientId;
		}

		$clientId = $this->generateUuid();
		$this->integrationConfig->setClientId($clientId);

		return $clientId;
	}

	private function generateHmacSecretB64(): string {
		return base64_encode(random_bytes(32));
	}

	private function generateUuid(): string {
		$bytes = random_bytes(16);
		$bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
		$bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
	}

	private function resolveRequestId(): string {
		$header = $this->request->getHeader('X-Request-Id');
		if ($header !== '') {
			return $header;
		}

		return $this->generateUuid();
	}

	/**
	 * @param array<string, mixed>|null $data
	 */
	private function buildStatusResponse(string $status, string $message, ?array $data = null, int $statusCode = Http::STATUS_OK): JSONResponse {
		return new JSONResponse([
			'status' => $status,
			'ok' => $status === 'ok',
			'message' => $message,
			'data' => $data,
		], $statusCode);
	}

	/**
	 * @param array<string, mixed>|null $details
	 */
	private function buildErrorResponse(string $code, string $message, string $requestId, int $status, ?array $details = null): JSONResponse {
		$detailsPayload = $details === null || $details === [] ? new \stdClass() : $details;

		return new JSONResponse([
			'status' => 'error',
			'message' => $message,
			'data' => null,
			'error' => [
				'code' => $code,
				'message' => $message,
				'requestId' => $requestId,
				'details' => $detailsPayload,
			],
		], $status);
	}

	private function buildTestConnectionSuccess(string $message, int $expiresIn): JSONResponse {
		return new JSONResponse([
			'status' => 0,
			'ok' => true,
			'message' => $message,
			'data' => [
				'expires_in' => $expiresIn,
			],
		]);
	}

	private function buildTestConnectionError(string $code, string $message, int $status): JSONResponse {
		return new JSONResponse([
			'status' => 1,
			'ok' => false,
			'message' => $message,
			'code' => $code,
			'data' => null,
		], $status);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function diagnosticsErrorFromException(string $step, WeatherApiException $exception, string $requestId): array {
		$this->logger->warning(
			'Weather API diagnostics step failed',
			LogSanitizer::sanitizeContext([
				'requestId' => $requestId,
				'step' => $step,
				'code' => $exception->getErrorCode(),
				'reason' => $exception->getReason() ?? '',
			]),
		);

		$details = $exception->getDetails();
		$httpStatus = null;
		if (isset($details['httpStatus']) && is_int($details['httpStatus'])) {
			$httpStatus = $details['httpStatus'];
		}

		return $this->buildDiagnosticsErrorResult(
			$exception->getErrorCode(),
			$exception->getMessage(),
			$httpStatus,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function buildDiagnosticsErrorResult(string $code, string $message, ?int $httpStatus = null): array {
		$result = [
			'ok' => false,
			'code' => $code,
			'message' => $message,
		];
		if ($httpStatus !== null) {
			$result['http'] = $httpStatus;
		}

		return $result;
	}

	private function httpStatusForCode(string $code): int {
		return match ($code) {
			'invalid_argument' => Http::STATUS_BAD_REQUEST,
			'unauthorized' => Http::STATUS_UNAUTHORIZED,
			'forbidden' => Http::STATUS_FORBIDDEN,
			'backend_timeout' => Http::STATUS_GATEWAY_TIMEOUT,
			'backend_unavailable' => Http::STATUS_SERVICE_UNAVAILABLE,
			default => Http::STATUS_BAD_REQUEST,
		};
	}

	private function ensureAdmin(): ?JSONResponse {
		$user = $this->userSession->getUser();
		if ($user === null || !$this->groupManager->isAdmin($user->getUID())) {
			return new JSONResponse([
				'ok' => false,
				'message' => 'Admin access required.',
			], Http::STATUS_FORBIDDEN);
		}

		return null;
	}

	private function withNoStore(JSONResponse $response): JSONResponse {
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}
}
