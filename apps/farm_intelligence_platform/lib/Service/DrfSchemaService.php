<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Service;

use OCA\FarmIntelligencePlatform\AppInfo\Application;
use OCP\ICache;
use OCP\ICacheFactory;
use Psr\Log\LoggerInterface;

class DrfSchemaService {
	private const CACHE_KEY = 'drf_openapi_schema_json_v2';
	private const CACHE_TTL_SECONDS = 3600;

	private const FARM_OPERATION_IDS = [
		'list' => 'v1_farms_list',
		'create' => 'v1_farms_create',
		'retrieve' => 'v1_farms_retrieve',
		'update' => 'v1_farms_update',
		'partial_update' => 'v1_farms_partial_update',
		'destroy' => 'v1_farms_destroy',
		'observations_list' => 'v1_farms_observations_list',
		'observations_create' => 'v1_farms_observations_create',
		'observations_retrieve' => 'v1_farms_observations_retrieve',
		'observations_update' => 'v1_farms_observations_update',
		'observations_delete' => 'v1_farms_observations_delete',
		'activities_list' => 'v1_farms_activities_list',
		'activities_create' => 'v1_farms_activities_create',
		'activities_retrieve' => 'v1_farms_activities_retrieve',
		'activities_update' => 'v1_farms_activities_update',
		'activities_delete' => 'v1_farms_activities_delete',
		'ndvi_latest' => 'v1_farms_ndvi_latest_retrieve',
		'ndvi_timeseries' => 'v1_farms_ndvi_timeseries_retrieve',
		'ndvi_raster' => 'v1_farms_ndvi_raster.png_retrieve',
		'ndvi_raster_queue' => 'v1_farms_ndvi_raster_queue_create',
		'ndvi_refresh' => 'v1_farms_ndvi_refresh_create',
		'farm_state' => 'v1_farm_state_retrieve',
		'ndwi_latest' => 'v1_farms_ndwi_latest_retrieve',
		'ndwi_timeseries' => 'v1_farms_ndwi_timeseries_retrieve',
		'ndwi_raster' => 'v1_farms_ndwi_raster.png_retrieve',
		'ndwi_raster_queue' => 'v1_farms_ndwi_raster_queue_create',
		'ndwi_refresh' => 'v1_farms_ndwi_refresh_create',
		'ndwi_farm_state' => 'v1_farms_ndwi_farm_state_retrieve',
		'ndmi_latest' => 'v1_farms_ndmi_latest_retrieve',
		'ndmi_timeseries' => 'v1_farms_ndmi_timeseries_retrieve',
		'ndmi_raster' => 'v1_farms_ndmi_raster.png_retrieve',
		'ndmi_raster_queue' => 'v1_farms_ndmi_raster_queue_create',
		'ndmi_refresh' => 'v1_farms_ndmi_refresh_create',
		'ndmi_farm_state' => 'v1_farms_ndmi_farm_state_retrieve',
		'weather_current' => 'v1_farms_weather_current_retrieve',
		'weather_hourly' => 'v1_farms_weather_hourly_retrieve',
		'weather_daily' => 'v1_farms_weather_daily_retrieve',
		'ndvi_job_status' => 'v1_ndvi_jobs_retrieve',
		'ndvi_ingest' => 'v1_ndvi_create',
		'ndvi_circuit_breaker_reset' => 'v1_ndvi_circuit_breaker_reset_create',
		'ndvi_upstream_health' => 'v1_ndvi_health_upstream_retrieve',
		'raster_tiles' => 'v1_farms_tiles_.png_retrieve',
		'raster_dates' => 'v1_farms_raster_dates_retrieve',
	];

	private const ACTIVITY_OPERATION_IDS = [
		'list' => 'v1_activities_list',
		'create' => 'v1_activities_create',
		'retrieve' => 'v1_activities_retrieve',
		'update' => 'v1_activities_update',
		'partial_update' => 'v1_activities_partial_update',
		'destroy' => 'v1_activities_destroy',
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
	 * @return array{schema: array<string, mixed>, warning: string|null}
	 * @throws WeatherApiException
	 */
	public function getActivitySchemaSummary(string $correlationId): array {
		[$schema, $warning] = $this->loadSchema($correlationId);
		$schema = $this->normalizeSchema($schema);

		return [
			'schema' => $this->buildActivitySummary($schema),
			'warning' => $warning,
		];
	}

	/**
	 * @return array<string, mixed>
	 * @throws WeatherApiException
	 */
	public function getActivityOperation(string $operationKey, string $correlationId): array {
		$result = $this->getActivitySchemaSummary($correlationId);
		$operations = $result['schema']['operations'] ?? [];

		if (!is_array($operations) || !isset($operations[$operationKey]) || !is_array($operations[$operationKey])) {
			throw new WeatherApiException('backend_error', 'Schema is missing activity operation: ' . $operationKey);
		}

		return $operations[$operationKey];
	}

	/**
	 * Generic operation lookup by DRF operationId.
	 *
	 * Loads the schema and extracts the path, method, queryParams, and bodyFields
	 * for the given DRF operationId (e.g. "v1_radio_providers_list").
	 *
	 * @return array{operationId: string, method: string, path: string, queryParams: list<array{name: string, type: string, format: string|null, required: bool}>, bodyFields: array<string, array{type: string, format: string|null, required: bool, readOnly: bool, enum?: list<mixed>}>}
	 * @throws WeatherApiException
	 */
	public function getOperation(string $operationId, string $correlationId): array {
		[$schema, $warning] = $this->loadSchema($correlationId);
		$schema = $this->normalizeSchema($schema);

		return $this->extractOperation($schema, $operationId);
	}

	/**
	 * @return array{0: array<string, mixed>, 1: string|null}
	 * @throws WeatherApiException
	 */
	private function loadSchema(string $correlationId): array {
		$warning = null;
		$schema = $this->loadCachedSchema();
		if ($schema !== null) {
			$this->logger->debug(
				'Weather API schema cache hit.',
				LogSanitizer::sanitizeContext([
					'schema_cache' => 'hit',
					'requestId' => $correlationId,
				]),
			);
			return [$schema, $warning];
		}

		$this->logger->debug(
			'Weather API schema cache miss.',
			LogSanitizer::sanitizeContext([
				'schema_cache' => 'miss',
				'requestId' => $correlationId,
			]),
		);

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
		$createFields = $this->extractFarmWriteFields($schema, self::FARM_OPERATION_IDS['create']);
		$updateFields = $this->extractFarmWriteFields($schema, self::FARM_OPERATION_IDS['partial_update']);
		if ($updateFields === []) {
			$updateFields = $this->extractFarmWriteFields($schema, self::FARM_OPERATION_IDS['update']);
		}
		if ($updateFields === []) {
			$updateFields = $createFields;
		}
		$columns = $this->extractFarmColumns($schema);
		if ($columns === []) {
			$columns = array_keys($farmFields);
		}
		$operations = [];

		foreach (self::FARM_OPERATION_IDS as $key => $operationId) {
			$operations[$key] = $this->extractOperation($schema, $operationId);
		}

		return [
			'fields' => $farmFields,
			'fieldsCreate' => $createFields,
			'fieldsUpdate' => $updateFields,
			'columns' => array_values($columns),
			'operations' => $operations,
		];
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string, mixed>
	 * @throws WeatherApiException
	 */
	private function buildActivitySummary(array $schema): array {
		$activityFields = $this->extractActivityFields($schema);
		$createFields = $this->extractActivityWriteFields($schema, self::ACTIVITY_OPERATION_IDS['create']);
		$updateFields = $this->extractActivityWriteFields($schema, self::ACTIVITY_OPERATION_IDS['partial_update']);
		$columns = $this->extractActivityColumns($schema);
		if ($columns === []) {
			$columns = array_keys($activityFields);
		}
		$operations = [];

		foreach (self::ACTIVITY_OPERATION_IDS as $key => $operationId) {
			$operations[$key] = $this->extractOperation($schema, $operationId);
		}

		return [
			'fields' => $activityFields,
			'fieldsCreate' => $createFields,
			'fieldsUpdate' => $updateFields,
			'columns' => array_values($columns),
			'operations' => $operations,
		];
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string, array<string, mixed>>
	 * @throws WeatherApiException
	 */
	private function extractActivityFields(array $schema): array {
		$fields = [];
		try {
			$activity = $this->resolveSchema($schema, $this->getComponent($schema, 'Activity'));

			if (($activity['type'] ?? null) === 'object' && isset($activity['properties']) && is_array($activity['properties'])) {
				$required = [];
				if (isset($activity['required']) && is_array($activity['required'])) {
					$required = array_map('strval', $activity['required']);
				}

				$fields = $this->extractFieldsFromSchema($schema, $activity, $required);
			}
		} catch (WeatherApiException) {
			$fields = [];
		}

		if ($fields === []) {
			$fields = $this->extractActivityFieldsFromListResponse($schema);
		}

		if ($fields === []) {
			$fields = $this->extractActivityFieldsFromCreateOperation($schema);
		}

		if ($fields === []) {
			$fields = $this->extractActivityFieldsFromOperations($schema);
		}

		return $fields;
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string, array<string, mixed>>
	 */
	private function extractActivityWriteFields(array $schema, string $operationId): array {
		try {
			$operation = $this->findOperation($schema, $operationId);
		} catch (WeatherApiException) {
			return [];
		}

		$fields = $this->extractBodyFields($schema, $operation['spec']);
		if ($fields === []) {
			return [];
		}

		return $this->filterWritableFields($fields);
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string>
	 */
	private function extractActivityColumns(array $schema): array {
		try {
			$operationId = self::ACTIVITY_OPERATION_IDS['list'];
			$operation = $this->findOperation($schema, $operationId);
		} catch (WeatherApiException) {
			return [];
		}

		$itemSchema = $this->extractListItemSchema($schema, $operation['spec']);
		if ($itemSchema === null) {
			return [];
		}

		$properties = $itemSchema['properties'] ?? null;
		if (!is_array($properties)) {
			return [];
		}

		return array_map('strval', array_keys($properties));
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string, array<string, mixed>>
	 */
	private function extractActivityFieldsFromListResponse(array $schema): array {
		try {
			$operationId = self::ACTIVITY_OPERATION_IDS['list'];
			$operation = $this->findOperation($schema, $operationId);
		} catch (WeatherApiException) {
			return [];
		}

		$itemSchema = $this->extractListItemSchema($schema, $operation['spec']);
		if ($itemSchema === null) {
			return [];
		}

		if (($itemSchema['type'] ?? null) !== 'object' || !isset($itemSchema['properties']) || !is_array($itemSchema['properties'])) {
			return [];
		}

		$required = [];
		if (isset($itemSchema['required']) && is_array($itemSchema['required'])) {
			$required = array_map('strval', $itemSchema['required']);
		}

		return $this->extractFieldsFromSchema($schema, $itemSchema, $required);
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string, array<string, mixed>>
	 */
	private function extractActivityFieldsFromCreateOperation(array $schema): array {
		try {
			$operationId = self::ACTIVITY_OPERATION_IDS['create'];
			$operation = $this->findOperation($schema, $operationId);
		} catch (WeatherApiException) {
			return [];
		}

		return $this->extractBodyFields($schema, $operation['spec']);
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string, array<string, mixed>>
	 */
	private function extractActivityFieldsFromOperations(array $schema): array {
		$allFields = [];

		foreach (self::ACTIVITY_OPERATION_IDS as $operationId) {
			try {
				$operation = $this->findOperation($schema, $operationId);
			} catch (WeatherApiException) {
				continue;
			}

			$bodyFields = $this->extractBodyFields($schema, $operation['spec']);
			foreach ($bodyFields as $name => $field) {
				if (!isset($allFields[$name])) {
					$allFields[$name] = $field;
				}
			}
		}

		return $allFields;
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
			$fields = $this->extractFarmFieldsFromListResponse($schema);
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
			'operationId' => $operationId,
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
			$enum = $property['enum'] ?? null;
			$enumValues = is_array($enum) ? array_values($enum) : null;
			$fields[(string)$name] = [
				'type' => (string)($property['type'] ?? 'string'),
				'format' => isset($property['format']) ? (string)$property['format'] : null,
				'required' => in_array((string)$name, $required, true),
				'readOnly' => (bool)($property['readOnly'] ?? false),
				'enum' => $enumValues,
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
	 * @return array<string, array<string, mixed>>
	 */
	private function extractFarmFieldsFromListResponse(array $schema): array {
		try {
			$operationId = self::FARM_OPERATION_IDS['list'];
			$operation = $this->findOperation($schema, $operationId);
		} catch (WeatherApiException) {
			return [];
		}

		$itemSchema = $this->extractListItemSchema($schema, $operation['spec']);
		if ($itemSchema === null) {
			return [];
		}

		if (($itemSchema['type'] ?? null) !== 'object' || !isset($itemSchema['properties']) || !is_array($itemSchema['properties'])) {
			return [];
		}

		$required = [];
		if (isset($itemSchema['required']) && is_array($itemSchema['required'])) {
			$required = array_map('strval', $itemSchema['required']);
		}

		return $this->extractFieldsFromSchema($schema, $itemSchema, $required);
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return list<string>
	 */
	private function extractFarmColumns(array $schema): array {
		try {
			$operationId = self::FARM_OPERATION_IDS['list'];
			$operation = $this->findOperation($schema, $operationId);
		} catch (WeatherApiException) {
			return [];
		}

		$itemSchema = $this->extractListItemSchema($schema, $operation['spec']);
		if ($itemSchema === null) {
			return [];
		}

		$properties = $itemSchema['properties'] ?? null;
		if (!is_array($properties)) {
			return [];
		}

		return array_map('strval', array_keys($properties));
	}

	/**
	 * @param array<string, mixed> $schema
	 * @return array<string, array<string, mixed>>
	 */
	private function extractFarmWriteFields(array $schema, string $operationId): array {
		try {
			$operation = $this->findOperation($schema, $operationId);
		} catch (WeatherApiException) {
			return [];
		}

		$fields = $this->extractBodyFields($schema, $operation['spec']);
		if ($fields === []) {
			return [];
		}

		return $this->filterWritableFields($fields);
	}

	/**
	 * @param array<string, array<string, mixed>> $fields
	 * @return array<string, array<string, mixed>>
	 */
	private function filterWritableFields(array $fields): array {
		$filtered = [];
		foreach ($fields as $name => $definition) {
			if (!is_string($name)) {
				continue;
			}
			if (($definition['readOnly'] ?? false) === true) {
				continue;
			}
			$filtered[$name] = $definition;
		}

		return $filtered;
	}

	/**
	 * @param array<string, mixed> $schema
	 * @param array<string, mixed> $spec
	 * @return array<string, mixed>|null
	 */
	private function extractListItemSchema(array $schema, array $spec): ?array {
		$responseSchema = $this->extractSuccessResponseSchema($schema, $spec);
		if ($responseSchema === null) {
			return null;
		}

		if (($responseSchema['type'] ?? null) === 'array' && isset($responseSchema['items']) && is_array($responseSchema['items'])) {
			return $this->resolveSchema($schema, $responseSchema['items']);
		}

		if (($responseSchema['type'] ?? null) !== 'object' || !isset($responseSchema['properties']) || !is_array($responseSchema['properties'])) {
			return null;
		}

		$properties = $responseSchema['properties'];
		$results = $properties['results'] ?? null;
		if (!is_array($results)) {
			return null;
		}

		$resultsSchema = $this->resolveSchema($schema, $results);
		if (($resultsSchema['type'] ?? null) === 'array' && isset($resultsSchema['items']) && is_array($resultsSchema['items'])) {
			return $this->resolveSchema($schema, $resultsSchema['items']);
		}

		if (($resultsSchema['type'] ?? null) === 'object' && isset($resultsSchema['properties']) && is_array($resultsSchema['properties'])) {
			$items = $resultsSchema['properties']['items'] ?? null;
			if (is_array($items)) {
				$itemsSchema = $this->resolveSchema($schema, $items);
				if (($itemsSchema['type'] ?? null) === 'array' && isset($itemsSchema['items']) && is_array($itemsSchema['items'])) {
					return $this->resolveSchema($schema, $itemsSchema['items']);
				}
				if (($itemsSchema['type'] ?? null) === 'object') {
					return $itemsSchema;
				}
			}
		}

		return null;
	}

	/**
	 * @param array<string, mixed> $schema
	 * @param array<string, mixed> $spec
	 * @return array<string, mixed>|null
	 */
	private function extractSuccessResponseSchema(array $schema, array $spec): ?array {
		$responses = $spec['responses'] ?? null;
		if (!is_array($responses)) {
			return null;
		}

		$success = $responses['200'] ?? null;
		if (!is_array($success)) {
			$first = reset($responses);
			$success = is_array($first) ? $first : null;
		}
		if (!is_array($success)) {
			return null;
		}

		$content = $success['content'] ?? null;
		if (!is_array($content) || $content === []) {
			return null;
		}

		$jsonContent = $content['application/json'] ?? reset($content);
		if (!is_array($jsonContent)) {
			return null;
		}

		$schemaDef = $jsonContent['schema'] ?? null;
		if (!is_array($schemaDef)) {
			return null;
		}

		return $this->resolveSchema($schema, $schemaDef);
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
