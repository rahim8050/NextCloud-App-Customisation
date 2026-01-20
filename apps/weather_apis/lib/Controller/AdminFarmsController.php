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

		return $this->buildSuccessResponse([
			'schema' => $result['schema'],
			'warning' => $result['warning'],
		], 'Schema loaded.');
	}

	#[AdminRequired]
	public function listFarms(): JSONResponse {
		$requestId = $this->resolveRequestId();

		try {
			$operation = $this->schemaService->getFarmOperation('list', $requestId);
			$queryDefs = $operation['queryParams'] ?? [];
			if (!is_array($queryDefs)) {
				$queryDefs = [];
			}
			$query = $this->buildListQueryParams($this->request->getParams(), $queryDefs);
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
			$body = $this->filterBodyParams(
				$this->request->getParams(),
				$operation['bodyFields'] ?? [],
				true,
			);
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
			$query = $this->filterQueryParams(
				$this->request->getParams(),
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
			$body = $this->filterBodyParams(
				$this->request->getParams(),
				$operation['bodyFields'] ?? [],
				true,
			);
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
			$body = $this->filterBodyParams(
				$this->request->getParams(),
				$operation['bodyFields'] ?? [],
				false,
			);
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

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_latest', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['farm_id' => $farmId]);
			$query = $this->filterQueryParams(
				$this->request->getParams(),
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
			return $this->handleWeatherApiException($exception, $requestId, 'ndvi latest');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndvi latest');
		}

		return $this->buildSuccessResponse($payload, 'NDVI latest loaded.');
	}

	#[AdminRequired]
	public function getNdviTimeseries(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_timeseries', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['farm_id' => $farmId]);
			$query = $this->filterQueryParams(
				$this->request->getParams(),
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
			return $this->handleWeatherApiException($exception, $requestId, 'ndvi timeseries');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndvi timeseries');
		}

		return $this->buildSuccessResponse($payload, 'NDVI timeseries loaded.');
	}

	#[AdminRequired]
	public function getNdviRasterPng(string $farmId): Response {
		$requestId = $this->resolveRequestId();

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_raster', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['farm_id' => $farmId]);
			$query = $this->filterQueryParams(
				$this->request->getParams(),
				$operation['queryParams'] ?? [],
			);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
		} catch (WeatherApiException $exception) {
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

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_raster_queue', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['farm_id' => $farmId]);
			$body = $this->filterBodyParams(
				$this->request->getParams(),
				$operation['bodyFields'] ?? [],
				false,
			);
			$payload = $this->weatherApiClient->requestJson(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'ndvi raster queue');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndvi raster queue');
		}

		return $this->buildSuccessResponse($payload, 'NDVI raster queued.');
	}

	#[AdminRequired]
	public function refreshNdvi(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_refresh', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['farm_id' => $farmId]);
			$body = $this->filterBodyParams(
				$this->request->getParams(),
				$operation['bodyFields'] ?? [],
				false,
			);
			$payload = $this->weatherApiClient->requestJson(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
		} catch (WeatherApiException $exception) {
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
		preg_match_all('/{([^}]+)}/', $path, $matches);
		if (!isset($matches[1])) {
			return $path;
		}

		foreach ($matches[1] as $name) {
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
