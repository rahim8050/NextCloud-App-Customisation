<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Controller;

use OCA\WeatherApis\Controller\AdminFarmsController;
use OCA\WeatherApis\Service\DrfSchemaService;
use OCA\WeatherApis\Service\WeatherApiClientInterface;
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
	public function testListFarmsReturnsPayload(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');
		$request->method('getParams')->willReturn([
			'page' => 2,
			'_route' => 'weather_apis.adminFarms.listFarms',
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
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');
		$request->method('getParams')->willReturn([
			'page' => 3,
			'ordering' => 'name',
			'_route' => 'weather_apis.adminFarms.listFarms',
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
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');

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
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');

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
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');
		$request->method('getParams')->willReturn([
			'id' => 99,
			'created_at' => '2025-01-01T00:00:00Z',
			'name' => 'Test Farm',
			'_route' => 'weather_apis.adminFarms.createFarm',
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

	public function testRasterPngReturnsBinary(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');
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

	public function testNdviLatestStripsFarmIdFromQuery(): void {
		$schema = $this->createSchema();

		$request = $this->createMock(IRequest::class);
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');
		$request->method('getParams')->willReturn([
			'farmId' => '19',
			'date' => '2024-02-01',
			'_route' => 'weather_apis.adminFarms.getNdviLatest',
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
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');
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
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');
		$request->method('getParams')->willReturn([
			'_route' => 'weather_apis.adminFarms.getNdviTimeseries',
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
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');
		$request->method('getParams')->willReturn([
			'start' => '2024-02-10',
			'end' => '2024-02-01',
			'_route' => 'weather_apis.adminFarms.getNdviTimeseries',
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
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');
		$request->method('getParams')->willReturn([
			'_route' => 'weather_apis.adminFarms.queueNdviRaster',
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
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');
		$request->method('getParams')->willReturn([
			'_route' => 'weather_apis.adminFarms.getNdviRasterPng',
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
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');
		$request->method('getParams')->willReturn([
			'farmId' => '19',
			'date' => '2024-02-01',
			'_route' => 'weather_apis.adminFarms.getNdviLatest',
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

	public function testMethodsRequireAdmin(): void {
		$reflection = new \ReflectionMethod(AdminFarmsController::class, 'listFarms');
		$this->assertNotEmpty($reflection->getAttributes(AdminRequired::class));
	}

	private function createController(
		IRequest $request,
		WeatherApiClientInterface $client,
		?LoggerInterface $logger = null,
	): AdminFarmsController {
		$schemaService = $this->createSchemaService($client);
		$logger = $logger ?? $this->createMock(LoggerInterface::class);

		return new AdminFarmsController(
			'weather_apis',
			$request,
			$schemaService,
			$client,
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
