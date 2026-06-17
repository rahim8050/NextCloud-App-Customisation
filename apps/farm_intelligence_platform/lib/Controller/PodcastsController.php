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
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

final class PodcastsController extends Controller {
	private const OPERATION_IDS = [
		'list' => 'v1_podcasts_list',
		'retrieve' => 'v1_podcasts_retrieve',
		'episodes_list' => 'v1_podcasts_episodes_list',
		'episodes_stream_retrieve' => 'v1_podcasts_episode_stream',
		'refresh_create' => 'v1_podcasts_refresh',
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

	#[PublicPage]
	public function list(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('list podcasts', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['list'], $requestId);
			$path = (string)($operation['path'] ?? '');
			$this->logProxyRequest('list podcasts', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('list podcasts', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'list podcasts');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'list podcasts');
		}
		return $this->buildSuccessResponse($payload, 'Podcasts loaded.');
	}

	#[PublicPage]
	public function get(string $podcastId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get podcast', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['retrieve'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['podcast_id' => $podcastId]);
			$this->logProxyRequest('get podcast', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('get podcast', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'get podcast');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'get podcast');
		}
		return $this->buildSuccessResponse($payload, 'Podcast loaded.');
	}

	#[PublicPage]
	public function listEpisodes(string $podcastId, ?int $limit = null): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('list podcast episodes', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['episodes_list'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['podcast_id' => $podcastId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
			$this->logProxyRequest('list podcast episodes', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('list podcast episodes', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'list podcast episodes');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'list podcast episodes');
		}
		return $this->buildSuccessResponse($payload, 'Podcast episodes loaded.');
	}

	#[PublicPage]
	public function getStreamUrl(int $episodeId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get podcast stream url', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['episodes_stream_retrieve'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['episode_id' => (string)$episodeId]);
			$this->logProxyRequest('get podcast stream url', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('get podcast stream url', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'get podcast stream url');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'get podcast stream url');
		}
		return $this->buildSuccessResponse($payload, 'Stream URL loaded.');
	}

	#[AdminRequired]
	public function refresh(string $podcastId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('refresh podcast', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['refresh_create'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['podcast_id' => $podcastId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('refresh podcast', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('refresh podcast', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'refresh podcast');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'refresh podcast');
		}
		return $this->buildSuccessResponse($payload, 'Podcast refresh queued.');
	}

	private function logEndpointEntry(string $action, string $requestId): void {
		$path = $this->request->getPathInfo();
		if ($path === '') {
			$path = $this->request->getRequestUri();
		}
		$this->logger->debug(
			'Weather API podcasts endpoint hit',
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
			'Weather API podcasts proxy request',
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
			'Weather API podcasts proxy response',
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
			'Weather API podcasts request failed',
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
			'Weather API podcasts request failed',
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
