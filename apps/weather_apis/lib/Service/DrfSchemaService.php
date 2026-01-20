<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

use OCA\WeatherApis\AppInfo\Application;
use OCP\ICache;
use OCP\ICacheFactory;
use Psr\Log\LoggerInterface;

final class DrfSchemaService {
	private const CACHE_KEY = 'drf_openapi_schema_json';
	private const CACHE_TTL_SECONDS = 3600;

	private const FARM_OPERATION_IDS = [
		'list' => 'v1_farms_list',
		'create' => 'v1_farms_create',
		'retrieve' => 'v1_farms_retrieve',
		'update' => 'v1_farms_update',
		'partial_update' => 'v1_farms_partial_update',
		'destroy' => 'v1_farms_destroy',
		'ndvi_latest' => 'v1_farms_ndvi_latest_retrieve',
		'ndvi_timeseries' => 'v1_farms_ndvi_timeseries_retrieve',
		'ndvi_raster' => 'v1_farms_ndvi_raster.png_retrieve',
		'ndvi_raster_queue' => 'v1_farms_ndvi_raster_queue_create',
		'ndvi_refresh' => 'v1_farms_ndvi_refresh_create',
	];

	private ICache $cache;

	public function __construct(
		private readonly WeatherApiClientInterface $weatherApiClient,
		ICacheFactory $cacheFactory,
		private readonly LoggerInterface $logger,
	) {
		$this->cache = $cacheFactory->createDistributed(Application::APP_ID);
	}

	/**
	 * @return array{schema: array<string, mixed>, warning: string|null}
	 * @throws WeatherApiException
	 */
	public function getFarmSchemaSummary(string $correlationId): array {
		[$schema, $warning] = $this->loadSchema($correlationId);
		$schema = $this->normalizeSchema($schema);

		return [
			'schema' => $this->buildFarmSummary($schema),
			'warning' => $warning,
		];
	}

	/**
	 * @return array<string, mixed>
	 * @throws WeatherApiException
	 */
	public function getFarmOperation(string $operationKey, string $correlationId): array {
		$result = $this->getFarmSchemaSummary($correlationId);
		$operations = $result['schema']['operations'] ?? [];

		if (!is_array($operations) || !isset($operations[$operationKey]) || !is_array($operations[$operationKey])) {
			throw new WeatherApiException('backend_error', 'Schema is missing farm operation: ' . $operationKey);
		}

		return $operations[$operationKey];
	}

	/**
	 * @return array{0: array<string, mixed>, 1: string|null}
	 * @throws WeatherApiException
	 */
	private function loadSchema(string $correlationId): array {
		$warning = null;

		try {
			$schema = $this->weatherApiClient->fetchSchema($correlationId);
			$this->cacheSchema($schema);
		} catch (WeatherApiException $exception) {
			$schema = $this->loadCachedSchema();
			if ($schema === null) {
				throw $exception;
			}

			$warning = 'Schema fetch failed; using cached schema.';
			$this->logger->warning(
				'Weather API schema fetch failed; using cached schema.',
				LogSanitizer::sanitizeContext([
					'code' => $exception->getErrorCode(),
					'reason' => $exception->getReason() ?? '',
					'requestId' => $correlationId,
				]),
			);
		}

		return [$schema, $warning];
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string, mixed>
	 * @throws WeatherApiException
	 */
	private function buildFarmSummary(array $schema): array {
		$farmFields = $this->extractFarmFields($schema);
		$operations = [];

		foreach (self::FARM_OPERATION_IDS as $key => $operationId) {
			$operations[$key] = $this->extractOperation($schema, $operationId);
		}

		return [
			'fields' => $farmFields,
			'operations' => $operations,
		];
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string, array<string, mixed>>
	 * @throws WeatherApiException
	 */
	private function extractFarmFields(array $schema): array {
		$fields = [];
		try {
			$farm = $this->resolveSchema($schema, $this->getComponent($schema, 'Farm'));

			if (($farm['type'] ?? null) === 'object' && isset($farm['properties']) && is_array($farm['properties'])) {
				$required = [];
				if (isset($farm['required']) && is_array($farm['required'])) {
					$required = array_map('strval', $farm['required']);
				}

				$fields = $this->extractFieldsFromSchema($schema, $farm, $required);
			}
		} catch (WeatherApiException) {
			$fields = [];
		}

		if ($fields === []) {
			$fields = $this->extractFarmFieldsFromCreateOperation($schema);
		}

		if ($fields === []) {
			throw new WeatherApiException('backend_error', 'Farm schema is missing properties.');
		}

		return $fields;
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string, mixed>
	 */
	private function extractOperation(array $schema, string $operationId): array {
		$operation = $this->findOperation($schema, $operationId);

		$meta = [
			'method' => $operation['method'],
			'path' => $operation['path'],
			'queryParams' => $this->extractQueryParams($schema, $operation['spec']),
			'bodyFields' => $this->extractBodyFields($schema, $operation['spec']),
		];

		return $meta;
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array{path: string, method: string, spec: array<string, mixed>}
	 * @throws WeatherApiException
	 */
	private function findOperation(array $schema, string $operationId): array {
		$paths = $schema['paths'] ?? null;
		if (!is_array($paths)) {
			throw new WeatherApiException('backend_error', 'Schema is missing paths.');
		}

		foreach ($paths as $path => $methods) {
			if (!is_array($methods)) {
				continue;
			}
			foreach ($methods as $method => $spec) {
				if (!is_array($spec)) {
					continue;
				}
				if (($spec['operationId'] ?? '') === $operationId) {
					return [
						'path' => (string)$path,
						'method' => strtoupper((string)$method),
						'spec' => $spec,
					];
				}
			}
		}

		throw new WeatherApiException('backend_error', 'Schema is missing operationId: ' . $operationId);
	}

	/**
	 * @param array<string, mixed> $schema
	 * @param array<string, mixed> $spec
	 * @return list<array{name: string, type: string, format: string|null, required: bool}>
	 */
	private function extractQueryParams(array $schema, array $spec): array {
		$params = $spec['parameters'] ?? null;
		if (!is_array($params)) {
			return [];
		}

		$out = [];
		foreach ($params as $param) {
			if (!is_array($param)) {
				continue;
			}
			$param = $this->resolveParameter($schema, $param);
			if (($param['in'] ?? '') !== 'query') {
				continue;
			}
			$name = $param['name'] ?? null;
			$schemaDef = $param['schema'] ?? null;
			if (!is_string($name) || !is_array($schemaDef)) {
				continue;
			}
			$schemaDef = $this->resolveSchema($schema, $schemaDef);
			$out[] = [
				'name' => $name,
				'type' => (string)($schemaDef['type'] ?? 'string'),
				'format' => isset($schemaDef['format']) ? (string)$schemaDef['format'] : null,
				'required' => (bool)($param['required'] ?? false),
			];
		}

		return $out;
	}

	/**
	 * @param array<string, mixed> $schema
	 * @param array<string, mixed> $spec
	 * @return array<string, array<string, mixed>>
	 */
	private function extractBodyFields(array $schema, array $spec): array {
		$requestBody = $spec['requestBody'] ?? null;
		if (!is_array($requestBody)) {
			return [];
		}

		$content = $requestBody['content'] ?? null;
		if (!is_array($content) || $content === []) {
			return [];
		}

		$jsonContent = $content['application/json'] ?? reset($content);
		if (!is_array($jsonContent)) {
			return [];
		}

		$schemaDef = $jsonContent['schema'] ?? null;
		if (!is_array($schemaDef)) {
			return [];
		}

		$schemaDef = $this->resolveSchema($schema, $schemaDef);
		if (($schemaDef['type'] ?? null) !== 'object') {
			return [];
		}

		$required = [];
		if (isset($schemaDef['required']) && is_array($schemaDef['required'])) {
			$required = array_map('strval', $schemaDef['required']);
		}

		return $this->extractFieldsFromSchema($schema, $schemaDef, $required);
	}

	/**
	 * @param array<string, mixed> $schema
	 * @param array<string, mixed> $schemaDef
	 * @param list<string> $required
	 * @return array<string, array<string, mixed>>
	 */
	private function extractFieldsFromSchema(array $schema, array $schemaDef, array $required): array {
		$properties = $schemaDef['properties'] ?? [];
		if (!is_array($properties)) {
			return [];
		}

		$fields = [];
		foreach ($properties as $name => $property) {
			if (!is_array($property)) {
				continue;
			}
			$property = $this->resolveSchema($schema, $property);
			$fields[(string)$name] = [
				'type' => (string)($property['type'] ?? 'string'),
				'format' => isset($property['format']) ? (string)$property['format'] : null,
				'required' => in_array((string)$name, $required, true),
				'readOnly' => (bool)($property['readOnly'] ?? false),
			];
		}

		return $fields;
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string, array<string, mixed>>
	 */
	private function extractFarmFieldsFromCreateOperation(array $schema): array {
		try {
			$operationId = self::FARM_OPERATION_IDS['create'];
			$operation = $this->findOperation($schema, $operationId);
		} catch (WeatherApiException) {
			return [];
		}

		return $this->extractBodyFields($schema, $operation['spec']);
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string, mixed>
	 */
	private function normalizeSchema(array $schema): array {
		if (isset($schema['paths']) || isset($schema['components'])) {
			return $schema;
		}

		$candidate = $schema['schema'] ?? null;
		if (!is_array($candidate)) {
			return $schema;
		}

		if (isset($candidate['paths']) || isset($candidate['components']) || isset($candidate['openapi'])) {
			return $candidate;
		}

		return $schema;
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string, mixed>
	 * @throws WeatherApiException
	 */
	private function getComponent(array $schema, string $name): array {
		$components = $schema['components']['schemas'] ?? null;
		if (!is_array($components) || !isset($components[$name]) || !is_array($components[$name])) {
			throw new WeatherApiException('backend_error', 'Schema component missing: ' . $name);
		}

		return $components[$name];
	}

	/**
	 * @param array<string, mixed> $schema
	 * @param array<string, mixed> $schemaDef
	 * @return array<string, mixed>
	 */
	private function resolveSchema(array $schema, array $schemaDef): array {
		if (isset($schemaDef['$ref']) && is_string($schemaDef['$ref'])) {
			return $this->resolveRef($schema, $schemaDef['$ref']);
		}

		$allOf = $schemaDef['allOf'] ?? null;
		if (!is_array($allOf)) {
			return $schemaDef;
		}

		$merged = ['type' => 'object', 'properties' => [], 'required' => []];
		foreach ($allOf as $part) {
			if (!is_array($part)) {
				continue;
			}
			$part = $this->resolveSchema($schema, $part);
			if (($part['type'] ?? null) === 'object') {
				if (isset($part['properties']) && is_array($part['properties'])) {
					$merged['properties'] = array_merge($merged['properties'], $part['properties']);
				}
				if (isset($part['required']) && is_array($part['required'])) {
					$merged['required'] = array_values(array_unique(array_merge($merged['required'], $part['required'])));
				}
			} else {
				$merged = array_merge($merged, $part);
			}
		}

		return $merged;
	}

	/**
	 * @param array<string, mixed> $schema
	 * @param array<string, mixed> $param
	 * @return array<string, mixed>
	 */
	private function resolveParameter(array $schema, array $param): array {
		if (!isset($param['$ref']) || !is_string($param['$ref'])) {
			return $param;
		}

		$ref = $param['$ref'];
		if (!str_starts_with($ref, '#/components/parameters/')) {
			return $param;
		}

		$name = substr($ref, strlen('#/components/parameters/'));
		$parameters = $schema['components']['parameters'] ?? null;
		if (!is_array($parameters) || !isset($parameters[$name]) || !is_array($parameters[$name])) {
			return $param;
		}

		return $parameters[$name];
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string, mixed>
	 */
	private function resolveRef(array $schema, string $ref): array {
		if (!str_starts_with($ref, '#/components/schemas/')) {
			return [];
		}

		$name = substr($ref, strlen('#/components/schemas/'));
		$components = $schema['components']['schemas'] ?? null;
		if (!is_array($components) || !isset($components[$name]) || !is_array($components[$name])) {
			return [];
		}

		return $components[$name];
	}

	/**
	 * @param array<string, mixed> $schema
	 */
	private function cacheSchema(array $schema): void {
		try {
			$encoded = json_encode($schema, JSON_THROW_ON_ERROR);
		} catch (\JsonException $exception) {
			$this->logger->warning(
				'Weather API schema encoding failed',
				LogSanitizer::sanitizeContext([
					'error' => $exception->getMessage(),
				]),
			);
			return;
		}

		$this->cache->set(self::CACHE_KEY, $encoded, self::CACHE_TTL_SECONDS);
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function loadCachedSchema(): ?array {
		$cached = $this->cache->get(self::CACHE_KEY);
		if (!is_string($cached) || $cached === '') {
			return null;
		}

		try {
			$decoded = json_decode($cached, true, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException) {
			return null;
		}

		return is_array($decoded) ? $decoded : null;
	}
}
