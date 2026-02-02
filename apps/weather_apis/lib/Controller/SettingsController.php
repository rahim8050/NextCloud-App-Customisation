<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Controller;

use InvalidArgumentException;
use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Service\HttpStatus;
use OCA\WeatherApis\Service\IntegrationConfig;
use OCA\WeatherApis\Service\IntegrationConfigException;
use OCA\WeatherApis\Service\LogSanitizer;
use OCA\WeatherApis\Service\UrlValidator;
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

final class SettingsController extends Controller {
	private const CLIENT_ID_MAX_LENGTH = 128;
	private const BASE_URL_MAX_LENGTH = 2048;
	private const DEV_ALLOWLIST_MAX_LENGTH = 2048;

	public function __construct(
		string $appName,
		IRequest $request,
		private readonly AppConfig $appConfig,
		private readonly IntegrationConfig $integrationConfig,
		private readonly UrlValidator $urlValidator,
		private readonly IUserSession $userSession,
		private readonly IGroupManager $groupManager,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);

		// Ensure form submissions with browser Accept headers still receive JSON.
		$this->registerResponder('xhtml+xml', fn (mixed $data) => $this->buildResponse($data, 'json'));
	}

	#[AuthorizedAdminSetting(settings: AdminSettings::class)]
	#[PasswordConfirmationRequired]
	public function saveAdmin(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$user = $this->userSession->getUser();
		$this->logger->debug(
			'Weather APIs admin settings save requested',
			LogSanitizer::sanitizeContext([
				'requestId' => $requestId,
				'uid' => $user?->getUID() ?? '',
				'contentType' => $this->request->getHeader('Content-Type'),
			]),
		);
		if ($user === null || !$this->groupManager->isAdmin($user->getUID())) {
			return $this->buildErrorResponse('forbidden', 'Admin access required.', $requestId, Http::STATUS_FORBIDDEN);
		}

		$params = $this->request->getParams();
		$baseUrl = trim($this->pickParam($params, ['baseUrl', 'base_url'], ''));
		$clientId = trim($this->pickParam($params, ['clientId', 'hmac_client_id'], ''));
		$timeoutRaw = $this->pickParam($params, ['timeoutSeconds', 'timeout_seconds'], (string)$this->appConfig->getTimeoutSeconds());
		$timeout = (int)$timeoutRaw;
		$devAllowHttp = filter_var(
			$this->pickParam($params, ['devAllowHttp', 'dev_allow_insecure_local_http'], '0'),
			FILTER_VALIDATE_BOOLEAN,
			FILTER_NULL_ON_FAILURE,
		) ?? false;
		$allowlistHosts = trim($this->pickParam($params, ['allowlistHosts', 'devAllowlistHosts', 'dev_allowlist_hosts'], ''));
		$apiKey = trim($this->pickParam($params, ['apiKey', 'api_key'], ''));
		$hmacSecret = trim($this->pickParam($params, ['hmacSecret', 'signingSecret', 'hmac_secret'], ''));
		$existingClientId = $this->integrationConfig->getClientIdOrNull();
		$existingSecretB64 = $this->integrationConfig->getSecretB64OrNull();

		if ($baseUrl === '') {
			return $this->buildErrorResponse('invalid_argument', 'Base URL is required.', $requestId, Http::STATUS_BAD_REQUEST);
		}
		if ($clientId === '') {
			return $this->buildErrorResponse('invalid_argument', 'Client ID is required.', $requestId, Http::STATUS_BAD_REQUEST);
		}
		if (mb_strlen($clientId) > self::CLIENT_ID_MAX_LENGTH) {
			return $this->buildErrorResponse('invalid_argument', 'Client ID is too long.', $requestId, Http::STATUS_BAD_REQUEST);
		}
		if (strlen($baseUrl) > self::BASE_URL_MAX_LENGTH) {
			return $this->buildErrorResponse('invalid_argument', 'Base URL is too long.', $requestId, Http::STATUS_BAD_REQUEST);
		}
		if ($timeout < 1 || $timeout > 30) {
			return $this->buildErrorResponse('invalid_argument', 'Timeout must be between 1 and 30 seconds.', $requestId, Http::STATUS_BAD_REQUEST);
		}
		if (strlen($allowlistHosts) > self::DEV_ALLOWLIST_MAX_LENGTH) {
			return $this->buildErrorResponse('invalid_argument', 'Allowlist is too long.', $requestId, Http::STATUS_BAD_REQUEST);
		}
		if ($hmacSecret !== '') {
			try {
				$this->integrationConfig->validateSecretB64($hmacSecret);
			} catch (IntegrationConfigException $exception) {
				return $this->buildErrorResponse('invalid_argument', $exception->getMessage(), $requestId, Http::STATUS_BAD_REQUEST);
			}
		}

		try {
			$this->urlValidator->validate(
				$baseUrl,
				$devAllowHttp,
				$allowlistHosts,
				$this->appConfig->isAllowLocalRemoteServers(),
			);
		} catch (InvalidArgumentException $exception) {
			return $this->buildErrorResponse('invalid_argument', $exception->getMessage(), $requestId, Http::STATUS_BAD_REQUEST);
		}

		try {
			$this->appConfig->setBaseUrl($baseUrl);
			$this->appConfig->setTimeoutSeconds($timeout);
			$this->appConfig->setDevAllowHttp($devAllowHttp);
			$this->appConfig->setAllowlistHosts($allowlistHosts);

			if ($hmacSecret !== '') {
				$this->integrationConfig->setCredentials($clientId, $hmacSecret);
			} else {
				$this->integrationConfig->setClientId($clientId);
				if ($existingSecretB64 !== null && $existingClientId !== null && $existingClientId !== $clientId) {
					$this->integrationConfig->setCredentials($clientId, $existingSecretB64);
				}
			}

			if ($apiKey !== '') {
				$this->appConfig->setApiKey($apiKey);
			}
		} catch (\Throwable $throwable) {
			$this->logger->error(
				'Failed to persist weather settings',
				LogSanitizer::sanitizeContext([
					'requestId' => $requestId,
					'error' => $throwable->getMessage(),
				]),
			);

			return $this->buildErrorResponse('backend_error', 'Unable to save settings.', $requestId, Http::STATUS_INTERNAL_SERVER_ERROR);
		}

		return new JSONResponse([
			'status' => 'ok',
			'ok' => true,
			'message' => 'Settings saved.',
		]);
	}

	/**
	 * @param array<string, mixed> $params
	 * @param list<string> $names
	 */
	private function pickParam(array $params, array $names, string $default): string {
		foreach ($names as $name) {
			if (array_key_exists($name, $params)) {
				return (string)$params[$name];
			}
		}

		return $default;
	}

	private function buildErrorResponse(string $code, string $message, string $requestId, int $status): JSONResponse {
		return new JSONResponse([
			'status' => 'error',
			'error' => [
				'code' => $code,
				'message' => $message,
				'requestId' => $requestId,
				'details' => new \stdClass(),
			],
		], HttpStatus::normalize($status));
	}

	private function resolveRequestId(): string {
		$header = $this->request->getHeader('X-Request-Id');
		if ($header !== '') {
			return $header;
		}

		return $this->generateUuid();
	}

	private function generateUuid(): string {
		$bytes = random_bytes(16);
		$bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
		$bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
	}
}
