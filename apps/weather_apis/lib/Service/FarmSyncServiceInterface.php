<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

interface FarmSyncServiceInterface {
	/**
	 * @param array<string, mixed> $payload
	 * @param string|null $idempotencyKey
	 * @return array<array-key, mixed>
	 * @throws WeatherApiException
	 */
	public function sync(array $payload, string $correlationId, ?string $idempotencyKey = null): array;
}
