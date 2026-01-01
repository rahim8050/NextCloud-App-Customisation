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
	private const TOKEN_PATH = '/api/v1/integration/token/';
	private const WHOAMI_PATH = '/api/v1/integration/whoami/';
	private const TOKEN_CACHE_KEY = 'integration_access_token';
	private const TOKEN_TTL_SECONDS = 240;

	private readonly Closure $timeProvider;
	private readonly Closure $nonceProvider;

	public function __construct(
		private readonly IClientService $clientService,
		private readonly AppConfig $appConfig,
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
				$token = $this->mintToken($context['baseUrl'], $context['allowLocalAddress'], $correlationId);

				return $this->fetchWhoami($context['baseUrl'], $token, $context['allowLocalAddress'], $correlationId);
			}

			throw $exception;
		}
	}

	/**
	 * @throws WeatherApiException
	 */
	private function mintToken(string $baseUrl, bool $allowLocalAddress, string $correlationId): string {
		$client = $this->clientService->newClient();
		$nonce = ($this->nonceProvider)();
		$timestamp = (string)($this->timeProvider)();

		$canonical = $this->tokenSigner->buildCanonicalString(
			'POST',
			self::TOKEN_PATH,
			'',
			$timestamp,
			$nonce,
			TokenSigner::EMPTY_BODY_HASH,
		);

		$signature = hash_hmac('sha256', $canonical, $this->appConfig->getHmacSecret());

		$options = $this->buildBaseOptions($correlationId, $allowLocalAddress);
		$options['headers'] = array_merge($options['headers'], [
			'Content-Type' => 'application/json',
			'X-API-Key' => $this->appConfig->getApiKey(),
			'X-Client-Id' => $this->appConfig->getClientId(),
			'X-Timestamp' => $timestamp,
			'X-Nonce' => $nonce,
			'X-Signature' => $signature,
		]);
		$options['body'] = '';

		$url = $this->buildEndpoint($baseUrl, self::TOKEN_PATH);

		try {
			$response = $client->post($url, $options);
		} catch (\Throwable $throwable) {
			throw $this->mapException($throwable);
		}

		$this->ensureSuccessResponse($response);

		$payload = $this->decodeJson($response->getBody());
		if (!isset($payload['access']) || !is_string($payload['access'])) {
			throw new WeatherApiException('backend_error', 'Token response is malformed.');
		}

		$token = $payload['access'];
		$this->cache->set($this->getTokenCacheKey(), $token, self::TOKEN_TTL_SECONDS);

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

		$this->logger->debug('Weather API base URL validation context', [
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
		]);

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

		throw new WeatherApiException($code, 'Backend returned HTTP ' . $status . '.', null, $reason);
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

	private function getCachedToken(string $baseUrl, bool $allowLocalAddress, string $correlationId): string {
		$value = $this->cache->get($this->getTokenCacheKey());
		if (is_string($value) && $value !== '') {
			return $value;
		}

		return $this->mintToken($baseUrl, $allowLocalAddress, $correlationId);
	}
}
