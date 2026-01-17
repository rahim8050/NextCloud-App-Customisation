<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

interface WeatherApiClientInterface {
	/**
	 * @return array<array-key, mixed>
	 * @throws WeatherApiException
	 */
	public function whoami(string $correlationId): array;

	/**
	 * @return array<array-key, mixed>
	 * @throws WeatherApiException
	 */
	public function nextcloudStatus(string $correlationId): array;

	/**
	 * @throws WeatherApiException
	 */
	public function nextcloudPreviewPng(string $correlationId): string;

	/**
	 * @throws WeatherApiException
	 */
	public function ping(string $correlationId): void;

	/**
	 * @throws WeatherApiException
	 */
	public function testConnection(string $correlationId): int;
}
