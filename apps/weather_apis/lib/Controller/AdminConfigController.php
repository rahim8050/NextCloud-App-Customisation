<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Controller;

use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Service\IntegrationConfig;
use OCA\WeatherApis\Service\LogSanitizer;
use OCA\WeatherApis\Settings\AdminSettings;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting;
use OCP\AppFramework\Http\Attribute\PasswordConfirmationRequired;
use OCP\AppFramework\Http\JSONResponse;
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
			return $this->buildErrorResponse('forbidden', 'Admin access required.', $requestId, Http::STATUS_FORBIDDEN);
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

			return $this->buildErrorResponse(
				(string)($status['code'] ?? 'invalid_argument'),
				$message,
				$requestId,
				Http::STATUS_BAD_REQUEST,
				$status,
			);
		}

		$finalMessage = $message;
		if (!empty($status['warning'])) {
			$finalMessage = $message . ' Legacy keys detected; remove them after migration.';
		}

		return $this->buildStatusResponse('ok', $finalMessage, [
			'ok' => true,
			'legacyPresent' => $status['legacyPresent'] ?? false,
			'warning' => $status['warning'] ?? null,
		]);
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
