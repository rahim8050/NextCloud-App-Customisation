<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Controller;

use OCA\FarmIntelligencePlatform\Service\DrfSchemaService;
use OCA\FarmIntelligencePlatform\Service\HttpStatus;
use OCA\FarmIntelligencePlatform\Service\LogSanitizer;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

final class AdminActivitiesController extends Controller {
	private const RESERVED_PARAMS = [
		'requesttoken',
		'format',
		'id',
		'activity_id',
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
		$this->logEndpointEntry('activity schema', $requestId);

		try {
			$result = $this->schemaService->getActivitySchemaSummary($requestId);
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
				'Weather API activity schema fetch failed',
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
			'Weather API activity schema summary',
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
	public function listActivities(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('list activities', $requestId);

		try {
			$operation = $this->schemaService->getActivityOperation('list', $requestId);
			$queryDefs = $operation['queryParams'] ?? [];
			if (!is_array($queryDefs)) {
				$queryDefs = [];
			}
			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);
			$query = $this->buildListQueryParams($params, $queryDefs);
			$path = (string)($operation['path'] ?? '');
			$this->logProxyRequest('list activities', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('list activities', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'list activities');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'list activities');
		}

		return $this->buildSuccessResponse($payload, 'Activities loaded.');
	}


	#[AdminRequired]
	public function createActivity(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('create activity', $requestId);

		try {
			$operation = $this->schemaService->getActivityOperation('create', $requestId);
			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], true);
			$path = (string)($operation['path'] ?? '');
			$this->logProxyRequest('create activity', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body,
				$requestId,
			);
			$this->logProxyResponse('create activity', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'create activity');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'create activity');
		}

		return $this->buildSuccessResponse($payload, 'Activity created.', Http::STATUS_CREATED);
	}


	#[AdminRequired]
	public function getActivity(string $id): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get activity', $requestId);

		try {
			$operation = $this->schemaService->getActivityOperation('retrieve', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['id' => $id]);
			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->logProxyRequest('get activity', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('get activity', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get activity');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get activity');
		}

		return $this->buildSuccessResponse($payload, 'Activity loaded.');
	}


	#[AdminRequired]
	public function updateActivity(string $id): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('update activity', $requestId);

		try {
			$operation = $this->schemaService->getActivityOperation('update', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['id' => $id]);
			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], true);
			$this->logProxyRequest('update activity', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'PUT'),
				$path,
				[],
				$body,
				$requestId,
			);
			$this->logProxyResponse('update activity', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'update activity');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'update activity');
		}

		return $this->buildSuccessResponse($payload, 'Activity updated.');
	}


	#[AdminRequired]
	public function patchActivity(string $id): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('patch activity', $requestId);

		try {
			$operation = $this->schemaService->getActivityOperation('partial_update', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['id' => $id]);

			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);

			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('patch activity', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'PATCH'),
				$path,
				[],
				$body,
				$requestId,
			);
			$this->logProxyResponse('patch activity', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'patch activity');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'patch activity');
		}

		return $this->buildSuccessResponse($payload, 'Activity updated.');
	}


	#[AdminRequired]
	public function getHealth(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('activities health', $requestId);
		try {
			$result = $this->weatherApiClient->requestJsonWithStatus('GET', '/api/v1/activities/health/', [], null, $requestId);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'activities health');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'activities health');
		}
		return new JSONResponse($payload, HttpStatus::normalize($result['statusCode']));
	}

	#[AdminRequired]
	public function deleteActivity(string $id): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('delete activity', $requestId);

		try {
			$operation = $this->schemaService->getActivityOperation('destroy', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['id' => $id]);
			$this->logProxyRequest('delete activity', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'DELETE'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('delete activity', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'delete activity');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'delete activity');
		}

		return $this->buildSuccessResponse($payload, 'Activity deleted.');
	}


	private function logEndpointEntry(string $action, string $requestId): void {
		$path = $this->request->getPathInfo();
		if ($path === '') {
			$path = $this->request->getRequestUri();
		}

		$this->logger->debug(
			'Weather API admin endpoint hit',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'method' => $this->request->getMethod(),
				'path' => $path,
			]),
		);
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
		], HttpStatus::normalize($status));
	}

	/**
	 * @param array<array-key, mixed>|null $data
	 */
	private function buildSuccessResponse(?array $data, string $message, int $status = Http::STATUS_OK): JSONResponse {
		return new JSONResponse([
			'status' => 'ok',
			'ok' => true,
			'message' => $message,
			'data' => $data,
		], HttpStatus::normalize($status));
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
	private function logProxyResponse(
		string $action,
		array $operation,
		string $path,
		string $requestId,
		int $statusCode,
	): void {
		$this->logger->debug(
			'Weather API admin proxy response',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'method' => (string)($operation['method'] ?? ''),
				'pathTemplate' => (string)($operation['path'] ?? ''),
				'path' => $path,
				'httpStatus' => $statusCode,
			]),
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
