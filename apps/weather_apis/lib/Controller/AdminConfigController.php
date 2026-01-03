<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Controller;

use InvalidArgumentException;
use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Settings\AdminSettings;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting;
use OCP\AppFramework\Http\Attribute\PasswordConfirmationRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;

final class AdminConfigController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private readonly AppConfig $appConfig,
		private readonly IUserSession $userSession,
		private readonly IGroupManager $groupManager,
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
			'ok' => true,
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
			'ok' => true,
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
