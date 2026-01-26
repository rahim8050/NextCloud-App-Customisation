<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Settings;

use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Service\IntegrationConfig;
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
			'INTEGRATION_HMAC_CLIENT_ID' => '',
			'INTEGRATION_HMAC_CLIENTS_JSON' => '',
		];

		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')->willReturnCallback(
			fn (string $appId, string $key, $default = '') => $storage[$key] ?? $default,
		);
		$config->method('getSystemValue')->willReturn(null);
		$config->method('getSystemValueBool')->willReturn(false);

		$crypto = $this->createMock(ICrypto::class);
		$appConfig = new AppConfig($config, $crypto);
		$integrationConfig = new IntegrationConfig($config, $crypto);

		$l10n = $this->createMock(IL10N::class);
		$urlGenerator = $this->createMock(IURLGenerator::class);
		$urlGenerator->expects($this->exactly(22))
			->method('linkToRoute')
			->withConsecutive(
				['weather_apis.settings.saveAdmin'],
				['weather_apis.adminConfig.generateCredentials'],
				['weather_apis.adminConfig.rotateHmac'],
				['weather_apis.adminConfig.getConfig'],
				['weather_apis.adminConfig.testConnection'],
				['weather_apis.adminConfig.diagnostics'],
				['weather_apis.adminConfig.previewPng'],
				['weather_apis.adminFarms.getSchema'],
				['weather_apis.adminFarms.listFarms'],
				['weather_apis.adminFarms.createFarm'],
				['weather_apis.adminFarms.getFarm', ['id' => '__ID__']],
				['weather_apis.adminFarms.updateFarm', ['id' => '__ID__']],
				['weather_apis.adminFarms.patchFarm', ['id' => '__ID__']],
				['weather_apis.adminFarms.deleteFarm', ['id' => '__ID__']],
				['weather_apis.adminFarms.getNdviLatest', ['farmId' => '__FARM_ID__']],
				['weather_apis.adminFarms.getNdviTimeseries', ['farmId' => '__FARM_ID__']],
				['weather_apis.adminFarms.getNdviRasterPng', ['farmId' => '__FARM_ID__']],
				['weather_apis.adminFarms.queueNdviRaster', ['farmId' => '__FARM_ID__']],
				['weather_apis.adminFarms.refreshNdvi', ['farmId' => '__FARM_ID__']],
				['weather_apis.adminFarms.getWeatherCurrent', ['farmId' => '__FARM_ID__']],
				['weather_apis.adminFarms.getWeatherHourly', ['farmId' => '__FARM_ID__']],
				['weather_apis.adminFarms.getWeatherDaily', ['farmId' => '__FARM_ID__']],
			)
			->willReturnOnConsecutiveCalls(
				'/apps/weather_apis/settings/admin',
				'/apps/weather_apis/api/v1/admin/generate-credentials',
				'/apps/weather_apis/api/v1/admin/rotate-hmac',
				'/apps/weather_apis/api/v1/admin/config',
				'/apps/weather_apis/api/v1/admin/test-connection',
				'/apps/weather_apis/admin/diagnostics',
				'/apps/weather_apis/admin/preview.png',
				'/apps/weather_apis/api/v1/admin/farms/schema',
				'/apps/weather_apis/api/v1/admin/farms/list',
				'/apps/weather_apis/api/v1/admin/farms/create',
				'/apps/weather_apis/api/v1/admin/farms/__ID__',
				'/apps/weather_apis/api/v1/admin/farms/__ID__',
				'/apps/weather_apis/api/v1/admin/farms/__ID__',
				'/apps/weather_apis/api/v1/admin/farms/__ID__',
				'/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/ndvi/latest',
				'/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/ndvi/timeseries',
				'/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/ndvi/raster.png',
				'/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/ndvi/raster/queue',
				'/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/ndvi/refresh',
				'/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/weather/current',
				'/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/weather/hourly',
				'/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/weather/daily',
			);

		$settings = new AdminSettings('weather_apis', $l10n, $appConfig, $integrationConfig, $urlGenerator);
		$response = $settings->getForm();

		$this->assertInstanceOf(TemplateResponse::class, $response);
		$this->assertArrayHasKey('saveUrl', $response->getParams());
		$this->assertNotSame('', $response->getParams()['saveUrl']);
		$this->assertSame('/apps/weather_apis/settings/admin', $response->getParams()['saveUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/generate-credentials', $response->getParams()['generateCredentialsUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/rotate-hmac', $response->getParams()['rotateHmacUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/config', $response->getParams()['configUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/test-connection', $response->getParams()['testConnectionUrl']);
		$this->assertSame('/apps/weather_apis/admin/diagnostics', $response->getParams()['diagnosticsUrl']);
		$this->assertSame('/apps/weather_apis/admin/preview.png', $response->getParams()['previewUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/schema', $response->getParams()['farmSchemaUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/list', $response->getParams()['farmListUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/create', $response->getParams()['farmCreateUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/__ID__', $response->getParams()['farmGetUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/__ID__', $response->getParams()['farmUpdateUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/__ID__', $response->getParams()['farmPatchUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/__ID__', $response->getParams()['farmDeleteUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/ndvi/latest', $response->getParams()['farmNdviLatestUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/ndvi/timeseries', $response->getParams()['farmNdviTimeseriesUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/ndvi/raster.png', $response->getParams()['farmNdviRasterUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/ndvi/raster/queue', $response->getParams()['farmNdviRasterQueueUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/ndvi/refresh', $response->getParams()['farmNdviRefreshUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/weather/current', $response->getParams()['farmWeatherCurrentUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/weather/hourly', $response->getParams()['farmWeatherHourlyUrl']);
		$this->assertSame('/apps/weather_apis/api/v1/admin/farms/__FARM_ID__/weather/daily', $response->getParams()['farmWeatherDailyUrl']);
	}
}
