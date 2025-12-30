<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Settings;

use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Settings\AdminSettings;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Security\ICrypto;
use PHPUnit\Framework\TestCase;

final class AdminSettingsTest extends TestCase {
	public function testFormProvidesSaveUrl(): void {
		$storage = [
			'base_url' => 'https://example.com',
			'timeout_seconds' => '10',
			'dev_allow_insecure_local_http' => '0',
			'dev_allowlist_hosts' => '',
			'api_key' => '',
			'hmac_secret' => '',
			'hmac_client_id' => '',
		];

		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')->willReturnCallback(
			fn (string $appId, string $key, $default = '') => $storage[$key] ?? $default,
		);

		$crypto = $this->createMock(ICrypto::class);
		$appConfig = new AppConfig($config, $crypto);

		$l10n = $this->createMock(IL10N::class);
		$urlGenerator = $this->createMock(IURLGenerator::class);
		$urlGenerator->expects($this->once())
			->method('linkToRoute')
			->with('weather_apis.settings.saveAdmin')
			->willReturn('/apps/weather_apis/settings/admin');

		$settings = new AdminSettings('weather_apis', $l10n, $appConfig, $urlGenerator);
		$response = $settings->getForm();

		$this->assertInstanceOf(TemplateResponse::class, $response);
		$this->assertSame('/apps/weather_apis/settings/admin', $response->getParams()['saveUrl']);
	}
}
