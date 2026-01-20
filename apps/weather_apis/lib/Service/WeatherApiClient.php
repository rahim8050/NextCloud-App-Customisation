<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

use Closure;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\Http\Client\LocalServerException;
use OCP\ICache;
use Psr\Log\LoggerInterface;

final class WeatherApiClient implements WeatherApiClientInterface {
	private const TOKEN_PATH = '/api/v1/integrations/token/';
	private const WHOAMI_PATH = '/api/v1/integrations/whoami/';
	private const PING_PATH = '/api/v1/integrations/nextcloud/ping/';
	private const STATUS_PATH = '/api/v1/integrations/nextcloud/status/';
	private const PREVIEW_PATH = '/api/v1/integrations/nextcloud/preview.png';
	private const TOKEN_CACHE_KEY = 'integration_access_token';
	private const TOKEN_TTL_FALLBACK_SECONDS = 240;
	private const TOKEN_TTL_SKEW_SECONDS = 5;

	private readonly Closure $timeProvider;
	private readonly Closure $nonceProvider;
	private readonly bool $hmacDebugLogging;

	public function __construct(
		private readonly IClientService $clientService,
		private readonly AppConfig $appConfig,
		private readonly IntegrationConfig $integrationConfig,
		private readonly UrlValidator $urlValidator,
		private readonly TokenSigner $tokenSigner,
		private readonly ICache $cache,
		private readonly LoggerInterface $logger,
		?callable $timeProvider = null,
		?callable $nonceProvider = null,
	) {
		$this->timeProvider = Closure::fromCallable($timeProvider ?? fn (): int => time());
		$this->nonceProvider = Closure::fromCallable($nonceProvider ?? fn (): string => bin2hex(random_bytes(16)));
		$this->hmacDebugLogging = $this->appConfig->isHmacDebugLoggingEnabled();
	}

	/**
	 * @throws WeatherApiException
	 */
	public function whoami(string $correlationId): array {
		$context = $this->resolveValidatedBaseUrlContext();
		$token = $this->getCachedToken($context['baseUrl'], $context['allowLocalAddress'], $correlationId);

		try {
			return $this->fetchWhoami($context['baseUrl'], $token, $context['allowLocalAddress'], $correlationId);
		} catch (WeatherApiException $exception) {
			if ($exception->getErrorCode() === 'unauthorized') {
				$this->clearCachedToken();
				$token = $this->mintToken($context['baseUrl'], $context['allowLocalAddress'], $correlationId);

				return $this->fetchWhoami($context['baseUrl'], $token, $context['allowLocalAddress'], $correlationId);
			}

			throw $exception;
		}
	}

	/**
	 * @return array<array-key, mixed>
	 * @throws WeatherApiException
	 */
	public function nextcloudStatus(string $correlationId): array {
		return $this->withTokenRetry(
			$correlationId,
			fn (string $baseUrl, string $token, bool $allowLocalAddress, string $requestId): array
				=> $this->fetchStatus($baseUrl, $token, $allowLocalAddress, $requestId),
		);
	}

	/**
	 * @throws WeatherApiException
	 */
	public function nextcloudPreviewPng(string $correlationId): string {
		return $this->withTokenRetry(
			$correlationId,
			fn (string $baseUrl, string $token, bool $allowLocalAddress, string $requestId): string
				=> $this->fetchPreviewPng($baseUrl, $token, $allowLocalAddress, $requestId),
		);
	}

	/**
	 * @throws WeatherApiException
	 */
	public function ping(string $correlationId): void {
		$context = $this->resolveValidatedBaseUrlContext();
		$client = $this->clientService->newClient();
		$nonce = (string)($this->nonceProvider)();
		$timestamp = (string)($this->timeProvider)();
		$body = '';
		$bodyHash = $this->tokenSigner->bodySha256Hex('GET', $body);

		try {
			$clientId = $this->integrationConfig->getClientId();
			$secret = $this->integrationConfig->getSecretBytes();
		} catch (IntegrationConfigException $exception) {
			throw new WeatherApiException($exception->getErrorCode(), $exception->getMessage(), $exception);
		}

		$canonical = $this->tokenSigner->buildCanonicalString(
			'GET',
			self::PING_PATH,
			'',
			$timestamp,
			$nonce,
			$bodyHash,
		);

		$signature = hash_hmac('sha256', $canonical, $secret);
		$this->logSigningContext(
			$correlationId,
			'GET',
			self::PING_PATH,
			$timestamp,
			$nonce,
			$bodyHash,
			$canonical,
			$signature,
			$secret,
		);

		$options = $this->buildBaseOptions($correlationId, $context['allowLocalAddress']);
		$options['headers'] = array_merge($options['headers'], [
			'Accept' => 'application/json',

			// DRF ping expects the X-NC-* Nextcloud HMAC header names
			'X-NC-CLIENT-ID' => $clientId,
			'X-NC-TIMESTAMP' => $timestamp,
			'X-NC-NONCE' => $nonce,
			'X-NC-SIGNATURE' => $signature,

			// Optional alias header (harmless; DRF may accept it)
			'X-Client-Id' => $clientId,
		]);

		$url = $this->buildEndpoint($context['baseUrl'], self::PING_PATH);

		try {
			$response = $client->get($url, $options);
		} catch (\Throwable $throwable) {
			$this->logTransportFailure($correlationId, 'GET', $url, $throwable);
			throw $this->mapException($throwable);
		}

		$body = null;
		$status = $response->getStatusCode();
		if ($status < 200 || $status >= 300) {
			$body = $this->bodyToString($response->getBody());
			$this->logHttpFailure($correlationId, 'GET', $url, $response, $body);
		}

		$this->logSignedResponse($correlationId, 'GET', self::PING_PATH, $response);

		$this->ensureSuccessResponse($response, $body);

		$payload = $this->decodeJson($response->getBody());
		$status = $payload['status'] ?? null;
		$data = $payload['data'] ?? null;
		if ((int)$status !== 0 || !is_array($data) || ($data['ok'] ?? null) !== true) {
			throw new WeatherApiException('backend_error', 'Ping response is malformed.');
		}
	}

	/**
	 * @throws WeatherApiException
	 */
	public function testConnection(string $correlationId): int {
		$context = $this->resolveValidatedBaseUrlContext();
		$payload = $this->mintTokenPayload($context['baseUrl'], $context['allowLocalAddress'], $correlationId);
		$this->cacheToken($payload['access'], $payload['expiresIn']);

		if ($payload['expiresIn'] === null) {
			throw new WeatherApiException('backend_error', 'Token response missing expires_in.');
		}

		return $payload['expiresIn'];
	}

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
	): array {
		$requestId = $this->resolveCorrelationId($correlationId);
		$httpMethod = $this->normalizeMethod($method);

		return $this->withTokenRetry(
			$requestId,
			function (string $baseUrl, string $token, bool $allowLocalAddress, string $resolvedId) use ($httpMethod, $path, $queryParams, $body): array {
				$client = $this->clientService->newClient();
				$options = $this->buildBaseOptions($resolvedId, $allowLocalAddress);

				$options['headers'] = array_merge($options['headers'], [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . $token,
				]);

				if ($body !== null) {
					$options['headers']['Content-Type'] = 'application/json';
					$options['body'] = $this->encodeJsonBody($body);
				}

				if ($queryParams !== []) {
					$options['query'] = $queryParams;
				}

				$url = $this->buildEndpoint($baseUrl, $path);

				try {
					$response = $this->sendRequest($client, $httpMethod, $url, $options);
				} catch (\Throwable $throwable) {
					$this->logTransportFailure($resolvedId, $httpMethod, $url, $throwable);
					throw $this->mapException($throwable);
				}

				$body = null;
				$status = $response->getStatusCode();
				if ($status < 200 || $status >= 300) {
					$body = $this->bodyToString($response->getBody());
					$this->logHttpFailure($resolvedId, $httpMethod, $url, $response, $body);
				}

				$this->ensureSuccessResponse($response, $body);

				$payload = trim($this->bodyToString($response->getBody()));
				if ($payload === '') {
					return [];
				}

				return $this->decodeJson($payload);
			},
		);
	}

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
	): array {
		$requestId = $this->resolveCorrelationId($correlationId);
		$httpMethod = $this->normalizeMethod($method);

		return $this->withTokenRetry(
			$requestId,
			function (string $baseUrl, string $token, bool $allowLocalAddress, string $resolvedId) use ($httpMethod, $path, $queryParams): array {
				$client = $this->clientService->newClient();
				$options = $this->buildBaseOptions($resolvedId, $allowLocalAddress);

				$options['headers'] = array_merge($options['headers'], [
					'Accept' => 'image/png',
					'Authorization' => 'Bearer ' . $token,
				]);

				if ($queryParams !== []) {
					$options['query'] = $queryParams;
				}

				$url = $this->buildEndpoint($baseUrl, $path);

				try {
					$response = $this->sendRequest($client, $httpMethod, $url, $options);
				} catch (\Throwable $throwable) {
					$this->logTransportFailure($resolvedId, $httpMethod, $url, $throwable);
					throw $this->mapException($throwable);
				}

				$body = null;
				$status = $response->getStatusCode();
				if ($status < 200 || $status >= 300) {
					$body = $this->bodyToString($response->getBody());
					$this->logHttpFailure($resolvedId, $httpMethod, $url, $response, $body);
				}

				$this->ensureSuccessResponse($response, $body);

				$contentType = strtolower($response->getHeader('Content-Type'));
				if ($contentType !== '' && !str_contains($contentType, 'image/png')) {
					throw new WeatherApiException('backend_error', 'Binary response is not PNG.');
				}

				$body = $this->bodyToString($response->getBody());
				if ($body === '') {
					throw new WeatherApiException('backend_error', 'Binary response is empty.');
				}

				return [
					'body' => $body,
					'contentType' => $contentType !== '' ? $contentType : 'image/png',
					'statusCode' => $response->getStatusCode(),
				];
			},
		);
	}

	/**
	 * @return array<array-key, mixed>
	 * @throws WeatherApiException
	 */
	public function fetchSchema(string $correlationId): array {
		$context = $this->resolveValidatedBaseUrlContext();
		$client = $this->clientService->newClient();

		$options = $this->buildBaseOptions($correlationId, $context['allowLocalAddress']);
		$options['headers'] = array_merge($options['headers'], [
			'Accept' => 'application/json',
		]);

		$url = $this->buildEndpoint($context['baseUrl'], '/api/schema/?format=json');

		try {
			$response = $client->get($url, $options);
		} catch (\Throwable $throwable) {
			$this->logTransportFailure($correlationId, 'GET', $url, $throwable);
			throw $this->mapException($throwable);
		}

		$body = null;
		$status = $response->getStatusCode();
		if ($status < 200 || $status >= 300) {
			$body = $this->bodyToString($response->getBody());
			$this->logHttpFailure($correlationId, 'GET', $url, $response, $body);
		}

		$this->ensureSuccessResponse($response, $body);

		return $this->decodeJson($response->getBody());
	}

	/**
	 * @throws WeatherApiException
	 */
	private function mintToken(string $baseUrl, bool $allowLocalAddress, string $correlationId): string {
		$payload = $this->mintTokenPayload($baseUrl, $allowLocalAddress, $correlationId);
		$this->cacheToken($payload['access'], $payload['expiresIn']);

		return $payload['access'];
	}

	/**
	 * @return array{access: string, expiresIn: int}
	 * @throws WeatherApiException
	 */
	private function mintTokenPayload(string $baseUrl, bool $allowLocalAddress, string $correlationId): array {
		$client = $this->clientService->newClient();
		$nonce = (string)($this->nonceProvider)();
		$timestamp = (string)($this->timeProvider)();
		$body = '';
		$bodyHash = $this->tokenSigner->bodySha256Hex('POST', $body);

		$canonical = $this->tokenSigner->buildCanonicalString(
			'POST',
			self::TOKEN_PATH,
			'',
			$timestamp,
			$nonce,
			$bodyHash,
		);

		try {
			$clientId = $this->integrationConfig->getClientId();
			$secret = $this->integrationConfig->getSecretBytes();
		} catch (IntegrationConfigException $exception) {
			throw new WeatherApiException($exception->getErrorCode(), $exception->getMessage(), $exception);
		}

		$signature = hash_hmac('sha256', $canonical, $secret);
		$this->logSigningContext(
			$correlationId,
			'POST',
			self::TOKEN_PATH,
			$timestamp,
			$nonce,
			$bodyHash,
			$canonical,
			$signature,
			$secret,
		);

		$options = $this->buildBaseOptions($correlationId, $allowLocalAddress);
		$options['headers'] = array_merge($options['headers'], [
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',

			// DRF token endpoint expects API key + integrations HMAC header names
			'X-API-Key' => $this->appConfig->getApiKey(),
			'X-Client-Id' => $clientId,
			'X-Timestamp' => $timestamp,
			'X-Nonce' => $nonce,
			'X-Signature' => $signature,
		]);
		$options['body'] = $body;

		$url = $this->buildEndpoint($baseUrl, self::TOKEN_PATH);

		try {
			$response = $client->post($url, $options);
		} catch (\Throwable $throwable) {
			$this->logTransportFailure($correlationId, 'POST', $url, $throwable);
			throw $this->mapException($throwable);
		}

		$body = null;
		$status = $response->getStatusCode();
		if ($status < 200 || $status >= 300) {
			$body = $this->bodyToString($response->getBody());
			$this->logHttpFailure($correlationId, 'POST', $url, $response, $body);
		}

		$this->logSignedResponse($correlationId, 'POST', self::TOKEN_PATH, $response);

		$this->ensureSuccessResponse($response, $body);

		$payload = $this->decodeJson($response->getBody());
		$data = $this->unwrapTokenPayload($payload);

		$access = $data['access'] ?? null;
		if (!is_string($access) || trim($access) === '') {
			throw new WeatherApiException('backend_error', 'Token response is malformed.');
		}

		$expiresRaw = $data['expires_in'] ?? null;
		if (!is_numeric($expiresRaw)) {
			throw new WeatherApiException('backend_error', 'Token response missing expires_in.');
		}

		return [
			'access' => $access,
			'expiresIn' => (int)$expiresRaw,
		];
	}

	/**
	 * @param array<array-key, mixed> $payload
	 * @return array<array-key, mixed>
	 * @throws WeatherApiException
	 */
	private function unwrapTokenPayload(array $payload): array {
		if (!array_key_exists('status', $payload)) {
			return $payload;
		}

		$status = $payload['status'];
		if (is_numeric($status) && (int)$status !== 0) {
			$message = is_string($payload['message'] ?? null)
				? $this->clampString($payload['message'], 200)
				: 'Token request failed.';
			$errors = $payload['errors'] ?? null;
			$code = 'backend_error';
			$reason = null;
			$details = [];

			if (is_array($errors)) {
				if (isset($errors['code']) && is_string($errors['code']) && $errors['code'] !== '') {
					$code = $this->clampString($errors['code'], 64);
				}
				if (isset($errors['reason']) && is_string($errors['reason']) && $errors['reason'] !== '') {
					$reason = $this->clampString($errors['reason'], 200);
				}
				$sanitized = $this->sanitizeTokenErrors($errors);
				if ($sanitized !== []) {
					$details['errors'] = $sanitized;
				}
			}

			throw new WeatherApiException($code, $message, null, $reason, $details);
		}

		$data = $payload['data'] ?? null;
		if (is_array($data)) {
			return $data;
		}

		return $payload;
	}

	/**
	 * @param array<array-key, mixed> $errors
	 * @return array<string, string>
	 */
	private function sanitizeTokenErrors(array $errors): array {
		$sanitized = [];
		if (isset($errors['code']) && is_string($errors['code'])) {
			$sanitized['code'] = $this->clampString($errors['code'], 64);
		}
		if (isset($errors['reason']) && is_string($errors['reason'])) {
			$sanitized['reason'] = $this->clampString($errors['reason'], 200);
		}
		if (isset($errors['detail']) && is_string($errors['detail'])) {
			$sanitized['detail'] = $this->clampString($errors['detail'], 200);
		}

		return $sanitized;
	}

	/**
	 * @throws WeatherApiException
	 */
	private function fetchWhoami(string $baseUrl, string $token, bool $allowLocalAddress, string $correlationId): array {
		$client = $this->clientService->newClient();

		$options = $this->buildBaseOptions($correlationId, $allowLocalAddress);
		$options['headers'] = array_merge($options['headers'], [
			'Authorization' => 'Bearer ' . $token,
		]);

		$url = $this->buildEndpoint($baseUrl, self::WHOAMI_PATH);

		try {
			$response = $client->get($url, $options);
		} catch (\Throwable $throwable) {
			$this->logTransportFailure($correlationId, 'GET', $url, $throwable);
			throw $this->mapException($throwable);
		}

		$body = null;
		$status = $response->getStatusCode();
		if ($status < 200 || $status >= 300) {
			$body = $this->bodyToString($response->getBody());
			$this->logHttpFailure($correlationId, 'GET', $url, $response, $body);
		}

		$this->ensureSuccessResponse($response, $body);

		return $this->decodeJson($response->getBody());
	}

	/**
	 * @template T
	 * @param callable(string, string, bool, string): T $callback
	 * @return T
	 * @throws WeatherApiException
	 */
	private function withTokenRetry(string $correlationId, callable $callback): mixed {
		$context = $this->resolveValidatedBaseUrlContext();
		$token = $this->getCachedToken($context['baseUrl'], $context['allowLocalAddress'], $correlationId);

		try {
			return $callback($context['baseUrl'], $token, $context['allowLocalAddress'], $correlationId);
		} catch (WeatherApiException $exception) {
			if ($exception->getErrorCode() === 'unauthorized') {
				$this->clearCachedToken();
				$token = $this->mintToken($context['baseUrl'], $context['allowLocalAddress'], $correlationId);

				return $callback($context['baseUrl'], $token, $context['allowLocalAddress'], $correlationId);
			}

			throw $exception;
		}
	}

	/**
	 * @return array<array-key, mixed>
	 * @throws WeatherApiException
	 */
	private function fetchStatus(string $baseUrl, string $token, bool $allowLocalAddress, string $correlationId): array {
		$client = $this->clientService->newClient();

		$options = $this->buildBaseOptions($correlationId, $allowLocalAddress);
		$options['headers'] = array_merge($options['headers'], [
			'Accept' => 'application/json',
			'Authorization' => 'Bearer ' . $token,
		]);

		$url = $this->buildEndpoint($baseUrl, self::STATUS_PATH);

		try {
			$response = $client->get($url, $options);
		} catch (\Throwable $throwable) {
			$this->logTransportFailure($correlationId, 'GET', $url, $throwable);
			throw $this->mapException($throwable);
		}

		$body = null;
		$status = $response->getStatusCode();
		if ($status < 200 || $status >= 300) {
			$body = $this->bodyToString($response->getBody());
			$this->logHttpFailure($correlationId, 'GET', $url, $response, $body);
		}

		$this->ensureSuccessResponse($response, $body);

		$payload = $this->decodeJson($response->getBody());
		$data = $payload['data'] ?? $payload;
		if (!is_array($data) || ($data['ok'] ?? null) !== true) {
			throw new WeatherApiException('backend_error', 'Status response is malformed.');
		}

		return $data;
	}

	/**
	 * @throws WeatherApiException
	 */
	private function fetchPreviewPng(string $baseUrl, string $token, bool $allowLocalAddress, string $correlationId): string {
		$client = $this->clientService->newClient();

		$options = $this->buildBaseOptions($correlationId, $allowLocalAddress);
		$options['headers'] = array_merge($options['headers'], [
			'Accept' => 'image/png',
			'Authorization' => 'Bearer ' . $token,
		]);

		$url = $this->buildEndpoint($baseUrl, self::PREVIEW_PATH);

		try {
			$response = $client->get($url, $options);
		} catch (\Throwable $throwable) {
			$this->logTransportFailure($correlationId, 'GET', $url, $throwable);
			throw $this->mapException($throwable);
		}

		$body = null;
		$status = $response->getStatusCode();
		if ($status < 200 || $status >= 300) {
			$body = $this->bodyToString($response->getBody());
			$this->logHttpFailure($correlationId, 'GET', $url, $response, $body);
		}

		$this->ensureSuccessResponse($response, $body);

		$contentType = strtolower($response->getHeader('Content-Type'));
		if ($contentType !== '' && !str_contains($contentType, 'image/png')) {
			throw new WeatherApiException('backend_error', 'Preview response is not PNG.');
		}

		$body = $this->bodyToString($response->getBody());
		if ($body === '') {
			throw new WeatherApiException('backend_error', 'Preview response is empty.');
		}

		return $body;
	}

	/**
	 * @throws WeatherApiException
	 */
	private function resolveValidatedBaseUrlContext(): array {
		$baseUrl = $this->appConfig->getBaseUrl();
		$baseUrlPresent = $baseUrl !== '';
		$devAllowHttp = $this->appConfig->isDevAllowInsecureLocalHttp();
		$allowlistHosts = $this->appConfig->getAllowlistHosts();
		$allowLocalRemoteServers = $this->appConfig->isAllowLocalRemoteServers();

		$parts = $baseUrlPresent ? parse_url($baseUrl) : false;
		$parsed = is_array($parts);
		$scheme = $parsed ? strtolower($parts['scheme'] ?? '') : '';
		$host = $parsed ? strtolower($parts['host'] ?? '') : '';
		$port = $parsed ? ($parts['port'] ?? null) : null;

		$allowlist = $this->urlValidator->parseAllowlistHosts($allowlistHosts);
		$hostAllowlisted = $host !== '' && in_array($host, $allowlist, true);
		$devOverrideActive = $devAllowHttp && $hostAllowlisted;
		$localAccessAllowed = $devOverrideActive || $allowLocalRemoteServers;
		$localAccessSource = $devOverrideActive ? 'dev_allowlist'
			: ($allowLocalRemoteServers ? 'system_config' : 'none');

		$this->logger->debug('Weather API base URL validation context', LogSanitizer::sanitizeContext([
			'baseUrlPresent' => $baseUrlPresent,
			'baseUrlParsed' => $parsed,
			'scheme' => $scheme,
			'host' => $host,
			'port' => $port,
			'devAllowHttp' => $devAllowHttp,
			'allowLocalRemoteServers' => $allowLocalRemoteServers,
			'allowlistCount' => count($allowlist),
			'hostAllowlisted' => $hostAllowlisted,
			'devOverrideActive' => $devOverrideActive,
			'localAccessAllowed' => $localAccessAllowed,
			'localAccessSource' => $localAccessSource,
		]));

		if (!$baseUrlPresent) {
			throw new WeatherApiException('invalid_argument', 'Base URL is not configured.');
		}

		try {
			$this->urlValidator->validate(
				$baseUrl,
				$devAllowHttp,
				$allowlistHosts,
				$allowLocalRemoteServers,
			);
		} catch (\InvalidArgumentException $exception) {
			throw new WeatherApiException('invalid_argument', 'Configured base URL is invalid.', $exception);
		}

		return [
			'baseUrl' => rtrim($baseUrl, '/'),
			'allowLocalAddress' => $localAccessAllowed,
		];
	}

	private function buildBaseOptions(string $correlationId, bool $allowLocalAddress): array {
		$timeout = $this->appConfig->getTimeoutSeconds();

		$options = [
			'timeout' => $timeout,
			'connect_timeout' => min(10, $timeout),
			'verify' => true,
			'allow_redirects' => ['max' => 0],
			'headers' => [
				'X-Request-Id' => $correlationId,
			],
		];

		if ($allowLocalAddress) {
			$options['nextcloud'] = [
				'allow_local_address' => true,
			];
		}

		return $options;
	}

	private function buildEndpoint(string $baseUrl, string $path): string {
		return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
	}

	private function resolveCorrelationId(?string $correlationId): string {
		if ($correlationId !== null && $correlationId !== '') {
			return $correlationId;
		}

		$bytes = random_bytes(16);
		$bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
		$bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
	}

	/**
	 * @param array<string, mixed> $body
	 */
	private function encodeJsonBody(array $body): string {
		try {
			return json_encode($body, JSON_THROW_ON_ERROR);
		} catch (\JsonException $exception) {
			throw new WeatherApiException('invalid_argument', 'Request body is not JSON serializable.', $exception);
		}
	}

	private function normalizeMethod(string $method): string {
		$normalized = strtoupper(trim($method));
		if (in_array($normalized, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
			return $normalized;
		}

		throw new WeatherApiException('invalid_argument', 'Unsupported HTTP method.');
	}

	private function sendRequest(IClient $client, string $method, string $url, array $options): IResponse {
		return match ($method) {
			'GET' => $client->get($url, $options),
			'POST' => $client->post($url, $options),
			'PUT' => $client->put($url, $options),
			'PATCH' => $client->patch($url, $options),
			'DELETE' => $client->delete($url, $options),
			default => throw new WeatherApiException('invalid_argument', 'Unsupported HTTP method.'),
		};
	}

	private function getTokenCacheKey(): string {
		return self::TOKEN_CACHE_KEY;
	}

	/**
	 * @param mixed $body
	 * @return array<array-key, mixed>
	 * @throws WeatherApiException
	 */
	private function decodeJson(mixed $body): array {
		$payload = $this->bodyToString($body);

		try {
			$decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException $exception) {
			throw new WeatherApiException('backend_error', 'Failed to parse backend response.', $exception);
		}

		if (!is_array($decoded)) {
			throw new WeatherApiException('backend_error', 'Backend response is not a JSON object/array.');
		}

		/** @var array<array-key, mixed> $decoded */
		return $decoded;
	}

	private function ensureSuccessResponse(IResponse $response, ?string $body = null): void {
		$status = $response->getStatusCode();
		if ($status >= 200 && $status < 300) {
			return;
		}

		$code = $this->determineErrorCode($status);
		$reason = null;
		if ($code === 'backend_unavailable') {
			$reason = 'http_status_' . $status;
		}

		$details = $this->extractSafeErrorDetails($response, $status, $body);
		$message = $details['message'] ?? '';
		$finalMessage = is_string($message) && $message !== ''
			? $message
			: 'Backend returned HTTP ' . $status . '.';

		throw new WeatherApiException($code, $finalMessage, null, $reason, $details);
	}

	private function determineErrorCode(int $status): string {
		return match (true) {
			$status === 401 => 'unauthorized',
			$status === 403 => 'forbidden',
			$status === 408 => 'backend_timeout',
			$status >= 500 => 'backend_unavailable',
			default => 'backend_error',
		};
	}

	/**
	 * @return array<string, mixed>
	 */
	private function extractSafeErrorDetails(IResponse $response, int $status, ?string $body = null): array {
		$details = [
			'httpStatus' => $status,
		];

		$body = $body !== null ? trim($body) : trim($this->bodyToString($response->getBody()));
		if ($body === '') {
			return $details;
		}

		$decoded = json_decode($body, true);
		if (!is_array($decoded)) {
			return $details;
		}

		if (isset($decoded['message']) && is_string($decoded['message'])) {
			$details['message'] = $this->clampString($decoded['message'], 200);
		}

		if (isset($decoded['errors']) && is_array($decoded['errors'])) {
			$errors = [];
			if (isset($decoded['errors']['code']) && is_string($decoded['errors']['code'])) {
				$errors['code'] = $this->clampString($decoded['errors']['code'], 64);
			}
			if (isset($decoded['errors']['reason']) && is_string($decoded['errors']['reason'])) {
				$errors['reason'] = $this->clampString($decoded['errors']['reason'], 200);
			}
			if ($errors !== []) {
				$details['errors'] = $errors;
			}
		}

		return $details;
	}

	private function mapException(\Throwable $throwable): WeatherApiException {
		if ($throwable instanceof WeatherApiException) {
			return $throwable;
		}

		$message = strtolower($throwable->getMessage());

		if (str_contains($message, 'timeout')) {
			return new WeatherApiException('backend_timeout', 'Backend request failed.', $throwable, 'timeout');
		}

		return new WeatherApiException(
			'backend_unavailable',
			'Backend request failed.',
			$throwable,
			$this->mapUnavailableReason($throwable, $message),
		);
	}

	private function mapUnavailableReason(\Throwable $throwable, string $message): string {
		if ($throwable instanceof LocalServerException) {
			return 'local_address_blocked';
		}

		if (str_contains($message, 'could not resolve') || str_contains($message, 'dns')) {
			return 'dns_resolution_failed';
		}

		if (str_contains($message, 'connection refused')) {
			return 'connection_refused';
		}

		if (str_contains($message, 'ssl') || str_contains($message, 'certificate')) {
			return 'tls_failed';
		}

		return 'request_failed';
	}

	private function logHttpFailure(
		string $requestId,
		string $method,
		string $url,
		IResponse $response,
		string $body,
	): void {
		$context = [
			'requestId' => $requestId,
			'method' => $method,
			'url' => $url,
			'httpStatus' => $response->getStatusCode(),
		];

		$snippet = $this->clampString($body, 200);
		if ($snippet !== '') {
			$context['responseSnippet'] = $snippet;
		}

		$this->logger->warning(
			'Weather API HTTP request failed',
			LogSanitizer::sanitizeContext($context),
		);
	}

	private function logTransportFailure(string $requestId, string $method, string $url, \Throwable $throwable): void {
		$context = [
			'requestId' => $requestId,
			'method' => $method,
			'url' => $url,
			'exception' => $throwable::class,
			'message' => $this->clampString($throwable->getMessage(), 200),
		];

		$httpStatus = $this->extractThrowableStatus($throwable);
		if ($httpStatus !== null) {
			$context['httpStatus'] = $httpStatus;
		}

		$this->logger->warning(
			'Weather API transport request failed',
			LogSanitizer::sanitizeContext($context),
		);
	}

	private function extractThrowableStatus(\Throwable $throwable): ?int {
		if (method_exists($throwable, 'getResponse')) {
			try {
				$response = $throwable->getResponse();
				if ($response instanceof IResponse) {
					return $response->getStatusCode();
				}
			} catch (\Throwable) {
				return null;
			}
		}

		if (method_exists($throwable, 'getStatusCode')) {
			try {
				$status = $throwable->getStatusCode();
			} catch (\Throwable) {
				return null;
			}

			if (is_int($status)) {
				return $status;
			}
			if (is_string($status) && is_numeric($status)) {
				return (int)$status;
			}
		}

		return null;
	}

	private function bodyToString(mixed $body): string {
		if (is_resource($body)) {
			$contents = stream_get_contents($body);

			return $contents === false ? '' : $contents;
		}

		return (string)$body;
	}

	private function clampString(string $value, int $limit): string {
		$trimmed = trim($value);
		if (strlen($trimmed) <= $limit) {
			return $trimmed;
		}

		return substr($trimmed, 0, $limit);
	}

	private function getCachedToken(string $baseUrl, bool $allowLocalAddress, string $correlationId): string {
		$value = $this->cache->get($this->getTokenCacheKey());
		if (is_string($value) && $value !== '') {
			return $value;
		}

		return $this->mintToken($baseUrl, $allowLocalAddress, $correlationId);
	}

	private function logSigningContext(
		string $correlationId,
		string $method,
		string $path,
		string $timestamp,
		string $nonce,
		string $bodyHash,
		string $canonical,
		string $signature,
		string $secret,
	): void {
		if (!$this->hmacDebugLogging) {
			return;
		}

		$canonicalHash = substr(hash('sha256', $canonical), 0, 16);
		$secretFingerprint = substr(hash('sha256', $secret), 0, 16);

		$this->logger->debug('Weather API HMAC signing context', [
			'requestId' => $correlationId,
			'method' => strtoupper($method),
			'path' => $path,
			'timestamp' => $timestamp,
			'nonce' => $nonce,
			'body_sha256' => $bodyHash,
			'canonical_sha256' => $canonicalHash,
			'secret_sha256' => $secretFingerprint,
			'signature' => $signature,
		]);
	}

	private function logSignedResponse(
		string $correlationId,
		string $method,
		string $path,
		IResponse $response,
	): void {
		if (!$this->hmacDebugLogging) {
			return;
		}

		$this->logger->debug('Weather API signed response received', [
			'requestId' => $correlationId,
			'method' => strtoupper($method),
			'path' => $path,
			'status' => $response->getStatusCode(),
		]);
	}

	private function clearCachedToken(): void {
		$this->cache->remove($this->getTokenCacheKey());
	}

	private function cacheToken(string $token, ?int $expiresIn): void {
		$this->cache->set(
			$this->getTokenCacheKey(),
			$token,
			$this->resolveTokenTtl($expiresIn),
		);
	}

	private function resolveTokenTtl(?int $expiresIn): int {
		if ($expiresIn !== null && $expiresIn > 0) {
			$ttl = $expiresIn - self::TOKEN_TTL_SKEW_SECONDS;
			return $ttl > 0 ? $ttl : 1;
		}

		return self::TOKEN_TTL_FALLBACK_SECONDS;
	}
}
