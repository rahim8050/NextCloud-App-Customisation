<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Settings;

use InvalidArgumentException;
use OCA\WeatherApis\Service\AppConfig;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\Settings\IDelegatedSettings;

final class AdminSettings implements IDelegatedSettings {
	public function __construct(
		private readonly string $appName,
		private readonly IL10N $l10n,
		private readonly AppConfig $appConfig,
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
			$clientId = $this->appConfig->getHmacClientId();
		} catch (InvalidArgumentException) {
			// ignored – display empty field until value is saved
		}

		return new TemplateResponse('weather_apis', 'settings/admin', [
			'appName' => $this->appName,
			'baseUrl' => $this->appConfig->getBaseUrl(),
			'clientId' => $clientId,
			'timeoutSeconds' => $this->appConfig->getTimeoutSeconds(),
			'devAllowHttp' => $this->appConfig->isDevAllowInsecureLocalHttp(),
			'devAllowlistHosts' => $this->appConfig->getDevAllowlistHosts(),
			'secretSet' => $this->appConfig->hasHmacSecret(),
			'apiKeySet' => $this->appConfig->hasApiKey(),
		]);
	}

	public function getName(): ?string {
		return 'Weather Apis';
	}

	public function getAuthorizedAppConfig(): array {
		return [
			AppConfig::APP_ID => [
				'/^base_url$/',
				'/^timeout_seconds$/',
				'/^dev_allow_insecure_local_http$/',
				'/^dev_allowlist_hosts$/',
				'/^api_key$/',
				'/^hmac_client_id$/',
				'/^hmac_secret$/',
			],
		];
	}
}
