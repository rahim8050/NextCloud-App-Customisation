<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Service;

use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Service\IntegrationConfig;
use OCA\WeatherApis\Service\TokenSigner;
use OCA\WeatherApis\Service\UrlValidator;
use OCA\WeatherApis\Service\WeatherApiClient;
use OCA\WeatherApis\Service\WeatherApiException;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\ICache;
use OCP\IConfig;
use OCP\Security\ICrypto;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class WeatherApiClientTest extends TestCase {
	public function testRequestOptionsEnforceTimeoutsAndRedirects(): void {
		$tokenResponse = $this->createResponse(200, '{"access":"token","expires_in":300}');
		$whoamiResponse = $this->createResponse(200, '{"user":"ok"}');

		$tokenClient = $this->createMock(IClient::class);
		$tokenClient
			->expects($this->once())
			->method('post')
			->with(
				$this->stringContains('/api/v1/integrations/token/'),
				$this->callback(fn (array $options): bool => $this->hasCorrectOptions($options, 'fixed-request')),
			)
			->willReturn($tokenResponse);

		$whoamiClient = $this->createMock(IClient::class);
		$whoamiClient
			->expects($this->once())
			->method('get')
			->with(
				$this->stringContains('/api/v1/integrations/whoami/'),
				$this->callback(fn (array $options): bool => $this->hasCorrectOptions($options, 'fixed-request', 'Bearer token')),
			)
			->willReturn($whoamiResponse);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->exactly(2))
			->method('newClient')
			->willReturnOnConsecutiveCalls($tokenClient, $whoamiClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn(null);
		$cache->expects($this->once())
			->method('set')
			->with('integration_access_token', 'token', 295)
			->willReturn(true);

		$client = $this->createClient($clientService, $cache);
		$client->whoami('fixed-request');
	}

	public function testPingUsesHmacHeadersAndParsesEnvelope(): void {
		$pingResponse = $this->createResponse(
			200,
			'{"status":0,"message":"OK","data":{"ok":true,"client_id":"client-id"}}',
		);

		$signer = new TokenSigner();
		$bodyHash = $signer->bodySha256Hex('GET', '');
		$canonical = $signer->buildCanonicalString(
			'GET',
			'/api/v1/integrations/nextcloud/ping/',
			'',
			'123',
			'nonce',
			$bodyHash,
		);
		$expectedSignature = hash_hmac('sha256', $canonical, 'plain-secret');

		$pingClient = $this->createMock(IClient::class);
		$pingClient
			->expects($this->once())
			->method('get')
			->with(
				$this->stringContains('/api/v1/integrations/nextcloud/ping/'),
				$this->callback(function (array $options) use ($expectedSignature): bool {
					return $this->hasCorrectOptions($options, 'ping-request')
						&& $options['headers']['X-NC-CLIENT-ID'] === 'client-id'
						&& $options['headers']['X-NC-TIMESTAMP'] === '123'
						&& $options['headers']['X-NC-NONCE'] === 'nonce'
						&& $options['headers']['X-NC-SIGNATURE'] === $expectedSignature
						&& $options['headers']['X-Client-Id'] === 'client-id'
						&& !array_key_exists('X-API-Key', $options['headers']);
				}),
			)
			->willReturn($pingResponse);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->once())
			->method('newClient')
			->willReturn($pingClient);

		$cache = $this->createMock(ICache::class);

		$client = $this->createClient($clientService, $cache);
		$client->ping('ping-request');
	}

	public function testMintTokenUsesIntegrationHeaders(): void {
		$tokenResponse = $this->createResponse(200, '{"access":"token","expires_in":300}');
		$whoamiResponse = $this->createResponse(200, '{"user":"ok"}');

		$signer = new TokenSigner();
		$bodyHash = $signer->bodySha256Hex('POST', '');
		$canonical = $signer->buildCanonicalString(
			'POST',
			'/api/v1/integrations/token/',
			'',
			'123',
			'nonce',
			$bodyHash,
		);
		$expectedSignature = hash_hmac('sha256', $canonical, 'plain-secret');

		$tokenClient = $this->createMock(IClient::class);
		$tokenClient
			->expects($this->once())
			->method('post')
			->with(
				$this->stringContains('/api/v1/integrations/token/'),
				$this->callback(function (array $options) use ($expectedSignature): bool {
					return $this->hasCorrectOptions($options, 'token-request')
						&& $options['headers']['Content-Type'] === 'application/json'
						&& $options['headers']['Accept'] === 'application/json'
						&& $options['headers']['X-API-Key'] === 'plain-api'
						&& $options['headers']['X-Client-Id'] === 'client-id'
						&& $options['headers']['X-Timestamp'] === '123'
						&& $options['headers']['X-Nonce'] === 'nonce'
						&& $options['headers']['X-Signature'] === $expectedSignature
						&& !array_key_exists('X-NC-CLIENT-ID', $options['headers']);
				}),
			)
			->willReturn($tokenResponse);

		$whoamiClient = $this->createMock(IClient::class);
		$whoamiClient
			->expects($this->once())
			->method('get')
			->willReturn($whoamiResponse);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->exactly(2))
			->method('newClient')
			->willReturnOnConsecutiveCalls($tokenClient, $whoamiClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn(null);
		$cache->method('set')->willReturn(true);

		$client = $this->createClient($clientService, $cache);
		$client->whoami('token-request');
	}

	public function testPingRejectsMalformedEnvelope(): void {
		$pingResponse = $this->createResponse(200, '{"status":0,"data":{"ok":false}}');

		$pingClient = $this->createMock(IClient::class);
		$pingClient->method('get')->willReturn($pingResponse);

		$clientService = $this->createMock(IClientService::class);
		$clientService->method('newClient')->willReturn($pingClient);

		$cache = $this->createMock(ICache::class);

		$client = $this->createClient($clientService, $cache);

		$this->expectException(WeatherApiException::class);
		try {
			$client->ping('rid');
		} catch (WeatherApiException $exception) {
			$this->assertSame('backend_error', $exception->getErrorCode());
			throw $exception;
		}
	}

	public function testTimeoutsMapToBackendTimeout(): void {
		$client = $this->buildClientWithFailingToken(fn () => new \RuntimeException('timeout reached'));

		$this->expectException(WeatherApiException::class);
		$this->expectExceptionMessage('Backend request failed.');
		try {
			$client->whoami('rid');
		} catch (WeatherApiException $exception) {
			$this->assertSame('backend_timeout', $exception->getErrorCode());
			throw $exception;
		}
	}

	public function testNon2xxResponseMapsToBackendUnavailable(): void {
		$tokenResponse = $this->createResponse(200, '{"access":"token"}');
		$whoamiResponse = $this->createResponse(502, '{"error":"oops"}');

		$client = $this->buildClientWithResponses($tokenResponse, $whoamiResponse);

		$this->expectException(WeatherApiException::class);
		try {
			$client->whoami('rid');
		} catch (WeatherApiException $exception) {
			$this->assertSame('backend_unavailable', $exception->getErrorCode());
			throw $exception;
		}
	}

	public function testInvalidJsonResponsesMapToBackendError(): void {
		$tokenResponse = $this->createResponse(200, '{"access":"token"}');
		$whoamiResponse = $this->createResponse(200, 'not json');

		$client = $this->buildClientWithResponses($tokenResponse, $whoamiResponse);

		$this->expectException(WeatherApiException::class);
		try {
			$client->whoami('rid');
		} catch (WeatherApiException $exception) {
			$this->assertSame('backend_error', $exception->getErrorCode());
			throw $exception;
		}
	}

	public function testUnauthorizedWhoamiRetriesOnceAndFails(): void {
		$tokenResponse = $this->createResponse(200, '{"access":"token","expires_in":300}');
		$whoamiUnauthorized = $this->createResponse(401, '{"detail":"nope"}');

		$firstWhoamiClient = $this->createMock(IClient::class);
		$firstWhoamiClient->expects($this->once())
			->method('get')
			->willReturn($whoamiUnauthorized);

		$tokenClient = $this->createMock(IClient::class);
		$tokenClient->expects($this->once())
			->method('post')
			->willReturn($tokenResponse);

		$secondWhoamiClient = $this->createMock(IClient::class);
		$secondWhoamiClient->expects($this->once())
			->method('get')
			->willReturn($whoamiUnauthorized);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->exactly(3))
			->method('newClient')
			->willReturnOnConsecutiveCalls($firstWhoamiClient, $tokenClient, $secondWhoamiClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn('cached-token');
		$cache->expects($this->once())
			->method('remove')
			->with('integration_access_token')
			->willReturn(true);
		$cache->expects($this->once())
			->method('set')
			->with('integration_access_token', 'token', 295)
			->willReturn(true);

		$client = $this->createClient($clientService, $cache);

		$this->expectException(WeatherApiException::class);
		try {
			$client->whoami('rid');
		} catch (WeatherApiException $exception) {
			$this->assertSame('unauthorized', $exception->getErrorCode());
			throw $exception;
		}
	}

	private function hasCorrectOptions(array $options, string $requestId, string $authorization = ''): bool {
		return $options['timeout'] === 15
			&& $options['connect_timeout'] === 10
			&& $options['allow_redirects'] === ['max' => 0]
			&& $options['headers']['X-Request-Id'] === $requestId
			&& ($authorization === '' || $options['headers']['Authorization'] === $authorization);
	}

	private function buildClientWithResponses(IResponse $tokenResponse, IResponse $whoamiResponse): WeatherApiClient {
		$tokenClient = $this->createMock(IClient::class);
		$tokenClient->method('post')->willReturn($tokenResponse);

		$whoamiClient = $this->createMock(IClient::class);
		$whoamiClient->method('get')->willReturn($whoamiResponse);

		$clientService = $this->createMock(IClientService::class);
		$clientService->method('newClient')->willReturnOnConsecutiveCalls($tokenClient, $whoamiClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn(null);
		$cache->method('set')->willReturn(true);

		return $this->createClient($clientService, $cache);
	}

	private function buildClientWithFailingToken(callable $throwableFactory): WeatherApiClient {
		$tokenClient = $this->createMock(IClient::class);
		$tokenClient->method('post')->willThrowException($throwableFactory());

		$clientService = $this->createMock(IClientService::class);
		$clientService->method('newClient')->willReturn($tokenClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn(null);

		return $this->createClient($clientService, $cache);
	}

	private function createClient(IClientService $clientService, ICache $cache): WeatherApiClient {
		return new WeatherApiClient(
			$clientService,
			$this->createAppConfig(),
			$this->createIntegrationConfig(),
			new UrlValidator(fn (string $host): array => ['93.184.216.34']),
			new TokenSigner(),
			$cache,
			$this->createMock(LoggerInterface::class),
			fn (): int => 123,
			fn (): string => 'nonce',
		);
	}

	private function createAppConfig(): AppConfig {
		$storage = [
			'baseUrl' => 'https://example.com',
			'timeoutSeconds' => '15',
			'devAllowHttp' => '0',
			'allowlistHosts' => '',
			'apiKey' => 'encrypted-api',
		];

		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')->willReturnCallback(
			function (string $appId, string $key, $default = '') use ($storage) {
				return $storage[$key] ?? $default;
			},
		);
		$config->method('getSystemValueBool')->willReturn(false);

		$crypto = $this->createMock(ICrypto::class);
		$crypto->method('decrypt')->willReturnCallback(fn (string $value): string => match ($value) {
			'encrypted-api' => 'plain-api',
			default => 'fallback',
		});

		return new AppConfig($config, $crypto);
	}

	private function createIntegrationConfig(): IntegrationConfig {
		$storage = [
			'INTEGRATION_HMAC_CLIENT_ID' => 'client-id',
			'INTEGRATION_HMAC_CLIENTS_JSON' => 'encrypted:{"client-id":"cGxhaW4tc2VjcmV0"}',
		];

		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')->willReturnCallback(
			function (string $appId, string $key, $default = '') use ($storage) {
				return $storage[$key] ?? $default;
			},
		);
		$config->method('getSystemValueBool')->willReturn(false);
		$config->method('getSystemValue')->willReturn(null);

		$crypto = $this->createMock(ICrypto::class);
		$crypto->method('decrypt')->willReturnCallback(
			fn (string $value): string => str_starts_with($value, 'encrypted:') ? substr($value, 10) : $value,
		);
		$crypto->method('encrypt')->willReturnCallback(
			fn (string $value): string => 'encrypted:' . $value,
		);

		return new IntegrationConfig($config, $crypto);
	}

	private function createResponse(int $status, string $body): IResponse {
		$response = $this->createMock(IResponse::class);
		$response->method('getStatusCode')->willReturn($status);
		$response->method('getBody')->willReturn($body);

		return $response;
	}
}
