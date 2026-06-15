<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Tests\Unit\Settings;

use OCA\FarmIntelligencePlatform\Service\AppConfig;
use OCA\FarmIntelligencePlatform\Service\IntegrationConfig;
use OCA\FarmIntelligencePlatform\Settings\AdminSettings;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Security\ICrypto;
use PHPUnit\Framework\TestCase;

final class AdminSettingsTest extends TestCase {
	public function testFormProvidesSaveUrl(): void {
		$response = $this->buildSettingsResponse();

		$this->assertInstanceOf(TemplateResponse::class, $response);
		$this->assertArrayHasKey('saveUrl', $response->getParams());
		$this->assertNotSame('', $response->getParams()['saveUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/settings/admin', $response->getParams()['saveUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/generate-credentials', $response->getParams()['generateCredentialsUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/rotate-hmac', $response->getParams()['rotateHmacUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/config', $response->getParams()['configUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/test-connection', $response->getParams()['testConnectionUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/admin/diagnostics', $response->getParams()['diagnosticsUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/admin/preview.png', $response->getParams()['previewUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/schema', $response->getParams()['farmSchemaUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/list', $response->getParams()['farmListUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/create', $response->getParams()['farmCreateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__ID__', $response->getParams()['farmGetUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__ID__', $response->getParams()['farmUpdateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__ID__', $response->getParams()['farmPatchUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__ID__', $response->getParams()['farmDeleteUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/sync', $response->getParams()['farmSyncUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndvi/latest', $response->getParams()['farmNdviLatestUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndvi/timeseries', $response->getParams()['farmNdviTimeseriesUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndvi/raster.png', $response->getParams()['farmNdviRasterUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndvi/raster/queue', $response->getParams()['farmNdviRasterQueueUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndvi/refresh', $response->getParams()['farmNdviRefreshUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/weather/current', $response->getParams()['farmWeatherCurrentUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/weather/hourly', $response->getParams()['farmWeatherHourlyUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/weather/daily', $response->getParams()['farmWeatherDailyUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/state', $response->getParams()['farmStateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/observations', $response->getParams()['farmObservationsUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/observations/__OBSERVATION_ID__', $response->getParams()['farmObservationUrl']);
	}

	public function testAdminUrlsRemainRoutePaths(): void {
		$response = $this->buildSettingsResponse();
		$params = $response->getParams();

		$this->assertStringStartsWith('/apps/farm_intelligence_platform/', $params['diagnosticsUrl']);
		$this->assertStringNotContainsString('/index.php', $params['diagnosticsUrl']);
		$this->assertStringNotContainsString('/api/v1/admin', $params['diagnosticsUrl']);
		$this->assertStringStartsWith('/apps/farm_intelligence_platform/', $params['previewUrl']);
		$this->assertStringNotContainsString('/index.php', $params['previewUrl']);
		$this->assertStringNotContainsString('/api/v1/admin', $params['previewUrl']);
		$this->assertStringNotContainsString('/index.php/index.php', $params['farmSchemaUrl']);
		$this->assertStringNotContainsString('/index.php/index.php', $params['testConnectionUrl']);
	}

	private function buildSettingsResponse(): TemplateResponse {
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
			fn (string $appId, string $key, mixed $default = ''): mixed => $storage[$key] ?? $default,
		);
		$config->method('getSystemValue')->willReturn(null);
		$config->method('getSystemValueBool')->willReturn(false);

		$crypto = $this->createMock(ICrypto::class);
		$appConfig = new AppConfig($config, $crypto);
		$integrationConfig = new IntegrationConfig($config, $crypto);

		$l10n = $this->createMock(IL10N::class);
		$urlGenerator = $this->createMock(IURLGenerator::class);
		$urlGenerator->expects($this->exactly(46))
			->method('linkToRoute')
			->withConsecutive(
				['farm_intelligence_platform.settings.saveAdmin'],
				['farm_intelligence_platform.adminConfig.generateCredentials'],
				['farm_intelligence_platform.adminConfig.rotateHmac'],
				['farm_intelligence_platform.adminConfig.getConfig'],
				['farm_intelligence_platform.adminConfig.testConnection'],
				['farm_intelligence_platform.adminConfig.diagnostics'],
				['farm_intelligence_platform.adminConfig.previewPng'],
				['farm_intelligence_platform.adminFarms.getSchema'],
				['farm_intelligence_platform.adminFarms.listFarms'],
				['farm_intelligence_platform.adminFarms.createFarm'],
				['farm_intelligence_platform.adminFarms.getFarm', ['id' => '__ID__']],
				['farm_intelligence_platform.adminFarms.updateFarm', ['id' => '__ID__']],
				['farm_intelligence_platform.adminFarms.patchFarm', ['id' => '__ID__']],
				['farm_intelligence_platform.adminFarms.deleteFarm', ['id' => '__ID__']],
				['farm_intelligence_platform.adminFarms.syncFarm', []],
				['farm_intelligence_platform.adminFarms.getNdviLatest', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getNdviTimeseries', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getNdviRasterPng', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.queueNdviRaster', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.refreshNdvi', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getWeatherCurrent', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getWeatherHourly', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getWeatherDaily', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getFarmState', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.listFarmObservations', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getFarmObservation', ['farmId' => '__FARM_ID__', 'observationId' => '__OBSERVATION_ID__']],
				['farm_intelligence_platform.adminFarms.listFarmActivities', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getFarmActivity', ['farmId' => '__FARM_ID__', 'activityId' => '__ACTIVITY_ID__']],
				['farm_intelligence_platform.adminActivities.getSchema'],
				['farm_intelligence_platform.adminActivities.listActivities'],
				['farm_intelligence_platform.adminActivities.createActivity'],
				['farm_intelligence_platform.adminActivities.getActivity', ['id' => '__ID__']],
				['farm_intelligence_platform.adminActivities.updateActivity', ['id' => '__ID__']],
				['farm_intelligence_platform.adminActivities.patchActivity', ['id' => '__ID__']],
				['farm_intelligence_platform.adminActivities.deleteActivity', ['id' => '__ID__']],
				['farm_intelligence_platform.adminRadio.listProviders'],
				['farm_intelligence_platform.adminRadio.listStations'],
				['farm_intelligence_platform.adminRadio.getStation', ['stationId' => '__STATION_ID__']],
				['farm_intelligence_platform.adminRadio.getStreamUrl', ['stationId' => '__STATION_ID__']],
				['farm_intelligence_platform.adminRadio.getStationNowPlaying', ['stationId' => '__STATION_ID__']],
				['farm_intelligence_platform.adminRadio.getStationAnalytics', ['stationId' => '__STATION_ID__']],
				['farm_intelligence_platform.adminRadio.getStationHealth', ['stationId' => '__STATION_ID__']],
				['farm_intelligence_platform.adminRadio.getStationHealthHistory', ['stationId' => '__STATION_ID__']],
				['farm_intelligence_platform.adminRadio.getRadioHealth'],
				['farm_intelligence_platform.adminRadio.getCurrentEmergency'],
				['farm_intelligence_platform.adminRadio.getEmergencyHistory'],
			)
			->willReturnOnConsecutiveCalls(
				'/apps/farm_intelligence_platform/settings/admin',
				'/apps/farm_intelligence_platform/api/v1/admin/generate-credentials',
				'/apps/farm_intelligence_platform/api/v1/admin/rotate-hmac',
				'/apps/farm_intelligence_platform/api/v1/admin/config',
				'/apps/farm_intelligence_platform/api/v1/admin/test-connection',
				'/apps/farm_intelligence_platform/admin/diagnostics',
				'/apps/farm_intelligence_platform/admin/preview.png',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/schema',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/list',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/create',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__ID__',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__ID__',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__ID__',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__ID__',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/sync',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndvi/latest',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndvi/timeseries',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndvi/raster.png',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndvi/raster/queue',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndvi/refresh',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/weather/current',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/weather/hourly',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/weather/daily',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/state',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/observations',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/observations/__OBSERVATION_ID__',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/activities',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/activities/__ACTIVITY_ID__',
				'/apps/farm_intelligence_platform/api/v1/admin/activities/schema',
				'/apps/farm_intelligence_platform/api/v1/admin/activities/list',
				'/apps/farm_intelligence_platform/api/v1/admin/activities/create',
				'/apps/farm_intelligence_platform/api/v1/admin/activities/__ID__',
				'/apps/farm_intelligence_platform/api/v1/admin/activities/__ID__',
				'/apps/farm_intelligence_platform/api/v1/admin/activities/__ID__',
				'/apps/farm_intelligence_platform/api/v1/admin/activities/__ID__',
				'/apps/farm_intelligence_platform/api/v1/admin/radio/providers',
				'/apps/farm_intelligence_platform/api/v1/admin/radio/stations',
				'/apps/farm_intelligence_platform/api/v1/admin/radio/stations/__STATION_ID__',
				'/apps/farm_intelligence_platform/api/v1/admin/radio/stations/__STATION_ID__/stream',
				'/apps/farm_intelligence_platform/api/v1/admin/radio/stations/__STATION_ID__/now-playing',
				'/apps/farm_intelligence_platform/api/v1/admin/radio/stations/__STATION_ID__/analytics',
				'/apps/farm_intelligence_platform/api/v1/admin/radio/stations/__STATION_ID__/health',
				'/apps/farm_intelligence_platform/api/v1/admin/radio/stations/__STATION_ID__/health/history',
				'/apps/farm_intelligence_platform/api/v1/admin/radio/health',
				'/apps/farm_intelligence_platform/api/v1/admin/radio/emergency/current',
				'/apps/farm_intelligence_platform/api/v1/admin/radio/emergency/history',
			);

		$settings = new AdminSettings('farm_intelligence_platform', $l10n, $appConfig, $integrationConfig, $urlGenerator);
		return $settings->getForm();
	}
}
