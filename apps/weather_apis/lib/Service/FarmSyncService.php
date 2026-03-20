<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

use Psr\Log\LoggerInterface;

final class FarmSyncService implements FarmSyncServiceInterface {
	private const FARM_SYNC_PATH = '/api/v1/farms/sync';
	private const IDEMPOTENCY_HEADER = 'Idempotency-Key';
	private const IDEMPOTENCY_KEY_MAX_LENGTH = 191;

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
	public function sync(array $payload, string $correlationId, ?string $idempotencyKey = null): array {
		$normalized = $this->normalizePayload($payload);
		$headers = $this->buildIdempotencyHeaders($idempotencyKey);

		try {
			$response = $this->client->requestJsonWithStatus(
				'POST',
				self::FARM_SYNC_PATH,
				[],
				$normalized,
				$correlationId,
				$headers,
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

		$logContext = [
			'correlation_id' => $correlationId,
			'status_code' => $response['statusCode'],
			'external_farm_id' => $normalized['external_farm_id'],
			'external_user_id' => $normalized['external_user_id'],
			'path' => self::FARM_SYNC_PATH,
		];
		if (isset($headers[self::IDEMPOTENCY_HEADER])) {
			$logContext['idempotency_key'] = $headers[self::IDEMPOTENCY_HEADER];
		}
		$this->logger->info('Farm sync request sent.', LogSanitizer::sanitizeContext($logContext));

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
		
		// Centroid is optional - only validate if provided
		$centroid = $payload['centroid'] ?? null;
		$centroidNormalized = null;
		if (is_array($centroid)) {
			$centroidNormalized = [
				'lat' => $this->requireFloat($centroid, 'lat'),
				'lon' => $this->requireFloat($centroid, 'lon'),
			];
		}

		$normalized = [
			'external_farm_id' => $externalFarmId,
			'external_user_id' => $externalUserId,
			'name' => $name,
			'bbox' => [
				'south' => $this->requireFloat($bbox, 'south'),
				'west' => $this->requireFloat($bbox, 'west'),
				'north' => $this->requireFloat($bbox, 'north'),
				'east' => $this->requireFloat($bbox, 'east'),
			],
		];
		
		// Only include centroid if it was provided and valid
		if ($centroidNormalized !== null) {
			$normalized['centroid'] = $centroidNormalized;
		}

		return $normalized;
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

	private function buildIdempotencyHeaders(?string $key): array {
		$trimmed = $key === null ? '' : trim($key);
		if ($trimmed === '') {
			return [];
		}

		return [
			self::IDEMPOTENCY_HEADER => $this->clampString($trimmed, self::IDEMPOTENCY_KEY_MAX_LENGTH),
		];
	}

	private function clampString(string $value, int $maxLength): string {
		if ($maxLength < 0) {
			return $value;
		}

		if (mb_strlen($value) <= $maxLength) {
			return $value;
		}

		return mb_substr($value, 0, $maxLength);
	}
}
