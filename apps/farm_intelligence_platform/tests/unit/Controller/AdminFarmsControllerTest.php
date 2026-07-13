<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Tests\Unit\Controller;

use OCA\FarmIntelligencePlatform\Controller\AdminFarmsController;
use OCA\FarmIntelligencePlatform\Service\DrfSchemaService;
use OCA\FarmIntelligencePlatform\Service\FarmSyncServiceInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Response;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class AdminFarmsControllerTest extends TestCase {
	/**
	 * @param IRequest&\PHPUnit\Framework\MockObject\MockObject $request
	 */
	private function stubRequestHeaders($request, string $requestId = 'request-id', string $idempotencyKey = ''): void {
		$request->method('getHeader')
			->willReturnCallback(static function (string $name) use ($requestId, $idempotencyKey): string {
				if ($name === 'X-Request-Id') {
					return $requestId;
				}
				if ($name === 'Idempotency-Key') {
					return $idempotencyKey;
				}

				return '';
			});
	}

	public function testListFarmsReturnsPayload(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'page' => 2,
			'_route' => 'farm_intelligence_platform.adminFarms.listFarms',
			'OCS-APIRequest' => 'true',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/farms/', ['page' => 2], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->listFarms();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame(['results' => []], $data['data']);
	}

	public function testListFarmsPassesThroughQueryParamsWhenSchemaMissing(): void {
		$schema = $this->createSchema();
		unset($schema['paths']['/api/v1/farms/']['get']['parameters']);

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'page' => 3,
			'ordering' => 'name',
			'_route' => 'farm_intelligence_platform.adminFarms.listFarms',
			'OCS-APIRequest' => 'true',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/farms/', ['page' => 3, 'ordering' => 'name'], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->listFarms();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame(['results' => []], $data['data']);
	}

	public function testGetSchemaReturnsFieldsAndColumns(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->with('request-id')
			->willReturn($schema);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getSchema();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertArrayHasKey('fields', $data['data']);
		$this->assertArrayHasKey('columns', $data['data']);
		$this->assertContains('name', $data['data']['columns']);
	}

	public function testGetSchemaReturnsErrorWhenFieldsMissing(): void {
		$schema = [
			'openapi' => '3.0.0',
			'paths' => [],
			'components' => ['schemas' => []],
		];

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->with('request-id')
			->willReturn($schema);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getSchema();
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('backend_error', $data['error']['code']);
		$this->assertNotEmpty($data['error']['message']);
	}

	public function testCreateFarmFiltersReadOnlyFields(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'id' => 99,
			'created_at' => '2025-01-01T00:00:00Z',
			'name' => 'Test Farm',
			'_route' => 'farm_intelligence_platform.adminFarms.createFarm',
			'OCS-APIRequest' => 'true',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('POST', '/api/v1/farms/', [], ['name' => 'Test Farm'], 'request-id')
			->willReturn(['payload' => ['id' => 1, 'name' => 'Test Farm'], 'statusCode' => 201]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->createFarm();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame('Test Farm', $data['data']['name']);
	}

	public function testSyncFarmCallsService(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'external_farm_id' => 'farm-uuid',
			'external_user_id' => 'nc-user',
			'name' => 'north-field',
			'bbox' => [
				'south' => -1.234,
				'west' => 36.812,
				'north' => -1.220,
				'east' => 36.830,
			],
			'centroid' => [
				'lat' => -1.227,
				'lon' => 36.820,
			],
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$farmSyncService = $this->createMock(FarmSyncServiceInterface::class);
		$farmSyncService->expects($this->once())
			->method('sync')
			->with($this->isType('array'), 'request-id', null)
			->willReturn(['ok' => true]);

		$controller = $this->createController($request, $weatherApiClient, null, $farmSyncService);
		$response = $controller->syncFarm();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame(['ok' => true], $data['data']);
	}

	public function testRasterPngReturnsBinary(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'date' => '2024-02-01',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestBinary')
			->with('GET', '/api/v1/farms/55/ndvi/raster.png', ['date' => '2024-02-01'], 'request-id')
			->willReturn([
				'body' => 'png-bytes',
				'contentType' => 'image/png',
				'statusCode' => 200,
			]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdviRasterPng('55');

		$this->assertInstanceOf(DataDisplayResponse::class, $response);
		$this->assertSame('png-bytes', $response->getData());
		$headers = $this->getResponseHeaders($response);
		$this->assertSame('image/png', $headers['Content-Type'] ?? '');
	}

	public function testRasterPngPassesExternalFarmId(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'date' => '2024-02-01',
			'external_farm_id' => 'farm-uuid',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestBinary')
			->with(
				'GET',
				'/api/v1/farms/55/ndvi/raster.png',
				['date' => '2024-02-01', 'external_farm_id' => 'farm-uuid'],
				'request-id',
			)
			->willReturn([
				'body' => 'png-bytes',
				'contentType' => 'image/png',
				'statusCode' => 200,
			]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdviRasterPng('55');

		$this->assertInstanceOf(DataDisplayResponse::class, $response);
	}

	public function testRasterQueuePassesExternalFarmId(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'date' => '2024-02-01',
			'external_farm_id' => 'farm-uuid',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with(
				'POST',
				'/api/v1/farms/55/ndvi/raster/queue',
				['external_farm_id' => 'farm-uuid'],
				['date' => '2024-02-01'],
				'request-id',
			)
			->willReturn(['payload' => ['ok' => true], 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->queueNdviRaster('55');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testNdviLatestStripsFarmIdFromQuery(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'farmId' => '19',
			'date' => '2024-02-01',
			'_route' => 'farm_intelligence_platform.adminFarms.getNdviLatest',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/farms/19/ndvi/latest', ['date' => '2024-02-01'], null, 'request-id')
			->willReturn(['payload' => ['data' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdviLatest('19');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testNdviLatestRejectsInvalidFarmId(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->never())->method('fetchSchema');
		$weatherApiClient->expects($this->never())->method('requestJsonWithStatus');

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdviLatest('invalid-id');

		$this->assertInstanceOf(JSONResponse::class, $response);
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('invalid_argument', $data['error']['code']);
	}

	public function testNdviTimeseriesRequiresStartEnd(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'_route' => 'farm_intelligence_platform.adminFarms.getNdviTimeseries',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->never())->method('requestJsonWithStatus');

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdviTimeseries('19');
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('invalid_argument', $data['error']['code']);
	}

	public function testNdviTimeseriesRejectsStartAfterEnd(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'start' => '2024-02-10',
			'end' => '2024-02-01',
			'_route' => 'farm_intelligence_platform.adminFarms.getNdviTimeseries',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->never())->method('requestJsonWithStatus');

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdviTimeseries('19');
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('invalid_argument', $data['error']['code']);
	}

	public function testNdviRasterQueueRequiresDate(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'_route' => 'farm_intelligence_platform.adminFarms.queueNdviRaster',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->never())->method('requestJsonWithStatus');

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->queueNdviRaster('19');
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('invalid_argument', $data['error']['code']);
	}

	public function testNdviRasterPngRequiresDate(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'_route' => 'farm_intelligence_platform.adminFarms.getNdviRasterPng',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->never())->method('requestBinary');

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdviRasterPng('19');
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('invalid_argument', $data['error']['code']);
	}

	public function testNdviLatestLogsQueryKeysWithoutFarmId(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'farmId' => '19',
			'date' => '2024-02-01',
			'_route' => 'farm_intelligence_platform.adminFarms.getNdviLatest',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/farms/19/ndvi/latest', ['date' => '2024-02-01'], null, 'request-id')
			->willReturn(['payload' => ['data' => []], 'statusCode' => 200]);

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->exactly(3))
			->method('debug')
			->withConsecutive(
				[
					'Weather API admin endpoint hit',
					$this->isType('array'),
				],
				[
					'Weather API admin proxy request',
					$this->callback(function (array $context): bool {
						$queryKeys = $context['queryKeys'] ?? [];
						return is_array($queryKeys)
							&& in_array('date', $queryKeys, true)
							&& !in_array('farmId', $queryKeys, true);
					}),
				],
				[
					'Weather API admin proxy response',
					$this->isType('array'),
				],
			);

		$controller = $this->createController($request, $weatherApiClient, $logger);
		$response = $controller->getNdviLatest('19');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testWeatherCurrentReturnsPayload(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($this->createSchema());
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/farms/42/weather/current/', [], null, 'request-id')
			->willReturn([
				'payload' => ['status' => 0, 'message' => 'OK', 'data' => ['temperature_c' => 22.0]],
				'statusCode' => 200,
			]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getWeatherCurrent('42');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertEquals(22.0, $data['data']['data']['temperature_c']);
	}

	public function testWeatherCurrentMapsUpstreamFailure(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($this->createSchema());
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->willThrowException(new WeatherApiException('backend_error', 'Boom'));

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getWeatherCurrent('42');
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('backend_error', $data['error']['code']);
		$this->assertSame(400, $response->getStatus());
	}

	public function testWeatherHourlyReturnsPayload(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'hours' => 24,
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($this->createSchema());
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/farms/7/weather/hourly/', ['hours' => 24], null, 'request-id')
			->willReturn([
				'payload' => ['status' => 0, 'message' => 'OK', 'data' => ['hours' => []]],
				'statusCode' => 200,
			]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getWeatherHourly('7');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame([], $data['data']['data']['hours']);
	}

	public function testWeatherHourlyMapsUpstreamFailure(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'hours' => 24,
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($this->createSchema());
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->willThrowException(new WeatherApiException('backend_timeout', 'Timeout'));

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getWeatherHourly('7');
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('backend_timeout', $data['error']['code']);
		$this->assertSame(504, $response->getStatus());
	}

	public function testWeatherDailyReturnsPayload(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'days' => 5,
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($this->createSchema());
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/farms/8/weather/daily/', ['days' => 5], null, 'request-id')
			->willReturn([
				'payload' => ['status' => 0, 'message' => 'OK', 'data' => ['forecasts' => []]],
				'statusCode' => 200,
			]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getWeatherDaily('8');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame([], $data['data']['data']['forecasts']);
	}

	public function testWeatherDailyMapsUpstreamFailure(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'days' => 5,
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($this->createSchema());
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->willThrowException(new WeatherApiException('backend_error', 'Boom'));

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getWeatherDaily('8');
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('backend_error', $data['error']['code']);
		$this->assertSame(400, $response->getStatus());
	}

	public function testFarmStateReturnsPayload(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($this->createSchema());
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/farm-state/9/', [], null, 'request-id')
			->willReturn([
				'payload' => ['success' => 0, 'message' => 'Farm state', 'data' => [
					'farm_id' => 9,
					'mean_ndvi' => 0.45,
					'max_ndvi' => 0.72,
					'coverage_pct' => 65.3,
					'trend' => 0.02,
					'state' => 'full_canopy',
					'interpretation' => 'Dense canopy detected.',
					'action' => 'Maintain current management.',
				]],
				'statusCode' => 200,
			]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getFarmState('9');
		$data = $this->decodeResponse($response);

		$this->assertSame(0, $data['data']['success']);
		$this->assertSame('full_canopy', $data['data']['data']['state']);
		$this->assertSame(0.45, $data['data']['data']['mean_ndvi']);
	}

	public function testFarmStateMapsUpstreamFailure(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($this->createSchema());
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->willThrowException(new WeatherApiException('backend_error', 'Boom'));

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getFarmState('9');
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('backend_error', $data['error']['code']);
		$this->assertSame(400, $response->getStatus());
	}

	public function testListFarmObservationsPassesPagination(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'limit' => 25,
			'offset' => 50,
			'event_type' => 'irrigation',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with(
				'GET',
				'/api/v1/farms/88/observations/',
				['limit' => 25, 'offset' => 50, 'event_type' => 'irrigation'],
				null,
				'request-id',
			)
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->listFarmObservations('88');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame(['results' => []], $data['data']);
	}

	public function testCreateFarmObservationCallsBackend(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'observed_at' => '2026-03-01T10:00:00Z',
			'event_type' => 'irrigation',
			'note' => 'manual',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with(
				'POST',
				'/api/v1/farms/88/observations/',
				[],
				[
					'observed_at' => '2026-03-01T10:00:00Z',
					'event_type' => 'irrigation',
					'note' => 'manual',
				],
				'request-id',
			)
			->willReturn(['payload' => ['id' => 1], 'statusCode' => 201]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->createFarmObservation('88');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame(['id' => 1], $data['data']);
	}

	public function testGetFarmObservationCallsBackend(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/farms/88/observations/9/', [], null, 'request-id')
			->willReturn(['payload' => ['id' => 9], 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getFarmObservation('88', '9');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame(['id' => 9], $data['data']);
	}

	public function testPatchFarmObservationCallsBackend(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'note' => 'updated',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with(
				'PATCH',
				'/api/v1/farms/88/observations/9/',
				[],
				['note' => 'updated'],
				'request-id',
			)
			->willReturn(['payload' => ['id' => 9], 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->patchFarmObservation('88', '9');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame(['id' => 9], $data['data']);
	}

	public function testDeleteFarmObservationCallsBackend(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('DELETE', '/api/v1/farms/88/observations/9/', [], null, 'request-id')
			->willReturn(['payload' => null, 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->deleteFarmObservation('88', '9');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertNull($data['data']);
	}

	public function testNdwiLatestStripsFarmIdFromQuery(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'farmId' => '42',
			'date' => '2024-06-01',
			'_route' => 'farm_intelligence_platform.adminFarms.getNdwiLatest',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/farms/42/ndwi/latest/', ['date' => '2024-06-01'], null, 'request-id')
			->willReturn(['payload' => ['data' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdwiLatest('42');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testNdwiLatestRejectsInvalidFarmId(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->never())->method('fetchSchema');
		$weatherApiClient->expects($this->never())->method('requestJsonWithStatus');

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdwiLatest('invalid');

		$this->assertInstanceOf(JSONResponse::class, $response);
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('invalid_argument', $data['error']['code']);
	}

	public function testNdwiLatestMapsUpstreamFailure(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->willThrowException(new WeatherApiException('backend_error', 'Boom'));

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdwiLatest('42');
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('backend_error', $data['error']['code']);
		$this->assertSame(400, $response->getStatus());
	}

	public function testNdwiTimeseriesRequiresStartEnd(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'_route' => 'farm_intelligence_platform.adminFarms.getNdwiTimeseries',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->never())->method('requestJsonWithStatus');

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdwiTimeseries('7');
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('invalid_argument', $data['error']['code']);
	}

	public function testNdwiTimeseriesRejectsStartAfterEnd(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'start' => '2024-02-10',
			'end' => '2024-02-01',
			'_route' => 'farm_intelligence_platform.adminFarms.getNdwiTimeseries',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->never())->method('requestJsonWithStatus');

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdwiTimeseries('7');
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('invalid_argument', $data['error']['code']);
	}

	public function testRefreshNdwiCallsBackend(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('POST', '/api/v1/farms/42/ndwi/refresh/', [], null, 'request-id')
			->willReturn(['payload' => ['data' => ['status' => 'queued']], 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->refreshNdwi('42');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame('queued', $data['data']['data']['status']);
	}

	public function testGetNdwiRasterPngRequiresDate(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'_route' => 'farm_intelligence_platform.adminFarms.getNdwiRasterPng',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->never())->method('requestBinary');

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdwiRasterPng('55');

		$this->assertInstanceOf(JSONResponse::class, $response);
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('invalid_argument', $data['error']['code']);
	}

	public function testGetNdwiRasterPngReturnsBinary(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'date' => '2024-06-01',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestBinary')
			->with('GET', '/api/v1/farms/55/ndwi/raster.png', ['date' => '2024-06-01'], 'request-id')
			->willReturn([
				'body' => 'png-bytes',
				'contentType' => 'image/png',
				'statusCode' => 200,
			]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdwiRasterPng('55');

		$this->assertInstanceOf(DataDisplayResponse::class, $response);
		$this->assertSame('png-bytes', $response->getData());
		$headers = $this->getResponseHeaders($response);
		$this->assertSame('image/png', $headers['Content-Type'] ?? '');
	}

	public function testQueueNdwiRasterRequiresDate(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'_route' => 'farm_intelligence_platform.adminFarms.queueNdwiRaster',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->never())->method('requestJsonWithStatus');

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->queueNdwiRaster('55');
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('invalid_argument', $data['error']['code']);
	}

	public function testGetNdwiFarmStateReturnsPayload(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/farms/9/ndwi/farm-state/', [], null, 'request-id')
			->willReturn([
				'payload' => ['success' => 0, 'data' => ['mean_ndwi' => -0.15, 'state' => 'water_body']],
				'statusCode' => 200,
			]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdwiFarmState('9');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame(-0.15, $data['data']['data']['mean_ndwi']);
		$this->assertSame('water_body', $data['data']['data']['state']);
	}

	public function testGetNdwiFarmStateMapsUpstreamFailure(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->willThrowException(new WeatherApiException('backend_error', 'Boom'));

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdwiFarmState('9');
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('backend_error', $data['error']['code']);
		$this->assertSame(400, $response->getStatus());
	}

	public function testNdwiLatestLogsQueryKeysWithoutFarmId(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getParams')->willReturn([
			'farmId' => '19',
			'date' => '2024-02-01',
			'_route' => 'farm_intelligence_platform.adminFarms.getNdwiLatest',
		]);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($schema);
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/farms/19/ndwi/latest/', ['date' => '2024-02-01'], null, 'request-id')
			->willReturn(['payload' => ['data' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdwiLatest('19');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testGetNdviJobStatusReturnsPayload(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getPathInfo')->willReturn('');
		$request->method('getRequestUri')->willReturn('');

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($this->createSchema());
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/ndvi/jobs/42/', [], null, 'request-id')
			->willReturn(['payload' => ['success' => 0, 'data' => ['id' => 42, 'status' => 'completed']], 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdviJobStatus(42);
		$data = $this->decodeResponse($response);

		$this->assertSame('completed', $data['data']['data']['status']);
	}

	public function testNdviIngestCallsBackend(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('POST');
		$request->method('getPathInfo')->willReturn('');
		$request->method('getRequestUri')->willReturn('');
		$request->method('getParams')->willReturn(['scene_id' => 'L8-123']);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($this->createSchema());
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('POST', '/api/v1/ndvi', [], $this->anything(), 'request-id')
			->willReturn(['payload' => ['success' => 0, 'data' => ['status' => 'queued']], 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->ndviIngest();
		$rendered = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('queued', $rendered['data']['data']['status']);
	}

	public function testResetNdviCircuitBreakerCallsBackend(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('POST');
		$request->method('getPathInfo')->willReturn('');
		$request->method('getRequestUri')->willReturn('');

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($this->createSchema());
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('POST', '/api/v1/ndvi/circuit-breaker/reset/', [], null, 'request-id')
			->willReturn(['payload' => ['success' => 0, 'message' => 'Circuit breaker reset'], 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->resetNdviCircuitBreaker();
		$data = $this->decodeResponse($response);

		$this->assertSame('Circuit breaker reset', $data['data']['message']);
	}

	public function testGetNdviUpstreamHealthReturnsPayload(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getPathInfo')->willReturn('');
		$request->method('getRequestUri')->willReturn('');

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('fetchSchema')
			->willReturn($this->createSchema());
		$weatherApiClient->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/ndvi/health/upstream/', [], null, 'request-id')
			->willReturn(['payload' => ['success' => 0, 'data' => ['healthy' => true]], 'statusCode' => 200]);

		$controller = $this->createController($request, $weatherApiClient);
		$response = $controller->getNdviUpstreamHealth();
		$data = $this->decodeResponse($response);

		$this->assertTrue($data['data']['data']['healthy']);
	}

	public function testMethodsRequireAdmin(): void {
		$reflection = new \ReflectionMethod(AdminFarmsController::class, 'listFarms');
		$this->assertNotEmpty($reflection->getAttributes(AdminRequired::class));
	}

	private function createController(
		IRequest $request,
		WeatherApiClientInterface $client,
		?LoggerInterface $logger = null,
		?FarmSyncServiceInterface $farmSyncService = null,
	): AdminFarmsController {
		$schemaService = $this->createSchemaService($client);
		$logger = $logger ?? $this->createMock(LoggerInterface::class);
		$farmSyncService = $farmSyncService ?? $this->createMock(FarmSyncServiceInterface::class);

		return new AdminFarmsController(
			'farm_intelligence_platform',
			$request,
			$schemaService,
			$client,
			$farmSyncService,
			$logger,
		);
	}

	private function createSchemaService(WeatherApiClientInterface $client): DrfSchemaService {
		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn(null);
		$cache->method('set')->willReturn(true);

		$cacheFactory = $this->createMock(ICacheFactory::class);
		$cacheFactory->method('createDistributed')->willReturn($cache);

		$logger = $this->createMock(LoggerInterface::class);

		return new DrfSchemaService($client, $cacheFactory, $logger);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function createSchema(): array {
		$farmSchema = [
			'type' => 'object',
			'properties' => [
				'id' => ['type' => 'integer', 'readOnly' => true],
				'slug' => ['type' => 'string', 'readOnly' => true],
				'name' => ['type' => 'string'],
				'created_at' => ['type' => 'string', 'readOnly' => true],
			],
			'required' => ['id', 'slug', 'name', 'created_at'],
		];

		$farmWriteSchema = [
			'type' => 'object',
			'properties' => [
				'id' => ['type' => 'integer', 'readOnly' => true],
				'name' => ['type' => 'string'],
				'created_at' => ['type' => 'string', 'readOnly' => true],
			],
			'required' => ['id', 'name', 'created_at'],
		];

		$body = [
			'content' => [
				'application/json' => [
					'schema' => $farmWriteSchema,
				],
			],
		];

		return [
			'components' => [
				'schemas' => [
					'Farm' => $farmSchema,
				],
			],
			'paths' => [
				'/api/v1/farms/' => [
					'get' => [
						'operationId' => 'v1_farms_list',
						'parameters' => [
							[
								'name' => 'page',
								'in' => 'query',
								'schema' => ['type' => 'integer'],
							],
						],
					],
					'post' => [
						'operationId' => 'v1_farms_create',
						'requestBody' => $body,
					],
				],
				'/api/v1/farms/{id}/' => [
					'get' => [
						'operationId' => 'v1_farms_retrieve',
					],
					'put' => [
						'operationId' => 'v1_farms_update',
						'requestBody' => $body,
					],
					'patch' => [
						'operationId' => 'v1_farms_partial_update',
						'requestBody' => $body,
					],
					'delete' => [
						'operationId' => 'v1_farms_destroy',
					],
				],
				'/api/v1/farms/{farm_id}/ndvi/latest' => [
					'get' => [
						'operationId' => 'v1_farms_ndvi_latest_retrieve',
						'parameters' => [
							[
								'name' => 'date',
								'in' => 'query',
								'schema' => ['type' => 'string'],
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/ndvi/timeseries' => [
					'get' => [
						'operationId' => 'v1_farms_ndvi_timeseries_retrieve',
						'parameters' => [
							[
								'name' => 'start',
								'in' => 'query',
								'schema' => ['type' => 'string'],
								'required' => true,
							],
							[
								'name' => 'end',
								'in' => 'query',
								'schema' => ['type' => 'string'],
								'required' => true,
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/ndvi/raster.png' => [
					'get' => [
						'operationId' => 'v1_farms_ndvi_raster.png_retrieve',
						'parameters' => [
							[
								'name' => 'date',
								'in' => 'query',
								'schema' => ['type' => 'string'],
								'required' => true,
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/ndvi/raster/queue' => [
					'post' => [
						'operationId' => 'v1_farms_ndvi_raster_queue_create',
						'requestBody' => [
							'content' => [
								'application/json' => [
									'schema' => [
										'type' => 'object',
										'required' => ['date'],
										'properties' => [
											'date' => ['type' => 'string'],
										],
									],
								],
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/ndvi/refresh' => [
					'post' => [
						'operationId' => 'v1_farms_ndvi_refresh_create',
						'requestBody' => [
							'content' => [
								'application/json' => [
									'schema' => [
										'type' => 'object',
										'properties' => [
											'date' => ['type' => 'string'],
										],
									],
								],
							],
						],
					],
				],
				'/api/v1/farm-state/{farm_id}/' => [
					'get' => [
						'operationId' => 'v1_farm_state_retrieve',
					],
				],
				'/api/v1/farms/{farm_id}/ndwi/latest/' => [
					'get' => [
						'operationId' => 'v1_farms_ndwi_latest_retrieve',
						'parameters' => [
							[
								'name' => 'date',
								'in' => 'query',
								'schema' => ['type' => 'string'],
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/ndwi/timeseries/' => [
					'get' => [
						'operationId' => 'v1_farms_ndwi_timeseries_retrieve',
						'parameters' => [
							[
								'name' => 'start',
								'in' => 'query',
								'schema' => ['type' => 'string'],
								'required' => true,
							],
							[
								'name' => 'end',
								'in' => 'query',
								'schema' => ['type' => 'string'],
								'required' => true,
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/ndwi/raster.png' => [
					'get' => [
						'operationId' => 'v1_farms_ndwi_raster.png_retrieve',
						'parameters' => [
							[
								'name' => 'date',
								'in' => 'query',
								'schema' => ['type' => 'string'],
								'required' => true,
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/ndwi/raster/queue' => [
					'post' => [
						'operationId' => 'v1_farms_ndwi_raster_queue_create',
						'requestBody' => [
							'content' => [
								'application/json' => [
									'schema' => [
										'type' => 'object',
										'required' => ['date'],
										'properties' => [
											'date' => ['type' => 'string'],
										],
									],
								],
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/ndwi/refresh/' => [
					'post' => [
						'operationId' => 'v1_farms_ndwi_refresh_create',
						'requestBody' => [
							'content' => [
								'application/json' => [
									'schema' => [
										'type' => 'object',
										'properties' => [
											'date' => ['type' => 'string'],
										],
									],
								],
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/ndwi/farm-state/' => [
					'get' => [
						'operationId' => 'v1_farms_ndwi_farm_state_retrieve',
					],
				],
				'/api/v1/farms/{farm_id}/ndmi/latest/' => [
					'get' => [
						'operationId' => 'v1_farms_ndmi_latest_retrieve',
					],
				],
				'/api/v1/farms/{farm_id}/ndmi/timeseries/' => [
					'get' => [
						'operationId' => 'v1_farms_ndmi_timeseries_retrieve',
						'parameters' => [
							[
								'name' => 'start',
								'in' => 'query',
								'schema' => ['type' => 'string'],
								'required' => true,
							],
							[
								'name' => 'end',
								'in' => 'query',
								'schema' => ['type' => 'string'],
								'required' => true,
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/ndmi/raster.png' => [
					'get' => [
						'operationId' => 'v1_farms_ndmi_raster.png_retrieve',
						'parameters' => [
							[
								'name' => 'date',
								'in' => 'query',
								'schema' => ['type' => 'string'],
								'required' => true,
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/ndmi/raster/queue' => [
					'post' => [
						'operationId' => 'v1_farms_ndmi_raster_queue_create',
						'requestBody' => [
							'content' => [
								'application/json' => [
									'schema' => [
										'type' => 'object',
										'required' => ['date'],
										'properties' => [
											'date' => ['type' => 'string'],
										],
									],
								],
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/ndmi/refresh/' => [
					'post' => [
						'operationId' => 'v1_farms_ndmi_refresh_create',
						'requestBody' => [
							'content' => [
								'application/json' => [
									'schema' => [
										'type' => 'object',
										'properties' => [
											'date' => ['type' => 'string'],
										],
									],
								],
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/ndmi/farm-state/' => [
					'get' => [
						'operationId' => 'v1_farms_ndmi_farm_state_retrieve',
					],
				],
				'/api/v1/farms/{farm_id}/observations/' => [
					'get' => [
						'operationId' => 'v1_farms_observations_list',
						'parameters' => [
							[
								'name' => 'start',
								'in' => 'query',
								'schema' => ['type' => 'string'],
							],
							[
								'name' => 'end',
								'in' => 'query',
								'schema' => ['type' => 'string'],
							],
							[
								'name' => 'event_type',
								'in' => 'query',
								'schema' => ['type' => 'string'],
							],
							[
								'name' => 'limit',
								'in' => 'query',
								'schema' => ['type' => 'integer'],
							],
							[
								'name' => 'offset',
								'in' => 'query',
								'schema' => ['type' => 'integer'],
							],
						],
					],
					'post' => [
						'operationId' => 'v1_farms_observations_create',
						'requestBody' => [
							'content' => [
								'application/json' => [
									'schema' => [
										'type' => 'object',
										'required' => ['observed_at', 'event_type'],
										'properties' => [
											'observed_at' => ['type' => 'string'],
											'event_type' => ['type' => 'string'],
											'note' => ['type' => 'string'],
										],
									],
								],
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/observations/{observation_id}/' => [
					'get' => [
						'operationId' => 'v1_farms_observations_retrieve',
					],
					'patch' => [
						'operationId' => 'v1_farms_observations_update',
						'requestBody' => [
							'content' => [
								'application/json' => [
									'schema' => [
										'type' => 'object',
										'properties' => [
											'observed_at' => ['type' => 'string'],
											'event_type' => ['type' => 'string'],
											'note' => ['type' => 'string'],
										],
									],
								],
							],
						],
					],
					'delete' => [
						'operationId' => 'v1_farms_observations_delete',
					],
				],
				'/api/v1/farms/{farm_id}/activities/' => [
					'get' => [
						'operationId' => 'v1_farms_activities_list',
					],
					'post' => [
						'operationId' => 'v1_farms_activities_create',
					],
				],
				'/api/v1/farms/{farm_id}/activities/{id}/' => [
					'get' => [
						'operationId' => 'v1_farms_activities_retrieve',
					],
					'put' => [
						'operationId' => 'v1_farms_activities_update',
					],
					'delete' => [
						'operationId' => 'v1_farms_activities_delete',
					],
				],
				'/api/v1/farms/{farm_id}/weather/current/' => [
					'get' => [
						'operationId' => 'v1_farms_weather_current_retrieve',
					],
				],
				'/api/v1/farms/{farm_id}/weather/hourly/' => [
					'get' => [
						'operationId' => 'v1_farms_weather_hourly_retrieve',
						'parameters' => [
							[
								'name' => 'hours',
								'in' => 'query',
								'schema' => ['type' => 'integer'],
							],
						],
					],
				],
				'/api/v1/farms/{farm_id}/weather/daily/' => [
					'get' => [
						'operationId' => 'v1_farms_weather_daily_retrieve',
						'parameters' => [
							[
								'name' => 'days',
								'in' => 'query',
								'schema' => ['type' => 'integer'],
							],
						],
					],
				],
				'/api/v1/ndvi/jobs/{id}/' => [
					'get' => [
						'operationId' => 'v1_ndvi_jobs_retrieve',
					],
				],
				'/api/v1/ndvi' => [
					'post' => [
						'operationId' => 'v1_ndvi_create',
						'requestBody' => [
							'content' => [
								'application/json' => [
									'schema' => [
										'type' => 'object',
										'properties' => [
											'scene_id' => ['type' => 'string'],
										],
									],
								],
							],
						],
					],
				],
				'/api/v1/ndvi/circuit-breaker/reset/' => [
					'post' => [
						'operationId' => 'v1_ndvi_circuit_breaker_reset_create',
					],
				],
				'/api/v1/ndvi/health/upstream/' => [
					'get' => [
						'operationId' => 'v1_ndvi_health_upstream_retrieve',
					],
				],
			],
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	private function decodeResponse(JSONResponse $response): array {
		$data = json_decode(
			json_encode($response->getData(), JSON_THROW_ON_ERROR),
			true,
			512,
			JSON_THROW_ON_ERROR,
		);

		/** @var array<string, mixed> $data */
		return $data;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function getResponseHeaders(Response $response): array {
		$reflection = new \ReflectionProperty(Response::class, 'headers');
		$reflection->setAccessible(true);
		$headers = $reflection->getValue($response);

		return is_array($headers) ? $headers : [];
	}
}
