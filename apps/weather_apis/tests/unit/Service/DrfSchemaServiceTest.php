<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Service;

use OCA\WeatherApis\Service\DrfSchemaService;
use OCA\WeatherApis\Service\WeatherApiClientInterface;
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
	}

	private function createService(WeatherApiClientInterface $client): DrfSchemaService {
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
				],
			],
			'paths' => [
				'/api/v1/farms/' => [
					'get' => ['operationId' => 'v1_farms_list'],
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
}
