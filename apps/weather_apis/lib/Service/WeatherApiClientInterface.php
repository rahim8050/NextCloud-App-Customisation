<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

interface WeatherApiClientInterface {
	/**
	 * @return array<array-key, mixed>
	 * @throws WeatherApiException
	 */
	public function whoami(string $correlationId): array;
}
