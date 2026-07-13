<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Tests\Unit\Service;

use OCA\FarmIntelligencePlatform\Service\DrfSchemaService;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\ICache;
use OCP\ICacheFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class DrfSchemaServiceTest extends TestCase {
	public function testGetFarmSchemaSummaryUnwrapsNestedSchema(): void {
		$schema = $this->createSchema();
		$wrapped = ['schema' => $schema];

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('fetchSchema')
			->with('request-id')
			->willReturn($wrapped);

		$service = $this->createService($client);
		$result = $service->getFarmSchemaSummary('request-id');

		$this->assertArrayHasKey('schema', $result);
		$this->assertArrayHasKey('fields', $result['schema']);
		$this->assertArrayHasKey('name', $result['schema']['fields']);
		$this->assertTrue($result['schema']['fields']['name']['required']);
		$this->assertArrayHasKey('columns', $result['schema']);
		$this->assertContains('name', $result['schema']['columns']);
		$this->assertArrayHasKey('fieldsCreate', $result['schema']);
		$this->assertArrayHasKey('name', $result['schema']['fieldsCreate']);
		$this->assertArrayNotHasKey('id', $result['schema']['fieldsCreate']);
		$this->assertArrayHasKey('fieldsUpdate', $result['schema']);
		$this->assertArrayHasKey('name', $result['schema']['fieldsUpdate']);
		$this->assertSame('/api/v1/farms/', $result['schema']['operations']['list']['path']);
		$this->assertSame('GET', $result['schema']['operations']['list']['method']);
	}

	public function testGetFarmSchemaSummaryFallsBackToCreateRequestBody(): void {
		$schema = $this->createSchemaWithCreateBody();
		$schema['components']['schemas']['Farm'] = ['type' => 'object'];

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('fetchSchema')
			->with('request-id')
			->willReturn($schema);

		$service = $this->createService($client);
		$result = $service->getFarmSchemaSummary('request-id');

		$this->assertArrayHasKey('fields', $result['schema']);
		$this->assertArrayHasKey('name', $result['schema']['fields']);
		$this->assertTrue($result['schema']['fields']['name']['required']);
		$this->assertArrayHasKey('fieldsCreate', $result['schema']);
		$this->assertArrayHasKey('name', $result['schema']['fieldsCreate']);
	}

	public function testGetFarmSchemaSummaryUsesPaginatedListResponse(): void {
		$schema = $this->createSchemaWithPaginatedList();

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('fetchSchema')
			->with('request-id')
			->willReturn($schema);

		$service = $this->createService($client);
		$result = $service->getFarmSchemaSummary('request-id');

		$this->assertArrayHasKey('fields', $result['schema']);
		$this->assertArrayHasKey('name', $result['schema']['fields']);
		$this->assertArrayHasKey('columns', $result['schema']);
		$this->assertContains('name', $result['schema']['columns']);
	}

	public function testGetFarmSchemaSummaryThrowsOnMissingFields(): void {
		$schema = [
			'openapi' => '3.0.0',
			'paths' => [],
			'components' => ['schemas' => []],
		];

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('fetchSchema')
			->with('request-id')
			->willReturn($schema);

		$service = $this->createService($client);

		$this->expectException(WeatherApiException::class);
		$service->getFarmSchemaSummary('request-id');
	}

	public function testGetFarmSchemaSummaryUsesCachedSchemaWithoutFetching(): void {
		$schema = $this->createSchema();
		$cached = json_encode($schema, JSON_THROW_ON_ERROR);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->never())
			->method('fetchSchema');

		$cache = $this->createMock(ICache::class);
		$cache->expects($this->once())
			->method('get')
			->with('drf_openapi_schema_json')
			->willReturn($cached);

		$service = $this->createServiceWithCache($client, $cache);
		$result = $service->getFarmSchemaSummary('request-id');

		$this->assertArrayHasKey('schema', $result);
		$this->assertArrayHasKey('fields', $result['schema']);
	}

	public function testGetFarmSchemaSummaryFetchesAndCachesOnMiss(): void {
		$schema = $this->createSchema();

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('fetchSchema')
			->with('request-id')
			->willReturn($schema);

		$cache = $this->createMock(ICache::class);
		$cache->expects($this->once())
			->method('get')
			->with('drf_openapi_schema_json')
			->willReturn(null);
		$cache->expects($this->once())
			->method('set')
			->with(
				'drf_openapi_schema_json',
				$this->isType('string'),
				3600,
			)
			->willReturn(true);

		$service = $this->createServiceWithCache($client, $cache);
		$service->getFarmSchemaSummary('request-id');
	}

	private function createService(WeatherApiClientInterface $client): DrfSchemaService {
		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn(null);
		$cache->method('set')->willReturn(true);

		return $this->createServiceWithCache($client, $cache);
	}

	private function createServiceWithCache(
		WeatherApiClientInterface $client,
		ICache $cache,
		?LoggerInterface $logger = null,
	): DrfSchemaService {
		$cacheFactory = $this->createMock(ICacheFactory::class);
		$cacheFactory->method('createDistributed')->willReturn($cache);

		$logger ??= $this->createMock(LoggerInterface::class);

		return new DrfSchemaService($client, $cacheFactory, $logger);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function createSchema(): array {
		$farmWrite = [
			'type' => 'object',
			'properties' => [
				'id' => ['type' => 'integer', 'readOnly' => true],
				'name' => ['type' => 'string'],
			],
			'required' => ['name'],
		];

		return [
			'openapi' => '3.0.0',
			'components' => [
				'schemas' => [
					'Farm' => [
						'type' => 'object',
						'properties' => [
							'id' => ['type' => 'integer', 'readOnly' => true],
							'name' => ['type' => 'string'],
						],
						'required' => ['id', 'name'],
					],
					'FarmWrite' => $farmWrite,
				],
			],
			'paths' => [
				'/api/v1/farms/' => [
					'get' => [
						'operationId' => 'v1_farms_list',
						'responses' => [
							'200' => [
								'content' => [
									'application/json' => [
										'schema' => [
											'type' => 'object',
											'properties' => [
												'count' => ['type' => 'integer'],
												'results' => [
													'type' => 'array',
													'items' => [
														'$ref' => '#/components/schemas/Farm',
													],
												],
											],
										],
									],
								],
							],
						],
					],
					'post' => [
						'operationId' => 'v1_farms_create',
						'requestBody' => [
							'content' => [
								'application/json' => [
									'schema' => [
										'$ref' => '#/components/schemas/FarmWrite',
									],
								],
							],
						],
					],
				],
				'/api/v1/farms/{id}/' => [
					'get' => ['operationId' => 'v1_farms_retrieve'],
					'put' => [
						'operationId' => 'v1_farms_update',
						'requestBody' => [
							'content' => [
								'application/json' => [
									'schema' => [
										'$ref' => '#/components/schemas/FarmWrite',
									],
								],
							],
						],
					],
					'patch' => [
						'operationId' => 'v1_farms_partial_update',
						'requestBody' => [
							'content' => [
								'application/json' => [
									'schema' => [
										'$ref' => '#/components/schemas/FarmWrite',
									],
								],
							],
						],
					],
					'delete' => ['operationId' => 'v1_farms_destroy'],
				],
				'/api/v1/farms/{farm_id}/ndvi/latest' => [
					'get' => ['operationId' => 'v1_farms_ndvi_latest_retrieve'],
				],
				'/api/v1/farms/{farm_id}/ndvi/timeseries' => [
					'get' => ['operationId' => 'v1_farms_ndvi_timeseries_retrieve'],
				],
				'/api/v1/farms/{farm_id}/ndvi/raster.png' => [
					'get' => ['operationId' => 'v1_farms_ndvi_raster.png_retrieve'],
				],
				'/api/v1/farms/{farm_id}/ndvi/raster/queue' => [
					'post' => ['operationId' => 'v1_farms_ndvi_raster_queue_create'],
				],
				'/api/v1/farms/{farm_id}/ndvi/refresh' => [
					'post' => ['operationId' => 'v1_farms_ndvi_refresh_create'],
				],
				'/api/v1/farms/{farm_id}/ndwi/latest/' => [
					'get' => ['operationId' => 'v1_farms_ndwi_latest_retrieve'],
				],
				'/api/v1/farms/{farm_id}/ndwi/timeseries/' => [
					'get' => ['operationId' => 'v1_farms_ndwi_timeseries_retrieve'],
				],
				'/api/v1/farms/{farm_id}/ndwi/raster.png' => [
					'get' => ['operationId' => 'v1_farms_ndwi_raster.png_retrieve'],
				],
				'/api/v1/farms/{farm_id}/ndwi/raster/queue' => [
					'post' => ['operationId' => 'v1_farms_ndwi_raster_queue_create'],
				],
				'/api/v1/farms/{farm_id}/ndwi/refresh/' => [
					'post' => ['operationId' => 'v1_farms_ndwi_refresh_create'],
				],
				'/api/v1/farms/{farm_id}/ndmi/latest/' => [
				'get' => ['operationId' => 'v1_farms_ndmi_latest_retrieve'],
			],
			'/api/v1/farms/{farm_id}/ndmi/timeseries/' => [
				'get' => ['operationId' => 'v1_farms_ndmi_timeseries_retrieve'],
			],
			'/api/v1/farms/{farm_id}/ndmi/raster.png' => [
				'get' => ['operationId' => 'v1_farms_ndmi_raster.png_retrieve'],
			],
			'/api/v1/farms/{farm_id}/ndmi/raster/queue' => [
				'post' => ['operationId' => 'v1_farms_ndmi_raster_queue_create'],
			],
			'/api/v1/farms/{farm_id}/ndmi/refresh/' => [
				'post' => ['operationId' => 'v1_farms_ndmi_refresh_create'],
			],
			'/api/v1/farms/{farm_id}/ndmi/farm-state/' => [
				'get' => ['operationId' => 'v1_farms_ndmi_farm_state_retrieve'],
			],
			'/api/v1/farms/{farm_id}/ndwi/farm-state/' => [
					'get' => ['operationId' => 'v1_farms_ndwi_farm_state_retrieve'],
				],
				'/api/v1/farm-state/{farm_id}/' => [
					'get' => ['operationId' => 'v1_farm_state_retrieve'],
				],
				'/api/v1/farms/{farm_id}/observations/' => [
					'get' => ['operationId' => 'v1_farms_observations_list'],
					'post' => ['operationId' => 'v1_farms_observations_create'],
				],
				'/api/v1/farms/{farm_id}/observations/{observation_id}/' => [
					'get' => ['operationId' => 'v1_farms_observations_retrieve'],
					'patch' => ['operationId' => 'v1_farms_observations_update'],
					'delete' => ['operationId' => 'v1_farms_observations_delete'],
				],
				'/api/v1/farms/{farm_id}/activities/' => [
					'get' => ['operationId' => 'v1_farms_activities_list'],
					'post' => ['operationId' => 'v1_farms_activities_create'],
				],
				'/api/v1/farms/{farm_id}/activities/{id}/' => [
					'get' => ['operationId' => 'v1_farms_activities_retrieve'],
					'put' => ['operationId' => 'v1_farms_activities_update'],
					'delete' => ['operationId' => 'v1_farms_activities_delete'],
				],
				'/api/v1/farms/{farm_id}/weather/current/' => [
					'get' => ['operationId' => 'v1_farms_weather_current_retrieve'],
				],
				'/api/v1/farms/{farm_id}/weather/hourly/' => [
					'get' => ['operationId' => 'v1_farms_weather_hourly_retrieve'],
				],
				'/api/v1/farms/{farm_id}/weather/daily/' => [
					'get' => ['operationId' => 'v1_farms_weather_daily_retrieve'],
				],
				'/api/v1/ndvi/jobs/{id}/' => [
					'get' => ['operationId' => 'v1_ndvi_jobs_retrieve'],
				],
				'/api/v1/ndvi' => [
					'post' => ['operationId' => 'v1_ndvi_create'],
				],
				'/api/v1/ndvi/circuit-breaker/reset/' => [
					'post' => ['operationId' => 'v1_ndvi_circuit_breaker_reset_create'],
				],
				'/api/v1/ndvi/health/upstream/' => [
					'get' => ['operationId' => 'v1_ndvi_health_upstream_retrieve'],
				],
			],
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	private function createSchemaWithCreateBody(): array {
		$schema = $this->createSchema();
		$schema['components']['schemas']['FarmWrite'] = [
			'type' => 'object',
			'properties' => [
				'name' => ['type' => 'string'],
			],
			'required' => ['name'],
		];
		$schema['paths']['/api/v1/farms/']['post'] = [
			'operationId' => 'v1_farms_create',
			'requestBody' => [
				'content' => [
					'application/json' => [
						'schema' => [
							'$ref' => '#/components/schemas/FarmWrite',
						],
					],
				],
			],
		];

		return $schema;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function createSchemaWithPaginatedList(): array {
		return [
			'openapi' => '3.0.0',
			'components' => [
				'schemas' => [],
			],
			'paths' => [
				'/api/v1/farms/' => [
					'get' => [
						'operationId' => 'v1_farms_list',
						'responses' => [
							'200' => [
								'content' => [
									'application/json' => [
										'schema' => [
											'type' => 'object',
											'properties' => [
												'results' => [
													'type' => 'object',
													'properties' => [
														'items' => [
															'type' => 'array',
															'items' => [
																'type' => 'object',
																'properties' => [
																	'id' => ['type' => 'integer'],
																	'name' => ['type' => 'string'],
																],
																'required' => ['id', 'name'],
															],
														],
													],
												],
											],
										],
									],
								],
							],
						],
					],
					'post' => ['operationId' => 'v1_farms_create'],
				],
				'/api/v1/farms/{id}/' => [
					'get' => ['operationId' => 'v1_farms_retrieve'],
					'put' => ['operationId' => 'v1_farms_update'],
					'patch' => ['operationId' => 'v1_farms_partial_update'],
					'delete' => ['operationId' => 'v1_farms_destroy'],
				],
				'/api/v1/farms/{farm_id}/ndvi/latest' => [
					'get' => ['operationId' => 'v1_farms_ndvi_latest_retrieve'],
				],
				'/api/v1/farms/{farm_id}/ndvi/timeseries' => [
					'get' => ['operationId' => 'v1_farms_ndvi_timeseries_retrieve'],
				],
				'/api/v1/farms/{farm_id}/ndvi/raster.png' => [
					'get' => ['operationId' => 'v1_farms_ndvi_raster.png_retrieve'],
				],
				'/api/v1/farms/{farm_id}/ndvi/raster/queue' => [
					'post' => ['operationId' => 'v1_farms_ndvi_raster_queue_create'],
				],
				'/api/v1/farms/{farm_id}/ndvi/refresh' => [
					'post' => ['operationId' => 'v1_farms_ndvi_refresh_create'],
				],
				'/api/v1/farms/{farm_id}/ndwi/latest/' => [
					'get' => ['operationId' => 'v1_farms_ndwi_latest_retrieve'],
				],
				'/api/v1/farms/{farm_id}/ndwi/timeseries/' => [
					'get' => ['operationId' => 'v1_farms_ndwi_timeseries_retrieve'],
				],
				'/api/v1/farms/{farm_id}/ndwi/raster.png' => [
					'get' => ['operationId' => 'v1_farms_ndwi_raster.png_retrieve'],
				],
				'/api/v1/farms/{farm_id}/ndwi/raster/queue' => [
					'post' => ['operationId' => 'v1_farms_ndwi_raster_queue_create'],
				],
				'/api/v1/farms/{farm_id}/ndwi/refresh/' => [
					'post' => ['operationId' => 'v1_farms_ndwi_refresh_create'],
				],
				'/api/v1/farms/{farm_id}/ndmi/latest/' => [
				'get' => ['operationId' => 'v1_farms_ndmi_latest_retrieve'],
			],
			'/api/v1/farms/{farm_id}/ndmi/timeseries/' => [
				'get' => ['operationId' => 'v1_farms_ndmi_timeseries_retrieve'],
			],
			'/api/v1/farms/{farm_id}/ndmi/raster.png' => [
				'get' => ['operationId' => 'v1_farms_ndmi_raster.png_retrieve'],
			],
			'/api/v1/farms/{farm_id}/ndmi/raster/queue' => [
				'post' => ['operationId' => 'v1_farms_ndmi_raster_queue_create'],
			],
			'/api/v1/farms/{farm_id}/ndmi/refresh/' => [
				'post' => ['operationId' => 'v1_farms_ndmi_refresh_create'],
			],
			'/api/v1/farms/{farm_id}/ndmi/farm-state/' => [
				'get' => ['operationId' => 'v1_farms_ndmi_farm_state_retrieve'],
			],
			'/api/v1/farms/{farm_id}/ndwi/farm-state/' => [
					'get' => ['operationId' => 'v1_farms_ndwi_farm_state_retrieve'],
				],
				'/api/v1/farm-state/{farm_id}/' => [
					'get' => ['operationId' => 'v1_farm_state_retrieve'],
				],
				'/api/v1/farms/{farm_id}/observations/' => [
					'get' => ['operationId' => 'v1_farms_observations_list'],
					'post' => ['operationId' => 'v1_farms_observations_create'],
				],
				'/api/v1/farms/{farm_id}/observations/{observation_id}/' => [
					'get' => ['operationId' => 'v1_farms_observations_retrieve'],
					'patch' => ['operationId' => 'v1_farms_observations_update'],
					'delete' => ['operationId' => 'v1_farms_observations_delete'],
				],
				'/api/v1/farms/{farm_id}/activities/' => [
					'get' => ['operationId' => 'v1_farms_activities_list'],
					'post' => ['operationId' => 'v1_farms_activities_create'],
				],
				'/api/v1/farms/{farm_id}/activities/{id}/' => [
					'get' => ['operationId' => 'v1_farms_activities_retrieve'],
					'put' => ['operationId' => 'v1_farms_activities_update'],
					'delete' => ['operationId' => 'v1_farms_activities_delete'],
				],
				'/api/v1/farms/{farm_id}/weather/current/' => [
					'get' => ['operationId' => 'v1_farms_weather_current_retrieve'],
				],
				'/api/v1/farms/{farm_id}/weather/hourly/' => [
					'get' => ['operationId' => 'v1_farms_weather_hourly_retrieve'],
				],
				'/api/v1/farms/{farm_id}/weather/daily/' => [
					'get' => ['operationId' => 'v1_farms_weather_daily_retrieve'],
				],
				'/api/v1/ndvi/jobs/{id}/' => [
					'get' => ['operationId' => 'v1_ndvi_jobs_retrieve'],
				],
				'/api/v1/ndvi' => [
					'post' => ['operationId' => 'v1_ndvi_create'],
				],
				'/api/v1/ndvi/circuit-breaker/reset/' => [
					'post' => ['operationId' => 'v1_ndvi_circuit_breaker_reset_create'],
				],
				'/api/v1/ndvi/health/upstream/' => [
					'get' => ['operationId' => 'v1_ndvi_health_upstream_retrieve'],
				],
			],
		];
	}
}
