<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

use Closure;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\Http\Client\LocalServerException;
use OCP\ICache;
use Psr\Log\LoggerInterface;

final class WeatherApiClient implements WeatherApiClientInterface {
	private const TOKEN_PATH = '/api/v1/integrations/token/';
	private const WHOAMI_PATH = '/api/v1/integrations/whoami/';
	private const PING_PATH = '/api/v1/integrations/nextcloud/ping/';
	private const TOKEN_CACHE_KEY = 'integration_access_token';
	private const TOKEN_TTL_FALLBACK_SECONDS = 240;
	private const TOKEN_TTL_SKEW_SECONDS = 5;

	private readonly Closure $timeProvider;
	private readonly Closure $nonceProvider;

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

		$this->logSigningContext(
			$correlationId,
			'GET',
			self::PING_PATH,
			$timestamp,
			$nonce,
			$bodyHash,
			$canonical,
		);

		$signature = hash_hmac('sha256', $canonical, $secret);

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
			throw $this->mapException($throwable);
		}

		$this->logSignedResponse($correlationId, 'GET', self::PING_PATH, $response);

		$this->ensureSuccessResponse($response);

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
	private function mintToken(string $baseUrl, bool $allowLocalAddress, string $correlationId): string {
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

		$this->logSigningContext(
			$correlationId,
			'POST',
			self::TOKEN_PATH,
			$timestamp,
			$nonce,
			$bodyHash,
			$canonical,
		);

		$signature = hash_hmac('sha256', $canonical, $secret);

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
			throw $this->mapException($throwable);
		}

		$this->logSignedResponse($correlationId, 'POST', self::TOKEN_PATH, $response);

		$this->ensureSuccessResponse($response);

		$payload = $this->decodeJson($response->getBody());
		if (!isset($payload['access']) || !is_string($payload['access'])) {
			throw new WeatherApiException('backend_error', 'Token response is malformed.');
		}

		$token = $payload['access'];
		$expiresIn = null;
		if (isset($payload['expires_in']) && is_numeric($payload['expires_in'])) {
			$expiresIn = (int)$payload['expires_in'];
		}
		$this->cache->set($this->getTokenCacheKey(), $token, $this->resolveTokenTtl($expiresIn));

		return $token;
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
			throw $this->mapException($throwable);
		}

		$this->ensureSuccessResponse($response);

		return $this->decodeJson($response->getBody());
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

	private function ensureSuccessResponse(IResponse $response): void {
		$status = $response->getStatusCode();
		if ($status >= 200 && $status < 300) {
			return;
		}

		$code = $this->determineErrorCode($status);
		$reason = null;
		if ($code === 'backend_unavailable') {
			$reason = 'http_status_' . $status;
		}

		$details = $this->extractSafeErrorDetails($response, $status);
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
	private function extractSafeErrorDetails(IResponse $response, int $status): array {
		$details = [
			'httpStatus' => $status,
		];

		$body = trim($this->bodyToString($response->getBody()));
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
	): void {
		$canonicalHash = substr(hash('sha256', $canonical), 0, 12);

		$this->logger->debug('Weather API HMAC signing context', [
			'requestId' => $correlationId,
			'method' => strtoupper($method),
			'path' => $path,
			'timestamp' => $timestamp,
			'nonce' => $nonce,
			'body_sha256' => $bodyHash,
			'canonical_sha256' => $canonicalHash,
		]);
	}

	private function logSignedResponse(
		string $correlationId,
		string $method,
		string $path,
		IResponse $response,
	): void {
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

	private function resolveTokenTtl(?int $expiresIn): int {
		if ($expiresIn !== null && $expiresIn > 0) {
			$ttl = $expiresIn - self::TOKEN_TTL_SKEW_SECONDS;
			return $ttl > 0 ? $ttl : 1;
		}

		return self::TOKEN_TTL_FALLBACK_SECONDS;
	}
}
