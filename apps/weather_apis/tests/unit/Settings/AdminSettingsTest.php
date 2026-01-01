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
			'baseUrl' => 'https://example.com',
			'timeoutSeconds' => '10',
			'devAllowHttp' => '0',
			'allowlistHosts' => '',
			'apiKey' => '',
			'hmacSecret' => '',
			'clientId' => '',
		];

		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')->willReturnCallback(
			fn (string $appId, string $key, $default = '') => $storage[$key] ?? $default,
		);

		$crypto = $this->createMock(ICrypto::class);
		$appConfig = new AppConfig($config, $crypto);

		$l10n = $this->createMock(IL10N::class);
		$urlGenerator = $this->createMock(IURLGenerator::class);
		$urlGenerator->expects($this->exactly(4))
			->method('linkToRoute')
			->withConsecutive(
				['weather_apis.settings.saveAdmin'],
				['weather_apis.adminConfig.generateCredentials'],
				['weather_apis.adminConfig.rotateHmac'],
				['weather_apis.adminConfig.getConfig'],
			)
			->willReturnOnConsecutiveCalls(
				'/apps/weather_apis/settings/admin',
				'/apps/weather_apis/api/v1/admin/generate-credentials',
				'/apps/weather_apis/api/v1/admin/rotate-hmac',
				'/apps/weather_apis/api/v1/admin/config',
			);

		$settings = new AdminSettings('weather_apis', $l10n, $appConfig, $urlGenerator);
		$response = $settings->getForm();

		$this->assertInstanceOf(TemplateResponse::class, $response);
		$this->assertArrayHasKey('saveUrl', $response->getParams());
		$this->assertNotSame('', $response->getParams()['saveUrl']);
		$this->assertSame('/apps/weather_apis/settings/admin', $response->getParams()['saveUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/generate-credentials', $response->getParams()['generateCredentialsUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/rotate-hmac', $response->getParams()['rotateHmacUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/config', $response->getParams()['configUrl']);
	}
}
