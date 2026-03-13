<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

use Psr\Log\LoggerInterface;

final class FarmSyncService implements FarmSyncServiceInterface {
	private const FARM_SYNC_PATH = '/api/v1/farms/sync';

	public function __construct(
		private readonly WeatherApiClientInterface $client,
		private readonly LoggerInterface $logger,
	) {
	}

	/**
	 * @param array<string, mixed> $payload
	 * @return array<array-key, mixed>
	 * @throws WeatherApiException
	 */
	public function sync(array $payload, string $correlationId): array {
		$normalized = $this->normalizePayload($payload);

		try {
			$response = $this->client->requestJsonWithStatus(
				'POST',
				self::FARM_SYNC_PATH,
				[],
				$normalized,
				$correlationId,
			);
		} catch (WeatherApiException $exception) {
			$this->logger->warning(
				'Farm sync request failed.',
				LogSanitizer::sanitizeContext([
					'correlation_id' => $correlationId,
					'error_code' => $exception->getErrorCode(),
					'external_farm_id' => $normalized['external_farm_id'],
					'external_user_id' => $normalized['external_user_id'],
				]),
			);

			throw $exception;
		}

		$this->logger->info(
			'Farm sync request sent.',
			LogSanitizer::sanitizeContext([
				'correlation_id' => $correlationId,
				'status_code' => $response['statusCode'],
				'external_farm_id' => $normalized['external_farm_id'],
				'external_user_id' => $normalized['external_user_id'],
				'path' => self::FARM_SYNC_PATH,
			]),
		);

		return $response['payload'];
	}

	/**
	 * @param array<string, mixed> $payload
	 * @return array<string, mixed>
	 */
	private function normalizePayload(array $payload): array {
		$externalFarmId = $this->requireString($payload, 'external_farm_id');
		$externalUserId = $this->requireString($payload, 'external_user_id');
		$name = $this->requireString($payload, 'name');

		$bbox = $this->requireArray($payload, 'bbox');
		$centroid = $this->requireArray($payload, 'centroid');

		return [
			'external_farm_id' => $externalFarmId,
			'external_user_id' => $externalUserId,
			'name' => $name,
			'bbox' => [
				'south' => $this->requireFloat($bbox, 'south'),
				'west' => $this->requireFloat($bbox, 'west'),
				'north' => $this->requireFloat($bbox, 'north'),
				'east' => $this->requireFloat($bbox, 'east'),
			],
			'centroid' => [
				'lat' => $this->requireFloat($centroid, 'lat'),
				'lon' => $this->requireFloat($centroid, 'lon'),
			],
		];
	}

	/**
	 * @param array<string, mixed> $payload
	 */
	private function requireString(array $payload, string $key): string {
		$value = $payload[$key] ?? null;
		if (!is_string($value)) {
			throw new WeatherApiException('invalid_argument', 'Missing or invalid ' . $key . '.');
		}

		$value = trim($value);
		if ($value === '') {
			throw new WeatherApiException('invalid_argument', 'Missing or invalid ' . $key . '.');
		}

		return $value;
	}

	/**
	 * @param array<string, mixed> $payload
	 * @return array<string, mixed>
	 */
	private function requireArray(array $payload, string $key): array {
		$value = $payload[$key] ?? null;
		if (!is_array($value)) {
			throw new WeatherApiException('invalid_argument', 'Missing or invalid ' . $key . '.');
		}

		return $value;
	}

	/**
	 * @param array<string, mixed> $payload
	 */
	private function requireFloat(array $payload, string $key): float {
		$value = $payload[$key] ?? null;
		if (is_int($value) || is_float($value)) {
			return (float)$value;
		}

		if (is_string($value) && is_numeric($value)) {
			return (float)$value;
		}

		throw new WeatherApiException('invalid_argument', 'Missing or invalid ' . $key . '.');
	}
}
