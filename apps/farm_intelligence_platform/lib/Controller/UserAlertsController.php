<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Controller;

use OCA\FarmIntelligencePlatform\Service\DrfSchemaService;
use OCA\FarmIntelligencePlatform\Service\LogSanitizer;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\UseSession;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

#[UseSession]
final class UserAlertsController extends Controller {
	private const OPERATION_IDS = [
		'subscriptions_list' => 'v1_alerts_subscriptions_list',
		'subscriptions_create' => 'v1_alerts_subscriptions_create',
		'subscriptions_retrieve' => 'v1_alerts_subscriptions_retrieve',
		'subscriptions_update' => 'v1_alerts_subscriptions_update',
		'subscriptions_destroy' => 'v1_alerts_subscriptions_destroy',
		'alerts_list' => 'v1_alerts_alerts_list',
		'alerts_retrieve' => 'v1_alerts_alerts_retrieve',
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

	public function listSubscriptions(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('list subscriptions', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['subscriptions_list'], $requestId);
			$path = (string)($operation['path'] ?? '');
			$this->logProxyRequest('list subscriptions', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('list subscriptions', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'list subscriptions');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'list subscriptions');
		}
		return $this->buildSuccessResponse($payload, 'Subscriptions loaded.');
	}

	public function createSubscription(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('create subscription', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['subscriptions_create'], $requestId);
			$path = (string)($operation['path'] ?? '');
			$params = $this->stripPathParams($this->request->getParams(), $path);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('create subscription', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('create subscription', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'create subscription');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'create subscription');
		}
		return $this->buildSuccessResponse($payload, 'Subscription created.', Http::STATUS_CREATED);
	}

	public function getSubscription(string $subId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get subscription', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['subscriptions_retrieve'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['sub_id' => $subId]);
			$this->logProxyRequest('get subscription', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('get subscription', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'get subscription');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'get subscription');
		}
		return $this->buildSuccessResponse($payload, 'Subscription loaded.');
	}

	public function updateSubscription(string $subId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('update subscription', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['subscriptions_update'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['sub_id' => $subId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('update subscription', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'PATCH'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('update subscription', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'update subscription');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'update subscription');
		}
		return $this->buildSuccessResponse($payload, 'Subscription updated.');
	}

	public function deleteSubscription(string $subId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('delete subscription', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['subscriptions_destroy'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['sub_id' => $subId]);
			$this->logProxyRequest('delete subscription', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'DELETE'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('delete subscription', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'delete subscription');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'delete subscription');
		}
		return $this->buildSuccessResponse($payload, 'Subscription deleted.');
	}

	public function listAlerts(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('list alerts', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['alerts_list'], $requestId);
			$path = (string)($operation['path'] ?? '');
			$this->logProxyRequest('list alerts', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('list alerts', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'list alerts');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'list alerts');
		}
		return $this->buildSuccessResponse($payload, 'Alerts loaded.');
	}

	public function getAlert(string $alertId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get alert', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getOperation(self::OPERATION_IDS['alerts_retrieve'], $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['alert_id' => $alertId]);
			$this->logProxyRequest('get alert', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('get alert', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'get alert');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'get alert');
		}
		return $this->buildSuccessResponse($payload, 'Alert loaded.');
	}

	private function logEndpointEntry(string $action, string $requestId): void {
		$path = $this->request->getPathInfo();
		if ($path === '') {
			$path = $this->request->getRequestUri();
		}
		$this->logger->debug(
			'Weather API user alerts endpoint hit',
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
			'Weather API user alerts proxy request',
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
			'Weather API user alerts proxy response',
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
			'Weather API user alerts request failed',
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
			'Weather API user alerts request failed',
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
