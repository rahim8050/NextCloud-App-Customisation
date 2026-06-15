<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Service;

interface WeatherApiClientInterface {
	/**
	 * @param array<string, mixed> $queryParams
	 * @param array<string, mixed>|null $body
	 * @return array<array-key, mixed>
	 * @throws WeatherApiException
	 */
	public function requestJson(
		string $method,
		string $path,
		array $queryParams = [],
		?array $body = null,
		?string $correlationId = null,
	): array;

	/**
	 * @param array<string, mixed> $queryParams
	 * @param array<string, mixed>|null $body
	 * @param array<string, string> $headers
	 * @return array{payload: array<array-key, mixed>, statusCode: int}
	 * @throws WeatherApiException
	 */
	public function requestJsonWithStatus(
		string $method,
		string $path,
		array $queryParams = [],
		?array $body = null,
		?string $correlationId = null,
		array $headers = [],
	): array;

	/**
	 * @param array<string, mixed> $queryParams
	 * @return array{body: string, contentType: string, statusCode: int}
	 * @throws WeatherApiException
	 */
	public function requestBinary(
		string $method,
		string $path,
		array $queryParams = [],
		?string $correlationId = null,
	): array;

	/**
	 * @return array<array-key, mixed>
	 * @throws WeatherApiException
	 */
	public function fetchSchema(string $correlationId): array;

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
