<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

use Closure;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
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
		$baseUrl = $this->resolveValidatedBaseUrl();
		$token = $this->getCachedToken($baseUrl, $correlationId);

		try {
			return $this->fetchWhoami($baseUrl, $token, $correlationId);
		} catch (WeatherApiException $exception) {
			if ($exception->getErrorCode() === 'unauthorized') {
				$token = $this->mintToken($baseUrl, $correlationId);

				return $this->fetchWhoami($baseUrl, $token, $correlationId);
			}

			throw $exception;
		}
	}

	/**
	 * @throws WeatherApiException
	 */
	private function mintToken(string $baseUrl, string $correlationId): string {
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

		$options = $this->buildBaseOptions($correlationId);
		$options['headers'] = array_merge($options['headers'], [
			'Content-Type' => 'application/json',
			'X-API-Key' => $this->appConfig->getApiKey(),
			'X-Client-Id' => $this->appConfig->getHmacClientId(),
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
	private function fetchWhoami(string $baseUrl, string $token, string $correlationId): array {
		$client = $this->clientService->newClient();

		$options = $this->buildBaseOptions($correlationId);
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
	private function resolveValidatedBaseUrl(): string {
		$baseUrl = $this->appConfig->getBaseUrl();
		if ($baseUrl === '') {
			throw new WeatherApiException('invalid_argument', 'Base URL is not configured.');
		}

		try {
			$this->urlValidator->validate(
				$baseUrl,
				$this->appConfig->isDevAllowInsecureLocalHttp(),
				$this->appConfig->getDevAllowlistHosts(),
			);
		} catch (\InvalidArgumentException $exception) {
			throw new WeatherApiException('invalid_argument', 'Configured base URL is invalid.', $exception);
		}

		return rtrim($baseUrl, '/');
	}

	private function buildBaseOptions(string $correlationId): array {
		$timeout = $this->appConfig->getTimeoutSeconds();

		return [
			'timeout' => $timeout,
			'connect_timeout' => min(10, $timeout),
			'verify' => true,
			'allow_redirects' => ['max' => 0],
			'headers' => [
				'X-Request-Id' => $correlationId,
			],
		];
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

		throw new WeatherApiException($code, 'Backend returned HTTP ' . $status . '.');
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

		$normalized = match (true) {
			str_contains(strtolower($throwable->getMessage()), 'timeout') => 'backend_timeout',
			default => 'backend_unavailable',
		};

		return new WeatherApiException($normalized, 'Backend request failed.', $throwable);
	}

	private function bodyToString(mixed $body): string {
		if (is_resource($body)) {
			$contents = stream_get_contents($body);

			return $contents === false ? '' : $contents;
		}

		return (string)$body;
	}

	private function getCachedToken(string $baseUrl, string $correlationId): string {
		$value = $this->cache->get($this->getTokenCacheKey());
		if (is_string($value) && $value !== '') {
			return $value;
		}

		return $this->mintToken($baseUrl, $correlationId);
	}
}
