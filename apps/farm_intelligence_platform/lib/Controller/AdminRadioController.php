<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Controller;

use OCA\FarmIntelligencePlatform\Service\DrfSchemaService;
use OCA\FarmIntelligencePlatform\Service\LogSanitizer;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

final class AdminRadioController extends Controller {
	private const OPERATION_IDS = [
		'providers_list' => 'v1_radio_providers_list',
		'stations_list' => 'v1_radio_stations_list',
		'stations_retrieve' => 'v1_radio_stations_retrieve',
		'stations_stream_retrieve' => 'v1_radio_stations_stream',
		'stations_now_playing_retrieve' => 'v1_radio_station_now_playing',
		'stations_analytics_retrieve' => 'v1_radio_station_analytics',
		'stations_health_list' => 'v1_radio_station_health_history',
		'radio_health_retrieve' => 'v1_radio_health',
		'emergency_current_retrieve' => 'v1_radio_emergency_current',
		'emergency_history_list' => 'v1_radio_emergency_history',
		'emergency_create' => 'v1_radio_emergency_create',
		'emergency_update' => 'v1_radio_emergency_update',
		'emergency_destroy' => 'v1_radio_emergency_delete',
		'tts_create' => 'v1_radio_tts_synthesize',
	];

	public function __construct(
		string $appName,
		IRequest $request,
		private readonly WeatherApiClientInterface $weatherApiClient,
		private readonly DrfSchemaService $schemaService,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
	}

	#[AdminRequired]
	public function listProviders(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('list radio providers', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['providers_list'], $requestId);
			$path = (string)($operation['path'] ?? '');
			$this->logProxyRequest('list radio providers', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('list radio providers', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'list radio providers');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'list radio providers');
		}

		return $this->buildSuccessResponse($payload, 'Radio providers loaded.');
	}

	#[AdminRequired]
	public function listStations(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('list radio stations', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['stations_list'], $requestId);
			$path = (string)($operation['path'] ?? '');
			$this->logProxyRequest('list radio stations', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('list radio stations', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'list radio stations');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'list radio stations');
		}

		return $this->buildSuccessResponse($payload, 'Radio stations loaded.');
	}

	#[AdminRequired]
	public function getStation(string $stationId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get radio station', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['stations_retrieve'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['station_id' => $stationId]);
			$this->logProxyRequest('get radio station', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('get radio station', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get radio station');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get radio station');
		}

		return $this->buildSuccessResponse($payload, 'Radio station loaded.');
	}

	#[AdminRequired]
	public function getStreamUrl(string $stationId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get radio stream', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['stations_stream_retrieve'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['station_id' => $stationId]);
			$this->logProxyRequest('get radio stream', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('get radio stream', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get radio stream');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get radio stream');
		}

		return $this->buildSuccessResponse($payload, 'Stream URL loaded.');
	}

	#[AdminRequired]
	public function getStationNowPlaying(string $stationId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get radio station now playing', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['stations_now_playing_retrieve'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['station_id' => $stationId]);
			$this->logProxyRequest('get radio station now playing', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('get radio station now playing', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get radio station now playing');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get radio station now playing');
		}

		return $this->buildSuccessResponse($payload, 'Now playing loaded.');
	}

	#[AdminRequired]
	public function getStationAnalytics(string $stationId, ?int $days = null): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get radio station analytics', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['stations_analytics_retrieve'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['station_id' => $stationId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
			$this->logProxyRequest('get radio station analytics', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('get radio station analytics', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get radio station analytics');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get radio station analytics');
		}

		return $this->buildSuccessResponse($payload, 'Analytics loaded.');
	}

	#[AdminRequired]
	public function getStationHealthHistory(string $stationId, ?int $limit = null): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get radio station health history', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['stations_health_list'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['station_id' => $stationId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
			$this->logProxyRequest('get radio station health history', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('get radio station health history', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get radio station health history');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get radio station health history');
		}

		return $this->buildSuccessResponse($payload, 'Station health history loaded.');
	}

	#[AdminRequired]
	public function getRadioHealth(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get radio health', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['radio_health_retrieve'], $requestId);
			$path = (string)($operation['path'] ?? '');
			$this->logProxyRequest('get radio health', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('get radio health', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get radio health');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get radio health');
		}

		return $this->buildSuccessResponse($payload, 'Radio health loaded.');
	}

	#[AdminRequired]
	public function getCurrentEmergency(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get current emergency', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['emergency_current_retrieve'], $requestId);
			$path = (string)($operation['path'] ?? '');
			$this->logProxyRequest('get current emergency', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('get current emergency', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get current emergency');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get current emergency');
		}

		return $this->buildSuccessResponse($payload, 'Current emergency loaded.');
	}

	#[AdminRequired]
	public function getEmergencyHistory(?int $limit = null): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get emergency history', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['emergency_history_list'], $requestId);
			$path = (string)($operation['path'] ?? '');
			$params = $this->stripPathParams($this->request->getParams(), $path);
			$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
			$this->logProxyRequest('get emergency history', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('get emergency history', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get emergency history');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get emergency history');
		}

		return $this->buildSuccessResponse($payload, 'Emergency history loaded.');
	}

	#[AdminRequired]
	public function createEmergency(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('create emergency', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['emergency_create'], $requestId);
			$path = (string)($operation['path'] ?? '');
			$params = $this->stripPathParams($this->request->getParams(), $path);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('create emergency', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('create emergency', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'create emergency');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'create emergency');
		}
		return $this->buildSuccessResponse($payload, 'Emergency broadcast created.', Http::STATUS_CREATED);
	}

	#[AdminRequired]
	public function updateEmergency(int $pk): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('update emergency', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['emergency_update'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['pk' => (string)$pk]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('update emergency', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'PATCH'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('update emergency', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'update emergency');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'update emergency');
		}
		return $this->buildSuccessResponse($payload, 'Emergency broadcast updated.');
	}

	#[AdminRequired]
	public function deleteEmergency(int $pk): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('delete emergency', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['emergency_destroy'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['pk' => (string)$pk]);
			$this->logProxyRequest('delete emergency', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'DELETE'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('delete emergency', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'delete emergency');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'delete emergency');
		}
		return $this->buildSuccessResponse($payload, 'Emergency broadcast removed.');
	}

	#[AdminRequired]
	public function synthesizeTts(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('synthesize TTS', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['tts_create'], $requestId);
			$path = (string)($operation['path'] ?? '');
			$params = $this->stripPathParams($this->request->getParams(), $path);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('synthesize TTS', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('synthesize TTS', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'synthesize TTS');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'synthesize TTS');
		}
		return $this->buildSuccessResponse($payload, 'TTS synthesis complete.');
	}

	private function logEndpointEntry(string $action, string $requestId): void {
		$path = $this->request->getPathInfo();
		if ($path === '') {
			$path = $this->request->getRequestUri();
		}

		$this->logger->debug(
			'Weather API radio endpoint hit',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'method' => $this->request->getMethod(),
				'path' => $path,
			]),
		);
	}

	private function logProxyRequest(
		string $action,
		array $operation,
		string $path,
		array $query,
		string $requestId,
	): void {
		$this->logger->debug(
			'Weather API radio proxy request',
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
			'Weather API radio proxy response',
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

	private function extractPathParamNames(string $path): array {
		preg_match_all('/{([^}]+)}/', $path, $matches);
		if (!isset($matches[1])) {
			return [];
		}
		return array_map('strval', $matches[1]);
	}

	private function filterQueryParams(array $params, array $definitions): array {
		$reserved = ['requesttoken', 'format', '_route', 'ocs-apirequest', 'ocs_api_request', 'ocsapirequest'];
		if ($definitions === []) {
			$unknown = $this->collectUnknownParams($params, [], $reserved);
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

		$unknown = $this->collectUnknownParams($params, array_keys($allowed), $reserved);
		if ($unknown !== []) {
			throw new WeatherApiException(
				'invalid_argument',
				'Unknown query parameters: ' . implode(', ', $unknown),
			);
		}

		$filtered = [];
		foreach ($params as $key => $value) {
			if (isset($allowed[$key])) {
				if ($value === '' || $value === null) {
					continue;
				}
				$filtered[$key] = $value;
			}
		}

		return $filtered;
	}

	private function collectUnknownParams(array $params, array $allowed, array $reserved = []): array {
		$allowedLookup = array_fill_keys($allowed, true);
		$reservedLower = array_map('strtolower', $reserved);
		$unknown = [];
		foreach ($params as $key => $_value) {
			if (!is_string($key)) {
				continue;
			}
			if (in_array(strtolower($key), $reservedLower, true)) {
				continue;
			}
			if (!isset($allowedLookup[$key])) {
				$unknown[] = $key;
			}
		}
		return $unknown;
	}

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

	private function filterBodyParams(array $params, array $definitions, bool $requireAll): array {
		$reserved = ['requesttoken', 'format', '_route', 'ocs-apirequest', 'ocs_api_request', 'ocsapirequest'];
		if ($definitions === []) {
			$unknown = $this->collectUnknownParams($params, [], $reserved);
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
		foreach ($definitions as $name => $definition) {
			if (!is_string($name)) {
				continue;
			}
			$allowed[$name] = true;
			if (($definition['required'] ?? false) === true) {
				$required[] = $name;
			}
		}

		$unknown = $this->collectUnknownParams($params, array_keys($allowed), $reserved);
		if ($unknown !== []) {
			throw new WeatherApiException(
				'invalid_argument',
				'Unknown fields: ' . implode(', ', $unknown),
			);
		}

		$filtered = [];
		foreach ($params as $key => $value) {
			if (isset($allowed[$key])) {
				$filtered[$key] = $value;
			}
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

	private function buildSuccessResponse(?array $data, string $message, int $status = Http::STATUS_OK): JSONResponse {
		return new JSONResponse([
			'status' => 'ok',
			'ok' => true,
			'message' => $message,
			'data' => $data,
		], $status);
	}

	private function handleWeatherApiException(WeatherApiException $exception, string $requestId, string $action): JSONResponse {
		$this->logger->warning(
			'Weather API radio request failed',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'code' => $exception->getErrorCode(),
			]),
		);

		return new JSONResponse([
			'status' => 'error',
			'ok' => false,
			'message' => $exception->getMessage(),
			'error' => [
				'code' => $exception->getErrorCode(),
				'message' => $exception->getMessage(),
				'requestId' => $requestId,
				'details' => $exception->getDetails(),
			],
		], Http::STATUS_BAD_REQUEST);
	}

	private function handleUnexpectedError(\Throwable $throwable, string $requestId, string $action): JSONResponse {
		$this->logger->warning(
			'Weather API radio request failed',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'error' => $throwable->getMessage(),
			]),
		);

		return new JSONResponse([
			'status' => 'error',
			'ok' => false,
			'message' => 'Backend request failed.',
			'error' => [
				'code' => 'backend_error',
				'message' => 'Backend request failed.',
				'requestId' => $requestId,
				'details' => new \stdClass(),
			],
		], Http::STATUS_BAD_REQUEST);
	}
}
