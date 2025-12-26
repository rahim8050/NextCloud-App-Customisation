<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Controller;

use InvalidArgumentException;
use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Service\UrlValidator;
use OCA\WeatherApis\Settings\AdminSettings;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting;
use OCP\AppFramework\Http\Attribute\PasswordConfirmationRequired;
use OCP\AppFramework\Http\DataResponse;
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
		private readonly UrlValidator $urlValidator,
		private readonly IUserSession $userSession,
		private readonly IGroupManager $groupManager,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
	}

	#[AuthorizedAdminSetting(settings: AdminSettings::class)]
	#[PasswordConfirmationRequired]
	public function saveAdmin(): DataResponse {
		$requestId = $this->resolveRequestId();
		$user = $this->userSession->getUser();
		if ($user === null || !$this->groupManager->isAdmin($user->getUID())) {
			return $this->buildErrorResponse('forbidden', 'Admin access required.', $requestId, Http::STATUS_FORBIDDEN);
		}

		$baseUrl = trim((string)$this->request->getParam('baseUrl', ''));
		$clientId = trim((string)$this->request->getParam('clientId', ''));
		$timeout = (int)$this->request->getParam('timeoutSeconds', $this->appConfig->getTimeoutSeconds());
		$devAllowHttp = filter_var($this->request->getParam('devAllowHttp', false), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
		$devAllowlistHosts = trim((string)$this->request->getParam('devAllowlistHosts', ''));
		$apiKey = trim((string)$this->request->getParam('apiKey', ''));
		$signingSecret = trim((string)$this->request->getParam('signingSecret', ''));

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
		if (strlen($devAllowlistHosts) > self::DEV_ALLOWLIST_MAX_LENGTH) {
			return $this->buildErrorResponse('invalid_argument', 'Allowlist is too long.', $requestId, Http::STATUS_BAD_REQUEST);
		}

		try {
			$this->urlValidator->validate($baseUrl, $devAllowHttp, $devAllowlistHosts);
		} catch (InvalidArgumentException $exception) {
			return $this->buildErrorResponse('invalid_argument', $exception->getMessage(), $requestId, Http::STATUS_BAD_REQUEST);
		}

		try {
			$this->appConfig->setBaseUrl($baseUrl);
			$this->appConfig->setClientId($clientId);
			$this->appConfig->setTimeoutSeconds($timeout);
			$this->appConfig->setDevAllowInsecureLocalHttp($devAllowHttp);
			$this->appConfig->setDevAllowlistHosts($devAllowlistHosts);

			if ($apiKey !== '') {
				$this->appConfig->setApiKey($apiKey);
			}
			if ($signingSecret !== '') {
				$this->appConfig->setHmacSecret($signingSecret);
			}
		} catch (\Throwable $throwable) {
			$this->logger->error('Failed to persist weather settings', [
				'requestId' => $requestId,
				'error' => $throwable->getMessage(),
			]);

			return $this->buildErrorResponse('backend_error', 'Unable to save settings.', $requestId, Http::STATUS_INTERNAL_SERVER_ERROR);
		}

		return new DataResponse(['ok' => true]);
	}

	private function buildErrorResponse(string $code, string $message, string $requestId, int $status): DataResponse {
		return new DataResponse([
			'status' => 'error',
			'error' => [
				'code' => $code,
				'message' => $message,
				'requestId' => $requestId,
				'details' => new \stdClass(),
			],
		], $status);
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
