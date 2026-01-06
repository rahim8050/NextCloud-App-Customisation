<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Controller;

use InvalidArgumentException;
use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Service\LogSanitizer;
use OCA\WeatherApis\Service\WeatherApiClientInterface;
use OCA\WeatherApis\Service\WeatherApiException;
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
		$hmacSecret = $this->generateHmacSecret();
		$this->appConfig->rotateHmacSecret($hmacSecret);
		$this->appConfig->migrateLegacyConfig();

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

		$hmacSecret = $this->generateHmacSecret();
		$this->appConfig->rotateHmacSecret($hmacSecret);
		$this->appConfig->migrateLegacyConfig();

		return $this->withNoStore(new JSONResponse([
			'status' => 'ok',
			'ok' => true,
			'message' => 'Rotated secret. Shown once.',
			'hmacSecret' => $hmacSecret,
		]));
	}

	#[AuthorizedAdminSetting(settings: AdminSettings::class)]
	public function getConfig(): JSONResponse {
		if ($response = $this->ensureAdmin()) {
			return $response;
		}

		$clientId = '';
		try {
			$clientId = $this->appConfig->getClientId();
		} catch (InvalidArgumentException) {
			// no configured client id yet
		}

		return new JSONResponse([
			'baseUrl' => $this->appConfig->getBaseUrl(),
			'clientId' => $clientId,
			'timeoutSeconds' => $this->appConfig->getTimeoutSeconds(),
			'devAllowHttp' => $this->appConfig->isDevAllowHttp(),
			'allowlistHosts' => $this->appConfig->getAllowlistHosts(),
			'hasApiKey' => $this->appConfig->hasApiKey(),
			'hasHmacSecret' => $this->appConfig->hasHmacSecret(),
			'hmacRotation' => [
				'hasPrevious' => $this->appConfig->hasPreviousHmacSecret(),
				'previousExpiresAt' => $this->appConfig->getHmacSecretPreviousExpiresAt(),
			],
		]);
	}

	#[AuthorizedAdminSetting(settings: AdminSettings::class)]
	public function testConnection(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$user = $this->userSession->getUser();
		if ($user === null || !$this->groupManager->isAdmin($user->getUID())) {
			return $this->buildErrorResponse('forbidden', 'Admin access required.', $requestId, Http::STATUS_FORBIDDEN);
		}

		try {
			$this->weatherApiClient->ping($requestId);
		} catch (WeatherApiException $exception) {
			$this->logger->error(
				'Weather API test connection failed',
				LogSanitizer::sanitizeContext([
					'errorCode' => $exception->getErrorCode(),
					'reason' => $exception->getReason(),
					'requestId' => $requestId,
				]),
			);

			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			$this->logger->error(
				'Weather API test connection failed',
				LogSanitizer::sanitizeContext([
					'error' => $throwable->getMessage(),
					'requestId' => $requestId,
				]),
			);

			return $this->buildErrorResponse(
				'backend_error',
				'Unable to reach backend.',
				$requestId,
				Http::STATUS_SERVICE_UNAVAILABLE,
			);
		}

		return $this->buildStatusResponse('ok', 'Connection successful.', ['ok' => true]);
	}

	private function resolveClientId(): string {
		try {
			return $this->appConfig->getClientId();
		} catch (InvalidArgumentException) {
			$clientId = $this->generateUuid();
			$this->appConfig->setClientId($clientId);

			return $clientId;
		}
	}

	private function generateHmacSecret(): string {
		return bin2hex(random_bytes(32));
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
