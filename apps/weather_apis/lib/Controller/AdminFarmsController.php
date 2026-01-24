<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Controller;

use OCA\WeatherApis\Service\DrfSchemaService;
use OCA\WeatherApis\Service\LogSanitizer;
use OCA\WeatherApis\Service\WeatherApiClientInterface;
use OCA\WeatherApis\Service\WeatherApiException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Response;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

final class AdminFarmsController extends Controller {
	private const RESERVED_PARAMS = [
		'requesttoken',
		'format',
		'id',
		'farm_id',
		'_route',
		'ocs-apirequest',
		'ocs_api_request',
		'ocsapirequest',
	];

	public function __construct(
		string $appName,
		IRequest $request,
		private readonly DrfSchemaService $schemaService,
		private readonly WeatherApiClientInterface $weatherApiClient,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
	}

	#[AdminRequired]
	public function getSchema(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logger->debug(
			'Weather API farm schema endpoint hit',
			LogSanitizer::sanitizeContext([
				'requestId' => $requestId,
			]),
		);

		try {
			$result = $this->schemaService->getFarmSchemaSummary($requestId);
		} catch (WeatherApiException $exception) {
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			$this->logger->warning(
				'Weather API schema fetch failed',
				LogSanitizer::sanitizeContext([
					'requestId' => $requestId,
					'error' => $throwable->getMessage(),
				]),
			);
			return $this->buildErrorResponse(
				'backend_error',
				'Unable to load schema.',
				$requestId,
				Http::STATUS_BAD_REQUEST,
			);
		}

		$schema = is_array($result['schema'] ?? null) ? $result['schema'] : [];
		$fields = is_array($schema['fields'] ?? null) ? $schema['fields'] : [];
		$columns = $schema['columns'] ?? [];
		if (!is_array($columns)) {
			$columns = [];
		}
		$operationIds = [];
		$operations = $schema['operations'] ?? [];
		if (is_array($operations)) {
			foreach ($operations as $operation) {
				if (!is_array($operation)) {
					continue;
				}
				$operationId = $operation['operationId'] ?? null;
				if (is_string($operationId) && $operationId !== '') {
					$operationIds[] = $operationId;
				}
			}
		}
		$this->logger->debug(
			'Weather API farm schema summary',
			LogSanitizer::sanitizeContext([
				'requestId' => $requestId,
				'fieldsCount' => count($fields),
				'columnsCount' => count($columns),
				'operationIds' => $operationIds,
			]),
		);

		$payload = $schema;
		$payload['warning'] = $result['warning'];
		$payload['schema'] = $schema;

		return $this->buildSuccessResponse($payload, 'Schema loaded.');
	}

	#[AdminRequired]
	public function listFarms(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logger->debug(
			'Weather API farms list endpoint hit',
			LogSanitizer::sanitizeContext([
				'requestId' => $requestId,
			]),
		);

		try {
			$operation = $this->schemaService->getFarmOperation('list', $requestId);
			$queryDefs = $operation['queryParams'] ?? [];
			if (!is_array($queryDefs)) {
				$queryDefs = [];
			}
			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);
			$query = $this->buildListQueryParams($params, $queryDefs);
			$this->logger->debug(
				'Weather API farms list outbound',
				LogSanitizer::sanitizeContext([
					'requestId' => $requestId,
					'method' => 'GET',
					'path' => (string)($operation['path'] ?? ''),
				]),
			);
			$payload = $this->weatherApiClient->requestJson(
				'GET',
				(string)($operation['path'] ?? ''),
				$query,
				null,
				$requestId,
			);
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'list farms');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'list farms');
		}

		return $this->buildSuccessResponse($payload, 'Farms loaded.');
	}

	#[AdminRequired]
	public function createFarm(): JSONResponse {
		$requestId = $this->resolveRequestId();

		try {
			$operation = $this->schemaService->getFarmOperation('create', $requestId);
			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], true);
			$payload = $this->weatherApiClient->requestJson(
				(string)($operation['method'] ?? 'POST'),
				(string)($operation['path'] ?? ''),
				[],
				$body,
				$requestId,
			);
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'create farm');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'create farm');
		}

		return $this->buildSuccessResponse($payload, 'Farm created.');
	}

	#[AdminRequired]
	public function getFarm(string $id): JSONResponse {
		$requestId = $this->resolveRequestId();

		try {
			$operation = $this->schemaService->getFarmOperation('retrieve', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['id' => $id]);
			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$payload = $this->weatherApiClient->requestJson(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get farm');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get farm');
		}

		return $this->buildSuccessResponse($payload, 'Farm loaded.');
	}

	#[AdminRequired]
	public function updateFarm(string $id): JSONResponse {
		$requestId = $this->resolveRequestId();

		try {
			$operation = $this->schemaService->getFarmOperation('update', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['id' => $id]);
			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], true);
			$payload = $this->weatherApiClient->requestJson(
				(string)($operation['method'] ?? 'PUT'),
				$path,
				[],
				$body,
				$requestId,
			);
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'update farm');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'update farm');
		}

		return $this->buildSuccessResponse($payload, 'Farm updated.');
	}

	#[AdminRequired]
	public function patchFarm(string $id): JSONResponse {
		$requestId = $this->resolveRequestId();

		try {
			$operation = $this->schemaService->getFarmOperation('partial_update', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['id' => $id]);
			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$payload = $this->weatherApiClient->requestJson(
				(string)($operation['method'] ?? 'PATCH'),
				$path,
				[],
				$body,
				$requestId,
			);
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'patch farm');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'patch farm');
		}

		return $this->buildSuccessResponse($payload, 'Farm updated.');
	}

	#[AdminRequired]
	public function deleteFarm(string $id): JSONResponse {
		$requestId = $this->resolveRequestId();

		try {
			$operation = $this->schemaService->getFarmOperation('destroy', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['id' => $id]);
			$payload = $this->weatherApiClient->requestJson(
				(string)($operation['method'] ?? 'DELETE'),
				$path,
				[],
				null,
				$requestId,
			);
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'delete farm');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'delete farm');
		}

		return $this->buildSuccessResponse($payload, 'Farm deleted.');
	}

	#[AdminRequired]
	public function getNdviLatest(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_latest', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->logProxyRequest('ndvi latest', $operation, $path, $query, $requestId);
			$payload = $this->weatherApiClient->requestJson(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndvi latest', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndvi latest');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndvi latest');
		}

		return $this->buildSuccessResponse($payload, 'NDVI latest loaded.');
	}

	#[AdminRequired]
	public function getNdviTimeseries(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_timeseries', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$startName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'start');
			$endName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'end');
			$this->ensureDateOrder($query, $startName, $endName);
			$this->logProxyRequest('ndvi timeseries', $operation, $path, $query, $requestId);
			$payload = $this->weatherApiClient->requestJson(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndvi timeseries', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndvi timeseries');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndvi timeseries');
		}

		return $this->buildSuccessResponse($payload, 'NDVI timeseries loaded.');
	}

	#[AdminRequired]
	public function getNdviRasterPng(string $farmId): Response {
		$requestId = $this->resolveRequestId();
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_raster', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
			if ($dateField !== null && array_key_exists($dateField, $query)) {
				$this->parseIsoDateValue($query[$dateField], $dateField);
			}
			$this->logProxyRequest('ndvi raster', $operation, $path, $query, $requestId);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndvi raster', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndvi raster');
		}

		$response = new DataDisplayResponse($binary['body'], Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}

	#[AdminRequired]
	public function queueNdviRaster(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_raster_queue', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$query = [];
			$body = null;
			$bodyFields = $operation['bodyFields'] ?? [];
			if ($bodyFields !== []) {
				$body = $this->filterBodyParams($params, $bodyFields, true);
				$dateField = $this->resolveBodyFieldName($bodyFields, 'date');
				if ($dateField !== null && array_key_exists($dateField, $body)) {
					$this->parseIsoDateValue($body[$dateField], $dateField);
				}
			} else {
				$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
				$this->requireQueryParams($query, $operation['queryParams'] ?? []);
				$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
				if ($dateField !== null && array_key_exists($dateField, $query)) {
					$this->parseIsoDateValue($query[$dateField], $dateField);
				}
			}
			$this->logProxyRequest('ndvi raster queue', $operation, $path, $query, $requestId);
			$payload = $this->weatherApiClient->requestJson(
				(string)($operation['method'] ?? 'POST'),
				$path,
				$query,
				$body === [] ? null : $body,
				$requestId,
			);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndvi raster queue', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndvi raster queue');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndvi raster queue');
		}

		return $this->buildSuccessResponse($payload, 'NDVI raster queued.');
	}

	#[AdminRequired]
	public function refreshNdvi(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_refresh', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('ndvi refresh', $operation, $path, [], $requestId);
			$payload = $this->weatherApiClient->requestJson(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndvi refresh', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndvi refresh');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndvi refresh');
		}

		return $this->buildSuccessResponse($payload, 'NDVI refresh queued.');
	}

	private function resolveRequestId(): string {
		$header = $this->request->getHeader('X-Request-Id');
		if ($header !== '') {
			return $header;
		}

		$bytes = random_bytes(16);
		$bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
		$bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
	}

	/**
	 * @param array<string, mixed> $params
	 * @param list<array{name: string, type: string, format: string|null, required: bool}> $definitions
	 * @return array<string, mixed>
	 */
	private function filterQueryParams(array $params, array $definitions): array {
		if ($definitions === []) {
			$unknown = $this->collectUnknownParams($params, []);
			if ($unknown !== []) {
				throw new WeatherApiException(
					'invalid_argument',
					'Unknown query parameters: ' . implode(', ', $unknown),
				);
			}
			return [];
		}

		$allowed = [];
		foreach ($definitions as $definition) {
			if (isset($definition['name']) && is_string($definition['name'])) {
				$allowed[$definition['name']] = true;
			}
		}

		$unknown = $this->collectUnknownParams($params, array_keys($allowed));
		if ($unknown !== []) {
			throw new WeatherApiException(
				'invalid_argument',
				'Unknown query parameters: ' . implode(', ', $unknown),
			);
		}

		$filtered = [];
		foreach ($params as $key => $value) {
			if (!$this->isParamAllowed($key, $allowed)) {
				continue;
			}
			if ($value === '' || $value === null) {
				continue;
			}
			$filtered[$key] = $value;
		}

		return $filtered;
	}

	/**
	 * @param array<string, mixed> $params
	 * @param list<array{name: string, type: string, format: string|null, required: bool}> $definitions
	 * @return array<string, mixed>
	 */
	private function buildListQueryParams(array $params, array $definitions): array {
		if ($definitions === []) {
			return $this->filterPassthroughQueryParams($params);
		}

		return $this->filterQueryParams($params, $definitions);
	}

	/**
	 * @param array<string, mixed> $params
	 * @return array<string, mixed>
	 */
	private function filterPassthroughQueryParams(array $params): array {
		$filtered = [];
		foreach ($params as $key => $value) {
			if (!is_string($key)) {
				continue;
			}
			if ($this->isReservedParam($key)) {
				continue;
			}
			if (is_array($value)) {
				$values = array_values(array_filter(
					$value,
					static fn ($entry) => $entry !== '' && $entry !== null,
				));
				if ($values === []) {
					continue;
				}
				$filtered[$key] = $values;
				continue;
			}
			if ($value === '' || $value === null) {
				continue;
			}
			$filtered[$key] = $value;
		}

		return $filtered;
	}

	/**
	 * @param array<string, mixed> $query
	 * @param list<array{name: string, type: string, format: string|null, required: bool}> $definitions
	 */
	private function requireQueryParams(array $query, array $definitions): void {
		if ($definitions === []) {
			return;
		}

		foreach ($definitions as $definition) {
			if (($definition['required'] ?? false) !== true) {
				continue;
			}
			$name = $definition['name'] ?? null;
			if (!is_string($name) || $name === '') {
				continue;
			}
			if (!array_key_exists($name, $query)) {
				throw new WeatherApiException('invalid_argument', 'Missing required query parameter: ' . $name);
			}
			$value = $query[$name];
			if ($value === null || (is_string($value) && trim($value) === '')) {
				throw new WeatherApiException('invalid_argument', 'Missing required query parameter: ' . $name);
			}
		}
	}

	/**
	 * @param list<array{name: string, type: string, format: string|null, required: bool}> $definitions
	 */
	private function resolveQueryParamName(array $definitions, string $desired): ?string {
		foreach ($definitions as $definition) {
			$name = $definition['name'] ?? null;
			if (is_string($name) && $name === $desired) {
				return $name;
			}
		}

		foreach ($definitions as $definition) {
			$name = $definition['name'] ?? null;
			if (is_string($name) && str_contains($name, $desired)) {
				return $name;
			}
		}

		return null;
	}

	/**
	 * @param array<string, array<string, mixed>> $definitions
	 */
	private function resolveBodyFieldName(array $definitions, string $desired): ?string {
		if (isset($definitions[$desired])) {
			return $desired;
		}

		foreach (array_keys($definitions) as $name) {
			if (is_string($name) && str_contains($name, $desired)) {
				return $name;
			}
		}

		return null;
	}

	/**
	 * @param array<string, mixed> $query
	 */
	private function ensureDateOrder(array $query, ?string $startKey, ?string $endKey): void {
		if ($startKey === null || $endKey === null) {
			return;
		}
		if (!array_key_exists($startKey, $query) || !array_key_exists($endKey, $query)) {
			return;
		}

		$startValue = $query[$startKey];
		$endValue = $query[$endKey];
		$startDate = $this->parseIsoDateValue($startValue, $startKey);
		$endDate = $this->parseIsoDateValue($endValue, $endKey);

		if ($startDate > $endDate) {
			throw new WeatherApiException('invalid_argument', 'Start date must be on or before end date.');
		}
	}

	private function parseIsoDateValue(mixed $value, string $field): \DateTimeImmutable {
		if (!is_string($value)) {
			throw new WeatherApiException('invalid_argument', 'Invalid date for ' . $field . '.');
		}
		$trimmed = trim($value);
		if ($trimmed === '') {
			throw new WeatherApiException('invalid_argument', 'Invalid date for ' . $field . '.');
		}

		$date = \DateTimeImmutable::createFromFormat('Y-m-d', $trimmed);
		$errors = \DateTimeImmutable::getLastErrors();
		if ($date === false) {
			throw new WeatherApiException('invalid_argument', 'Invalid date for ' . $field . '.');
		}
		if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
			throw new WeatherApiException('invalid_argument', 'Invalid date for ' . $field . '.');
		}

		if ($date->format('Y-m-d') !== $trimmed) {
			throw new WeatherApiException('invalid_argument', 'Invalid date for ' . $field . '.');
		}

		return $date;
	}

	/**
	 * @param array<string, mixed> $params
	 * @param array<string, array<string, mixed>> $definitions
	 * @return array<string, mixed>
	 */
	private function filterBodyParams(array $params, array $definitions, bool $requireAll): array {
		if ($definitions === []) {
			$unknown = $this->collectUnknownParams($params, []);
			if ($unknown !== []) {
				throw new WeatherApiException(
					'invalid_argument',
					'Unknown fields: ' . implode(', ', $unknown),
				);
			}
			return [];
		}

		$allowed = [];
		$required = [];
		$ignored = [];

		foreach ($definitions as $name => $definition) {
			if (!is_string($name)) {
				continue;
			}
			if (($definition['readOnly'] ?? false) === true) {
				$ignored[] = $name;
				continue;
			}
			$allowed[$name] = true;
			if (($definition['required'] ?? false) === true) {
				$required[] = $name;
			}
		}

		$unknown = $this->collectUnknownParams($params, array_keys($allowed), $ignored);
		if ($unknown !== []) {
			throw new WeatherApiException(
				'invalid_argument',
				'Unknown fields: ' . implode(', ', $unknown),
			);
		}

		$filtered = [];
		foreach ($params as $key => $value) {
			if (!$this->isParamAllowed($key, $allowed)) {
				continue;
			}
			$filtered[$key] = $value;
		}

		if ($requireAll) {
			foreach ($required as $field) {
				if (!array_key_exists($field, $filtered)) {
					throw new WeatherApiException('invalid_argument', 'Missing required field: ' . $field);
				}
				$value = $filtered[$field];
				if (is_string($value) && trim($value) === '') {
					throw new WeatherApiException('invalid_argument', 'Missing required field: ' . $field);
				}
			}
		}

		return $filtered;
	}

	/**
	 * @param array<string, mixed> $params
	 * @param list<string> $allowed
	 * @param list<string> $ignored
	 * @return list<string>
	 */
	private function collectUnknownParams(array $params, array $allowed, array $ignored = []): array {
		$allowedLookup = array_fill_keys($allowed, true);
		$ignoredLookup = array_fill_keys($ignored, true);
		$unknown = [];
		foreach ($params as $key => $_value) {
			if (!is_string($key)) {
				continue;
			}
			if ($this->isReservedParam($key)) {
				continue;
			}
			if (isset($ignoredLookup[$key])) {
				continue;
			}
			if (!isset($allowedLookup[$key])) {
				$unknown[] = $key;
			}
		}

		return $unknown;
	}

	/**
	 * @param array<string, bool> $allowed
	 */
	private function isParamAllowed(string $name, array $allowed): bool {
		return !$this->isReservedParam($name) && isset($allowed[$name]);
	}

	private function isReservedParam(string $name): bool {
		return in_array(strtolower($name), self::RESERVED_PARAMS, true);
	}

	private function applyPathParams(string $path, array $params): string {
		$names = $this->extractPathParamNames($path);
		if ($names === []) {
			return $path;
		}

		foreach ($names as $name) {
			if (!array_key_exists($name, $params)) {
				throw new WeatherApiException('invalid_argument', 'Missing path parameter: ' . $name);
			}
			$value = (string)$params[$name];
			if ($value === '') {
				throw new WeatherApiException('invalid_argument', 'Missing path parameter: ' . $name);
			}
			$path = str_replace('{' . $name . '}', rawurlencode($value), $path);
		}

		return $path;
	}

	/**
	 * @param array<string, mixed> $params
	 * @return array<string, mixed>
	 */
	private function stripPathParams(array $params, string $pathTemplate): array {
		$names = $this->extractPathParamNames($pathTemplate);
		if ($names === []) {
			return $params;
		}

		foreach ($names as $name) {
			unset($params[$name]);
			$camel = $this->snakeToCamel($name);
			if ($camel !== $name) {
				unset($params[$camel]);
			}
		}

		return $params;
	}

	/**
	 * @return list<string>
	 */
	private function extractPathParamNames(string $path): array {
		preg_match_all('/{([^}]+)}/', $path, $matches);
		if (!isset($matches[1])) {
			return [];
		}

		return array_map('strval', $matches[1]);
	}

	private function snakeToCamel(string $value): string {
		if (!str_contains($value, '_')) {
			return $value;
		}

		$parts = array_filter(explode('_', $value), static fn ($part) => $part !== '');
		if ($parts === []) {
			return $value;
		}

		$first = array_shift($parts);
		$parts = array_map(static fn ($part) => ucfirst(strtolower($part)), $parts);

		return $first . implode('', $parts);
	}

	private function validateFarmId(string $farmId, string $requestId): ?JSONResponse {
		$trimmed = trim($farmId);
		if ($trimmed === '' || !ctype_digit($trimmed)) {
			return $this->buildErrorResponse(
				'invalid_argument',
				'Invalid farmId.',
				$requestId,
				Http::STATUS_BAD_REQUEST,
				['farmId' => $farmId],
			);
		}

		return null;
	}

	/**
	 * @param array<string, mixed>|null $details
	 */
	private function buildErrorResponse(
		string $code,
		string $message,
		string $requestId,
		int $status,
		?array $details = null,
	): JSONResponse {
		$detailsPayload = $details === null || $details === [] ? new \stdClass() : $details;

		return new JSONResponse([
			'status' => 'error',
			'ok' => false,
			'message' => $message,
			'error' => [
				'code' => $code,
				'message' => $message,
				'requestId' => $requestId,
				'details' => $detailsPayload,
			],
		], $status);
	}

	/**
	 * @param array<string, mixed> $data
	 */
	private function buildSuccessResponse(array $data, string $message, int $status = Http::STATUS_OK): JSONResponse {
		return new JSONResponse([
			'status' => 'ok',
			'ok' => true,
			'message' => $message,
			'data' => $data,
		], $status);
	}

	private function handleWeatherApiException(WeatherApiException $exception, string $requestId, string $action): JSONResponse {
		$this->logger->warning(
			'Weather API admin request failed',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'code' => $exception->getErrorCode(),
				'reason' => $exception->getReason() ?? '',
			]),
		);

		return $this->buildErrorResponse(
			$exception->getErrorCode(),
			$exception->getMessage(),
			$requestId,
			$this->httpStatusForCode($exception->getErrorCode()),
			$exception->getDetails(),
		);
	}

	/**
	 * @param array<string, mixed> $operation
	 * @param array<string, mixed> $query
	 */
	private function logProxyRequest(
		string $action,
		array $operation,
		string $path,
		array $query,
		string $requestId,
	): void {
		$this->logger->debug(
			'Weather API admin proxy request',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'method' => (string)($operation['method'] ?? ''),
				'pathTemplate' => (string)($operation['path'] ?? ''),
				'path' => $path,
				'queryKeys' => array_values(array_keys($query)),
			]),
		);
	}

	/**
	 * @param array<string, mixed> $operation
	 * @param array<string, mixed> $query
	 */
	private function logProxyError(
		string $action,
		array $operation,
		string $path,
		array $query,
		string $requestId,
		WeatherApiException $exception,
	): void {
		$details = $exception->getDetails();
		$context = [
			'action' => $action,
			'requestId' => $requestId,
			'method' => (string)($operation['method'] ?? ''),
			'pathTemplate' => (string)($operation['path'] ?? ''),
			'path' => $path,
			'queryKeys' => array_values(array_keys($query)),
		];

		if (isset($details['httpStatus'])) {
			$context['httpStatus'] = $details['httpStatus'];
		}
		if (isset($details['responseContentType'])) {
			$context['responseContentType'] = $details['responseContentType'];
		}

		$this->logger->debug(
			'Weather API admin proxy error',
			LogSanitizer::sanitizeContext($context),
		);
	}

	private function handleUnexpectedError(\Throwable $throwable, string $requestId, string $action): JSONResponse {
		$this->logger->warning(
			'Weather API admin request failed',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'error' => $throwable->getMessage(),
			]),
		);

		return $this->buildErrorResponse(
			'backend_error',
			'Backend request failed.',
			$requestId,
			Http::STATUS_BAD_REQUEST,
		);
	}

	private function httpStatusForCode(string $code): int {
		return match ($code) {
			'invalid_argument' => Http::STATUS_BAD_REQUEST,
			'unauthorized' => Http::STATUS_UNAUTHORIZED,
			'forbidden' => Http::STATUS_FORBIDDEN,
			'backend_timeout' => Http::STATUS_GATEWAY_TIMEOUT,
			'backend_unavailable' => Http::STATUS_SERVICE_UNAVAILABLE,
			default => Http::STATUS_BAD_REQUEST,
		};
	}
}
