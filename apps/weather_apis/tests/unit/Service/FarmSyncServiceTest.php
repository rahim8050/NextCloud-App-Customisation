<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Service;

use OCA\WeatherApis\Service\FarmSyncService;
use OCA\WeatherApis\Service\WeatherApiClientInterface;
use OCA\WeatherApis\Service\WeatherApiException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class FarmSyncServiceTest extends TestCase {
	public function testSyncCallsClientWithNormalizedPayload(): void {
		$client = $this->createMock(WeatherApiClientInterface::class);
		$logger = $this->createMock(LoggerInterface::class);

		$payload = [
			'external_farm_id' => 'farm-uuid',
			'external_user_id' => 'nc-user',
			'name' => 'north-field',
			'bbox' => [
				'south' => '-1.234',
				'west' => 36.812,
				'north' => -1.220,
				'east' => '36.830',
			],
			'centroid' => [
				'lat' => -1.227,
				'lon' => '36.820',
			],
		];

		$expected = [
			'external_farm_id' => 'farm-uuid',
			'external_user_id' => 'nc-user',
			'name' => 'north-field',
			'bbox' => [
				'south' => -1.234,
				'west' => 36.812,
				'north' => -1.22,
				'east' => 36.83,
			],
			'centroid' => [
				'lat' => -1.227,
				'lon' => 36.82,
			],
		];

		$client
			->expects($this->once())
			->method('requestJsonWithStatus')
			->with('POST', '/api/v1/farms/sync', [], $expected, 'req-1', [])
			->willReturn(['payload' => ['ok' => true], 'statusCode' => 200]);

		$logger->expects($this->once())->method('info');

		$service = new FarmSyncService($client, $logger);
		$result = $service->sync($payload, 'req-1');

		self::assertSame(['ok' => true], $result);
	}

	public function testSyncIncludesIdempotencyHeader(): void {
		$client = $this->createMock(WeatherApiClientInterface::class);
		$logger = $this->createMock(LoggerInterface::class);

		$payload = [
			'external_farm_id' => 'farm-uuid',
			'external_user_id' => 'nc-user',
			'name' => 'north-field',
			'bbox' => [
				'south' => '-1.234',
				'west' => 36.812,
				'north' => -1.220,
				'east' => '36.830',
			],
		];

		$client
			->expects($this->once())
			->method('requestJsonWithStatus')
			->with(
				'POST',
				'/api/v1/farms/sync',
				[],
				[
					'external_farm_id' => 'farm-uuid',
					'external_user_id' => 'nc-user',
					'name' => 'north-field',
					'bbox' => [
						'south' => -1.234,
						'west' => 36.812,
						'north' => -1.22,
						'east' => 36.83,
					],
				],
				'req-2',
				['Idempotency-Key' => 'farm-sync:abc'],
			)
			->willReturn(['payload' => ['ok' => true], 'statusCode' => 200]);

		$logger->expects($this->once())->method('info');

		$service = new FarmSyncService($client, $logger);
		$result = $service->sync($payload, 'req-2', '  farm-sync:abc  ');

		self::assertSame(['ok' => true], $result);
	}

	public function testSyncThrowsOnMissingField(): void {
		$client = $this->createMock(WeatherApiClientInterface::class);
		$logger = $this->createMock(LoggerInterface::class);

		$service = new FarmSyncService($client, $logger);

		$this->expectException(WeatherApiException::class);
		$this->expectExceptionMessage('Missing or invalid external_farm_id.');

		$service->sync([
			'external_user_id' => 'nc-user',
			'name' => 'north-field',
			'bbox' => ['south' => -1.0, 'west' => 36.0, 'north' => -1.0, 'east' => 36.0],
			'centroid' => ['lat' => -1.0, 'lon' => 36.0],
		], 'req-2');
	}
}
