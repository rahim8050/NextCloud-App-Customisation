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
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndwi/latest', $response->getParams()['farmNdwiLatestUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndwi/timeseries', $response->getParams()['farmNdwiTimeseriesUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndwi/raster.png', $response->getParams()['farmNdwiRasterUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndwi/raster/queue', $response->getParams()['farmNdwiRasterQueueUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndwi/refresh', $response->getParams()['farmNdwiRefreshUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndwi/farm-state', $response->getParams()['farmNdwiFarmStateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/latest', $response->getParams()['farmNdmiLatestUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/timeseries', $response->getParams()['farmNdmiTimeseriesUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/raster.png', $response->getParams()['farmNdmiRasterUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/raster/queue', $response->getParams()['farmNdmiRasterQueueUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/refresh', $response->getParams()['farmNdmiRefreshUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/farm-state', $response->getParams()['farmNdmiFarmStateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/latest', $response->getParams()['farmRviLatestUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/timeseries', $response->getParams()['farmRviTimeseriesUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/raster.png', $response->getParams()['farmRviRasterUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/raster/queue', $response->getParams()['farmRviRasterQueueUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/refresh', $response->getParams()['farmRviRefreshUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/farm-state', $response->getParams()['farmRviFarmStateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/latest', $response->getParams()['farmS1SmiLatestUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/timeseries', $response->getParams()['farmS1SmiTimeseriesUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/raster.png', $response->getParams()['farmS1SmiRasterUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/raster/queue', $response->getParams()['farmS1SmiRasterQueueUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/refresh', $response->getParams()['farmS1SmiRefreshUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/farm-state', $response->getParams()['farmS1SmiFarmStateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/weather/current', $response->getParams()['farmWeatherCurrentUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/weather/hourly', $response->getParams()['farmWeatherHourlyUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/weather/daily', $response->getParams()['farmWeatherDailyUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/state', $response->getParams()['farmStateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/observations', $response->getParams()['farmObservationsUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/observations/__OBSERVATION_ID__', $response->getParams()['farmObservationUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/radio/emergency', $response->getParams()['radioEmergencyCreateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/radio/emergency/__PK__', $response->getParams()['radioEmergencyUpdateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/radio/emergency/__PK__', $response->getParams()['radioEmergencyDeleteUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/radio/tts', $response->getParams()['radioTtsUrl']);
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
		$urlGenerator->expects($this->exactly(74))
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
				['farm_intelligence_platform.adminFarms.getNdwiLatest', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getNdwiTimeseries', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getNdwiRasterPng', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.queueNdwiRaster', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.refreshNdwi', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getNdwiFarmState', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getNdmiLatest', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getNdmiTimeseries', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getNdmiRasterPng', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.queueNdmiRaster', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.refreshNdmi', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getNdmiFarmState', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getRviLatest', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getRviTimeseries', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getRviRasterPng', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.queueRviRaster', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.refreshRvi', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getRviFarmState', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getS1SmiLatest', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getS1SmiTimeseries', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getS1SmiRasterPng', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.queueS1SmiRaster', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.refreshS1Smi', ['farmId' => '__FARM_ID__']],
				['farm_intelligence_platform.adminFarms.getS1SmiFarmState', ['farmId' => '__FARM_ID__']],
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
				['farm_intelligence_platform.adminRadio.getStationHealthHistory', ['stationId' => '__STATION_ID__']],
				['farm_intelligence_platform.adminRadio.getStationHealthHistory', ['stationId' => '__STATION_ID__']],
				['farm_intelligence_platform.adminRadio.getRadioHealth'],
				['farm_intelligence_platform.adminRadio.getCurrentEmergency'],
				['farm_intelligence_platform.adminRadio.getEmergencyHistory'],
				['farm_intelligence_platform.adminRadio.createEmergency'],
				['farm_intelligence_platform.adminRadio.updateEmergency', ['pk' => '__PK__']],
				['farm_intelligence_platform.adminRadio.deleteEmergency', ['pk' => '__PK__']],
				['farm_intelligence_platform.adminRadio.synthesizeTts'],
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
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndwi/latest',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndwi/timeseries',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndwi/raster.png',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndwi/raster/queue',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndwi/refresh',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndwi/farm-state',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/latest',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/timeseries',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/raster.png',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/raster/queue',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/refresh',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/farm-state',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/latest',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/timeseries',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/raster.png',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/raster/queue',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/refresh',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/farm-state',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/latest',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/timeseries',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/raster.png',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/raster/queue',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/refresh',
				'/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/farm-state',
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
				'/apps/farm_intelligence_platform/api/v1/admin/radio/emergency',
				'/apps/farm_intelligence_platform/api/v1/admin/radio/emergency/__PK__',
				'/apps/farm_intelligence_platform/api/v1/admin/radio/emergency/__PK__',
				'/apps/farm_intelligence_platform/api/v1/admin/radio/tts',
			);

		$settings = new AdminSettings('farm_intelligence_platform', $l10n, $appConfig, $integrationConfig, $urlGenerator);
		return $settings->getForm();
	}
}
