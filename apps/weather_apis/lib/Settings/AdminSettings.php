<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Settings;

use InvalidArgumentException;
use OCA\WeatherApis\Service\AppConfig;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IDelegatedSettings;

final class AdminSettings implements IDelegatedSettings {
	public function __construct(
		private readonly string $appName,
		private readonly IL10N $l10n,
		private readonly AppConfig $appConfig,
		private readonly IURLGenerator $urlGenerator,
	) {
	}

	public function getSection(): string {
		return 'weather_apis';
	}

	public function getPriority(): int {
		return 10;
	}

	public function getForm(): TemplateResponse {
		$clientId = '';
		try {
			$clientId = $this->appConfig->getClientId();
		} catch (InvalidArgumentException) {
			// ignored – display empty field until value is saved
		}

		return new TemplateResponse('weather_apis', 'settings/admin', [
			'appName' => $this->appName,
			'baseUrl' => $this->appConfig->getBaseUrl(),
			'clientId' => $clientId,
			'timeoutSeconds' => $this->appConfig->getTimeoutSeconds(),
			'devAllowHttp' => $this->appConfig->isDevAllowHttp(),
			'allowlistHosts' => $this->appConfig->getAllowlistHosts(),
			'hmacSecretSet' => $this->appConfig->hasHmacSecret(),
			'apiKeySet' => $this->appConfig->hasApiKey(),
			'saveUrl' => $this->urlGenerator->linkToRoute('weather_apis.settings.saveAdmin'),
			'generateCredentialsUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminConfig.generateCredentials'),
			'rotateHmacUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminConfig.rotateHmac'),
			'configUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminConfig.getConfig'),
		]);
	}

	public function getName(): ?string {
		return 'Weather Apis';
	}

	public function getAuthorizedAppConfig(): array {
		return [
			AppConfig::APP_ID => [
				'/^baseUrl$/',
				'/^clientId$/',
				'/^apiKey$/',
				'/^hmacSecret$/',
				'/^hmacSecretPrevious$/',
				'/^hmacSecretPreviousExpiresAt$/',
				'/^timeoutSeconds$/',
				'/^devAllowHttp$/',
				'/^allowlistHosts$/',
				'/^base_url$/',
				'/^timeout_seconds$/',
				'/^dev_allow_insecure_local_http$/',
				'/^dev_allowlist_hosts$/',
				'/^api_key$/',
				'/^hmac_client_id$/',
				'/^hmac_secret$/',
				'/^signingSecret$/',
				'/^devAllowlistHosts$/',
			],
		];
	}
}
