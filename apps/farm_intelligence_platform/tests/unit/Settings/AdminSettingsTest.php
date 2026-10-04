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
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndwi/state', $response->getParams()['farmNdwiFarmStateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/latest', $response->getParams()['farmNdmiLatestUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/timeseries', $response->getParams()['farmNdmiTimeseriesUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/raster.png', $response->getParams()['farmNdmiRasterUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/raster/queue', $response->getParams()['farmNdmiRasterQueueUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/refresh', $response->getParams()['farmNdmiRefreshUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/ndmi/state', $response->getParams()['farmNdmiFarmStateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/latest', $response->getParams()['farmRviLatestUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/timeseries', $response->getParams()['farmRviTimeseriesUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/raster.png', $response->getParams()['farmRviRasterUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/raster/queue', $response->getParams()['farmRviRasterQueueUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/refresh', $response->getParams()['farmRviRefreshUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/rvi/state', $response->getParams()['farmRviFarmStateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/latest', $response->getParams()['farmS1SmiLatestUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/timeseries', $response->getParams()['farmS1SmiTimeseriesUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/raster.png', $response->getParams()['farmS1SmiRasterUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/raster/queue', $response->getParams()['farmS1SmiRasterQueueUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/refresh', $response->getParams()['farmS1SmiRefreshUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/s1_smi/state', $response->getParams()['farmS1SmiFarmStateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/evi/latest', $response->getParams()['farmEviLatestUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/evi/timeseries', $response->getParams()['farmEviTimeseriesUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/evi/raster.png', $response->getParams()['farmEviRasterUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/evi/raster/queue', $response->getParams()['farmEviRasterQueueUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/evi/refresh', $response->getParams()['farmEviRefreshUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/evi/state', $response->getParams()['farmEviFarmStateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/l_rvi/latest', $response->getParams()['farmLRviLatestUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/l_rvi/timeseries', $response->getParams()['farmLRviTimeseriesUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/l_rvi/raster.png', $response->getParams()['farmLRviRasterUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/l_rvi/raster/queue', $response->getParams()['farmLRviRasterQueueUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/l_rvi/refresh', $response->getParams()['farmLRviRefreshUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/l_rvi/state', $response->getParams()['farmLRviFarmStateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/l_rvi/geotiff', $response->getParams()['farmLRviGeotiffUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/nisar_smi/latest', $response->getParams()['farmNisarSmiLatestUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/nisar_smi/timeseries', $response->getParams()['farmNisarSmiTimeseriesUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/nisar_smi/raster.png', $response->getParams()['farmNisarSmiRasterUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/nisar_smi/raster/queue', $response->getParams()['farmNisarSmiRasterQueueUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/nisar_smi/refresh', $response->getParams()['farmNisarSmiRefreshUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/nisar_smi/state', $response->getParams()['farmNisarSmiFarmStateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/nisar_smi/geotiff', $response->getParams()['farmNisarSmiGeotiffUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/weather/current', $response->getParams()['farmWeatherCurrentUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/weather/hourly', $response->getParams()['farmWeatherHourlyUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/weather/daily', $response->getParams()['farmWeatherDailyUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/state', $response->getParams()['farmStateUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/decision', $response->getParams()['farmDecisionUrl']);
		$this->assertSame('/apps/farm_intelligence_platform/api/v1/admin/farms/__FARM_ID__/forecast', $response->getParams()['farmForecastUrl']);
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
		$routesConfig = require __DIR__ . '/../../../appinfo/routes.php';
		$routesMap = [];
		foreach (array_merge($routesConfig['routes'] ?? [], $routesConfig['ocs'] ?? []) as $r) {
			if (!isset($r['name']) || !isset($r['url'])) {
				continue;
			}
			$name = str_replace('#', '.', (string)$r['name']);
			$routesMap['farm_intelligence_platform.' . $name] = (string)$r['url'];
		}

		$urlGenerator = $this->createMock(IURLGenerator::class);
		$urlGenerator->method('linkToRoute')
			->willReturnCallback(function (string $route, array $parameters = []) use ($routesMap): string {
				$url = $routesMap[$route] ?? '';
				foreach ($parameters as $k => $v) {
					$url = str_replace(['{' . $k . '}', '{' . strtolower($k) . '}'], (string)$v, $url);
				}
				return '/apps/farm_intelligence_platform' . $url;
			});

		$settings = new AdminSettings('farm_intelligence_platform', $l10n, $appConfig, $integrationConfig, $urlGenerator);
		return $settings->getForm();
	}
}
