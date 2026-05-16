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
use OCP\IMemcache;
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
		$expectedSignature = base64_encode(hash_hmac('sha256', $canonical, 'plain-secret', true));

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
		$expectedSignature = base64_encode(hash_hmac('sha256', $canonical, 'plain-secret', true));

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

	public function testTestConnectionReturnsExpiresIn(): void {
		$tokenResponse = $this->createResponse(200, '{"access":"token","expires_in":300}');

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
		$expectedSignature = base64_encode(hash_hmac('sha256', $canonical, 'plain-secret', true));

		$tokenClient = $this->createMock(IClient::class);
		$tokenClient
			->expects($this->once())
			->method('post')
			->with(
				$this->stringContains('/api/v1/integrations/token/'),
				$this->callback(function (array $options) use ($expectedSignature): bool {
					return $this->hasCorrectOptions($options, 'token-request')
						&& $options['headers']['X-API-Key'] === 'plain-api'
						&& $options['headers']['X-Client-Id'] === 'client-id'
						&& $options['headers']['X-Timestamp'] === '123'
						&& $options['headers']['X-Nonce'] === 'nonce'
						&& $options['headers']['X-Signature'] === $expectedSignature;
				}),
			)
			->willReturn($tokenResponse);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->once())
			->method('newClient')
			->willReturn($tokenClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn(null);
		$cache->expects($this->once())
			->method('set')
			->with('integration_access_token', 'token', 295)
			->willReturn(true);

		$client = $this->createClient($clientService, $cache);
		$expiresIn = $client->testConnection('token-request');
		$this->assertSame(300, $expiresIn);
	}

	public function testTestConnectionAcceptsEnvelopeTokenResponse(): void {
		$tokenResponse = $this->createResponse(
			200,
			'{"status":0,"message":"OK","data":{"access":"token","expires_in":300}}',
		);

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
		$expectedSignature = base64_encode(hash_hmac('sha256', $canonical, 'plain-secret', true));

		$tokenClient = $this->createMock(IClient::class);
		$tokenClient
			->expects($this->once())
			->method('post')
			->with(
				$this->stringContains('/api/v1/integrations/token/'),
				$this->callback(function (array $options) use ($expectedSignature): bool {
					return $this->hasCorrectOptions($options, 'token-request')
						&& $options['headers']['X-API-Key'] === 'plain-api'
						&& $options['headers']['X-Client-Id'] === 'client-id'
						&& $options['headers']['X-Timestamp'] === '123'
						&& $options['headers']['X-Nonce'] === 'nonce'
						&& $options['headers']['X-Signature'] === $expectedSignature;
				}),
			)
			->willReturn($tokenResponse);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->once())
			->method('newClient')
			->willReturn($tokenClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn(null);
		$cache->expects($this->once())
			->method('set')
			->with('integration_access_token', 'token', 295)
			->willReturn(true);

		$client = $this->createClient($clientService, $cache);
		$expiresIn = $client->testConnection('token-request');
		$this->assertSame(300, $expiresIn);
	}

	public function testTokenEnvelopeErrorMapsToException(): void {
		$tokenResponse = $this->createResponse(
			200,
			'{"status":1,"message":"Invalid signature","errors":{"code":"sig_mismatch","reason":"Invalid Nextcloud signature"}}',
		);

		$tokenClient = $this->createMock(IClient::class);
		$tokenClient->expects($this->once())
			->method('post')
			->willReturn($tokenResponse);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->once())
			->method('newClient')
			->willReturn($tokenClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn(null);

		$client = $this->createClient($clientService, $cache);

		$this->expectException(WeatherApiException::class);
		try {
			$client->testConnection('token-request');
		} catch (WeatherApiException $exception) {
			$this->assertSame('sig_mismatch', $exception->getErrorCode());
			$this->assertSame('Invalid signature', $exception->getMessage());
			$this->assertSame('Invalid Nextcloud signature', $exception->getReason());
			throw $exception;
		}
	}

	public function testNextcloudStatusUsesBearerTokenAndParsesEnvelope(): void {
		$statusResponse = $this->createResponse(
			200,
			'{"status":0,"message":"OK","data":{"ok":true,"server_time":"2025-01-01T00:00:00Z","version":"1.0.0","capabilities":{"png_preview":true}}}',
		);

		$statusClient = $this->createMock(IClient::class);
		$statusClient
			->expects($this->once())
			->method('get')
			->with(
				$this->stringContains('/api/v1/integrations/nextcloud/status/'),
				$this->callback(fn (array $options): bool => $this->hasCorrectOptions($options, 'status-request', 'Bearer cached-token')
					&& $options['headers']['Accept'] === 'application/json'),
			)
			->willReturn($statusResponse);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->once())
			->method('newClient')
			->willReturn($statusClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn('cached-token');

		$client = $this->createClient($clientService, $cache);
		$result = $client->nextcloudStatus('status-request');
		$this->assertTrue($result['ok']);
	}

	public function testNextcloudStatusWaitsForSharedTokenMintAndUsesCachedToken(): void {
		$statusResponse = $this->createResponse(
			200,
			'{"status":0,"message":"OK","data":{"ok":true,"server_time":"2025-01-01T00:00:00Z","version":"1.0.0","capabilities":{"png_preview":true}}}',
		);

		$statusClient = $this->createMock(IClient::class);
		$statusClient
			->expects($this->once())
			->method('get')
			->with(
				$this->stringContains('/api/v1/integrations/nextcloud/status/'),
				$this->callback(fn (array $options): bool => $this->hasCorrectOptions($options, 'status-request', 'Bearer shared-token')
					&& $options['headers']['Accept'] === 'application/json'),
			)
			->willReturn($statusResponse);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->once())
			->method('newClient')
			->willReturn($statusClient);

		$cache = $this->createMock(IMemcache::class);
		$cache->expects($this->once())
			->method('add')
			->with('integration_access_token:mint_lock', 'nonce', 15)
			->willReturn(false);

		$reads = 0;
		$cache->method('get')->willReturnCallback(function () use (&$reads): ?string {
			$reads++;
			return $reads >= 2 ? 'shared-token' : null;
		});

		$client = $this->createClient($clientService, $cache);
		$result = $client->nextcloudStatus('status-request');
		$this->assertTrue($result['ok']);
	}

	public function testNextcloudStatusMintReleasesTokenLock(): void {
		$tokenResponse = $this->createResponse(200, '{"access":"token","expires_in":300}');
		$statusResponse = $this->createResponse(
			200,
			'{"status":0,"message":"OK","data":{"ok":true,"server_time":"2025-01-01T00:00:00Z","version":"1.0.0","capabilities":{"png_preview":true}}}',
		);

		$tokenClient = $this->createMock(IClient::class);
		$tokenClient->expects($this->once())
			->method('post')
			->willReturn($tokenResponse);

		$statusClient = $this->createMock(IClient::class);
		$statusClient->expects($this->once())
			->method('get')
			->with(
				$this->stringContains('/api/v1/integrations/nextcloud/status/'),
				$this->callback(fn (array $options): bool => $this->hasCorrectOptions($options, 'status-request', 'Bearer token')
					&& $options['headers']['Accept'] === 'application/json'),
			)
			->willReturn($statusResponse);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->exactly(2))
			->method('newClient')
			->willReturnOnConsecutiveCalls($tokenClient, $statusClient);

		$cache = $this->createMock(IMemcache::class);
		$cache->expects($this->once())
			->method('add')
			->with('integration_access_token:mint_lock', 'nonce', 15)
			->willReturn(true);
		$cache->expects($this->once())
			->method('cad')
			->with('integration_access_token:mint_lock', 'nonce')
			->willReturn(true);
		$cache->expects($this->once())
			->method('set')
			->with('integration_access_token', 'token', 295)
			->willReturn(true);
		$cache->method('get')->willReturn(null);

		$client = $this->createClient($clientService, $cache);
		$result = $client->nextcloudStatus('status-request');
		$this->assertTrue($result['ok']);
	}

	public function testNextcloudPreviewReturnsPng(): void {
		$pngPayload = "\x89PNG\r\n\x1a\nfake-bytes";
		$previewResponse = $this->createResponse(
			200,
			$pngPayload,
			['Content-Type' => 'image/png'],
		);

		$previewClient = $this->createMock(IClient::class);
		$previewClient
			->expects($this->once())
			->method('get')
			->with(
				$this->stringContains('/api/v1/integrations/nextcloud/preview.png'),
				$this->callback(fn (array $options): bool => $this->hasCorrectOptions($options, 'preview-request', 'Bearer cached-token')
					&& $options['headers']['Accept'] === 'image/png'),
			)
			->willReturn($previewResponse);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->once())
			->method('newClient')
			->willReturn($previewClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn('cached-token');

		$client = $this->createClient($clientService, $cache);
		$content = $client->nextcloudPreviewPng('preview-request');
		$this->assertSame($pngPayload, $content);
	}

	public function testRequestJsonUsesBearerTokenAndQuery(): void {
		$response = $this->createResponse(200, '{"ok":true}');

		$jsonClient = $this->createMock(IClient::class);
		$jsonClient
			->expects($this->once())
			->method('get')
			->with(
				$this->stringContains('/api/v1/farms/'),
				$this->callback(fn (array $options): bool => $this->hasCorrectOptions($options, 'json-request', 'Bearer cached-token')
					&& $options['headers']['Accept'] === 'application/json'
					&& ($options['query']['page'] ?? null) === 2
					&& !array_key_exists('body', $options)),
			)
			->willReturn($response);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->once())
			->method('newClient')
			->willReturn($jsonClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn('cached-token');

		$client = $this->createClient($clientService, $cache);
		$payload = $client->requestJson('GET', '/api/v1/farms/', ['page' => 2], null, 'json-request');

		$this->assertTrue($payload['ok']);
	}

	public function testRequestJsonLogsTransportFailure(): void {
		$exception = new \RuntimeException('boom');

		$jsonClient = $this->createMock(IClient::class);
		$jsonClient
			->expects($this->once())
			->method('get')
			->willThrowException($exception);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->once())
			->method('newClient')
			->willReturn($jsonClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn('cached-token');

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('warning')
			->with(
				'Weather API transport request failed',
				$this->callback(function (array $context): bool {
					return ($context['requestId'] ?? '') === 'log-request'
						&& ($context['method'] ?? '') === 'GET'
						&& str_contains((string)($context['url'] ?? ''), '/api/v1/farms/')
						&& ($context['exception'] ?? '') === \RuntimeException::class
						&& str_contains((string)($context['message'] ?? ''), 'boom');
				}),
			);

		$client = $this->createClient($clientService, $cache, $logger);

		$this->expectException(WeatherApiException::class);
		$client->requestJson('GET', '/api/v1/farms/', ['page' => 1], null, 'log-request');
	}

	public function testRequestJsonLogsHttpFailure(): void {
		$response = $this->createResponse(503, 'Bad Gateway');

		$jsonClient = $this->createMock(IClient::class);
		$jsonClient
			->expects($this->once())
			->method('get')
			->willReturn($response);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->once())
			->method('newClient')
			->willReturn($jsonClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn('cached-token');

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('warning')
			->with(
				'Weather API HTTP request failed',
				$this->callback(function (array $context): bool {
					return ($context['requestId'] ?? '') === 'http-fail'
						&& ($context['method'] ?? '') === 'GET'
						&& str_contains((string)($context['url'] ?? ''), '/api/v1/farms/')
						&& ($context['httpStatus'] ?? null) === 503
						&& str_contains((string)($context['responseSnippet'] ?? ''), 'Bad Gateway');
				}),
			);

		$client = $this->createClient($clientService, $cache, $logger);

		$this->expectException(WeatherApiException::class);
		$client->requestJson('GET', '/api/v1/farms/', ['page' => 1], null, 'http-fail');
	}

	public function testRequestBinaryReturnsBytes(): void {
		$pngPayload = "\x89PNG\r\n\x1a\nbinary";
		$response = $this->createResponse(200, $pngPayload, ['Content-Type' => 'image/png']);

		$binaryClient = $this->createMock(IClient::class);
		$binaryClient
			->expects($this->once())
			->method('get')
			->with(
				$this->stringContains('/api/v1/farms/1/ndvi/raster.png'),
				$this->callback(fn (array $options): bool => $this->hasCorrectOptions($options, 'binary-request', 'Bearer cached-token')
					&& $options['headers']['Accept'] === 'image/png'),
			)
			->willReturn($response);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->once())
			->method('newClient')
			->willReturn($binaryClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn('cached-token');

		$client = $this->createClient($clientService, $cache);
		$result = $client->requestBinary('GET', '/api/v1/farms/1/ndvi/raster.png', [], 'binary-request');

		$this->assertSame('image/png', $result['contentType']);
		$this->assertSame($pngPayload, $result['body']);
		$this->assertSame(200, $result['statusCode']);
	}

	public function testFetchSchemaUsesJsonFormat(): void {
		$schemaResponse = $this->createResponse(200, '{"openapi":"3.0.0","paths":{}}');

		$schemaClient = $this->createMock(IClient::class);
		$schemaClient
			->expects($this->once())
			->method('get')
			->with(
				$this->stringContains('/api/schema/?format=json'),
				$this->callback(fn (array $options): bool => $this->hasCorrectOptions($options, 'schema-request')
					&& $options['headers']['Accept'] === 'application/json'),
			)
			->willReturn($schemaResponse);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->once())
			->method('newClient')
			->willReturn($schemaClient);

		$cache = $this->createMock(ICache::class);

		$client = $this->createClient($clientService, $cache);
		$result = $client->fetchSchema('schema-request');

		$this->assertSame('3.0.0', $result['openapi']);
	}

	public function testNextcloudStatusUnauthorizedRetriesOnce(): void {
		$tokenResponse = $this->createResponse(200, '{"access":"token","expires_in":300}');
		$statusUnauthorized = $this->createResponse(401, '{"detail":"nope"}');
		$statusOk = $this->createResponse(
			200,
			'{"status":0,"message":"OK","data":{"ok":true,"server_time":"2025-01-01T00:00:00Z","version":"1.0.0","capabilities":{"png_preview":true}}}',
		);

		$firstStatusClient = $this->createMock(IClient::class);
		$firstStatusClient->expects($this->once())
			->method('get')
			->willReturn($statusUnauthorized);

		$tokenClient = $this->createMock(IClient::class);
		$tokenClient->expects($this->once())
			->method('post')
			->willReturn($tokenResponse);

		$secondStatusClient = $this->createMock(IClient::class);
		$secondStatusClient->expects($this->once())
			->method('get')
			->willReturn($statusOk);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->exactly(3))
			->method('newClient')
			->willReturnOnConsecutiveCalls($firstStatusClient, $tokenClient, $secondStatusClient);

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
		$result = $client->nextcloudStatus('rid');
		$this->assertTrue($result['ok']);
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
			$details = $exception->getDetails();
			$this->assertSame(\RuntimeException::class, $details['exception'] ?? '');
			$this->assertIsString($details['exceptionMessage'] ?? null);
			throw $exception;
		}
	}

	public function testNon2xxResponseMapsToBackendUnavailable(): void {
		$tokenResponse = $this->createResponse(200, '{"access":"token","expires_in":300}');
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

	public function testClientErrorMapsToInvalidArgumentWithDetails(): void {
		$response = $this->createResponse(400, '{"message":"start required"}', ['Content-Type' => 'application/json']);

		$jsonClient = $this->createMock(IClient::class);
		$jsonClient
			->expects($this->once())
			->method('get')
			->willReturn($response);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->once())
			->method('newClient')
			->willReturn($jsonClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn('cached-token');

		$client = $this->createClient($clientService, $cache);

		$this->expectException(WeatherApiException::class);
		try {
			$client->requestJson('GET', '/api/v1/farms/', [], null, 'rid');
		} catch (WeatherApiException $exception) {
			$this->assertSame('invalid_argument', $exception->getErrorCode());
			$details = $exception->getDetails();
			$this->assertSame(400, $details['httpStatus'] ?? null);
			$this->assertSame('application/json', $details['responseContentType'] ?? '');
			$this->assertIsString($details['drfMessage'] ?? null);
			throw $exception;
		}
	}

	public function testTransportErrorMapsToBackendUnavailableWithDetails(): void {
		$exception = new \RuntimeException('connection refused');

		$jsonClient = $this->createMock(IClient::class);
		$jsonClient
			->expects($this->once())
			->method('get')
			->willThrowException($exception);

		$clientService = $this->createMock(IClientService::class);
		$clientService->expects($this->once())
			->method('newClient')
			->willReturn($jsonClient);

		$cache = $this->createMock(ICache::class);
		$cache->method('get')->willReturn('cached-token');

		$client = $this->createClient($clientService, $cache);

		$this->expectException(WeatherApiException::class);
		try {
			$client->requestJson('GET', '/api/v1/farms/', [], null, 'rid');
		} catch (WeatherApiException $exception) {
			$this->assertSame('backend_unavailable', $exception->getErrorCode());
			$details = $exception->getDetails();
			$this->assertSame(\RuntimeException::class, $details['exception'] ?? '');
			$this->assertIsString($details['exceptionMessage'] ?? null);
			throw $exception;
		}
	}

	public function testInvalidJsonResponsesMapToBackendError(): void {
		$tokenResponse = $this->createResponse(200, '{"access":"token","expires_in":300}');
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
			&& $options['http_errors'] === false
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

	private function createClient(
		IClientService $clientService,
		ICache $cache,
		?LoggerInterface $logger = null,
	): WeatherApiClient {
		return new WeatherApiClient(
			$clientService,
			$this->createAppConfig(),
			$this->createIntegrationConfig(),
			new UrlValidator(fn (string $host): array => ['93.184.216.34']),
			new TokenSigner(),
			$cache,
			$logger ?? $this->createMock(LoggerInterface::class),
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
			function (string $appId, string $key, mixed $default = '') use ($storage): mixed {
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
			function (string $appId, string $key, mixed $default = '') use ($storage): mixed {
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

	private function createResponse(int $status, string $body, array $headers = []): IResponse {
		$response = $this->createMock(IResponse::class);
		$response->method('getStatusCode')->willReturn($status);
		$response->method('getBody')->willReturn($body);
		$response->method('getHeader')->willReturnCallback(
			fn (string $key): string => $headers[$key] ?? '',
		);
		$response->method('getHeaders')->willReturn($headers);

		return $response;
	}
}
