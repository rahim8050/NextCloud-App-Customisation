<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Tests\Unit\Controller;

use OCA\FarmIntelligencePlatform\Controller\AdminApiKeysController;
use OCA\FarmIntelligencePlatform\Service\DrfSchemaService;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class AdminApiKeysControllerTest extends TestCase {
	public function testListKeysHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_keys_retrieve', 'request-id')
			->willReturn([
				'operationId' => 'v1_keys_list',
				'method' => 'GET',
				'path' => '/api/v1/keys/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/keys/', [], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->listKeys();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('ok', $data['status']);
		$this->assertSame([], $data['data']['results']);
	}

	public function testCreateKeyForwardsBody(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('POST');
		$request->method('getParams')->willReturn(['name' => 'My Key']);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_keys_create', 'request-id')
			->willReturn([
				'operationId' => 'v1_keys_create',
				'method' => 'POST',
				'path' => '/api/v1/keys/',
				'queryParams' => [],
				'bodyFields' => ['name' => ['type' => 'string', 'format' => null, 'required' => false, 'readOnly' => false]],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('POST', '/api/v1/keys/', [], ['name' => 'My Key'], 'request-id')
			->willReturn(['payload' => ['id' => 'uuid', 'name' => 'My Key'], 'statusCode' => 201]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->createKey();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('My Key', $data['data']['name']);
	}

	public function testCreateKeyReturnsCreated(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('POST');
		$request->method('getParams')->willReturn([]);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_keys_create', 'request-id')
			->willReturn([
				'operationId' => 'v1_keys_create',
				'method' => 'POST',
				'path' => '/api/v1/keys/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willReturn(['payload' => ['key' => 'sk-xxx'], 'statusCode' => 201]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->createKey();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('sk-xxx', $data['data']['key']);
		$this->assertSame(201, $response->getStatus());
	}

	public function testRevokeKeyEncodesPk(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('DELETE');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_keys_destroy', 'request-id')
			->willReturn([
				'operationId' => 'v1_keys_destroy',
				'method' => 'DELETE',
				'path' => '/api/v1/keys/{pk}/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('DELETE', '/api/v1/keys/some-uuid/', [], null, 'request-id')
			->willReturn(['payload' => null, 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->revokeKey('some-uuid');
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertNull($data['data']);
	}

	public function testRotateKeyHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('POST');
		$request->method('getParams')->willReturn(['name' => 'rotated-key']);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_keys_rotate_create', 'request-id')
			->willReturn([
				'operationId' => 'v1_keys_rotate_create',
				'method' => 'POST',
				'path' => '/api/v1/keys/{pk}/rotate/',
				'queryParams' => [],
				'bodyFields' => ['name' => ['type' => 'string', 'format' => null, 'required' => false, 'readOnly' => false]],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('POST', '/api/v1/keys/uuid-123/rotate/', [], ['name' => 'rotated-key'], 'request-id')
			->willReturn(['payload' => ['key' => 'sk-new'], 'statusCode' => 201]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->rotateKey('uuid-123');
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('sk-new', $data['data']['key']);
	}

	public function testRotateKeyReturnsCreated(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('POST');
		$request->method('getParams')->willReturn([]);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_keys_rotate_create', 'request-id')
			->willReturn([
				'operationId' => 'v1_keys_rotate_create',
				'method' => 'POST',
				'path' => '/api/v1/keys/{pk}/rotate/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willReturn(['payload' => ['key' => 'sk-new'], 'statusCode' => 201]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->rotateKey('uuid-123');

		$this->assertSame(201, $response->getStatus());
	}

	public function testWeatherApiExceptionSurfacesAsErrorResponse(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_keys_retrieve', 'request-id')
			->willReturn([
				'operationId' => 'v1_keys_list',
				'method' => 'GET',
				'path' => '/api/v1/keys/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willThrowException(new WeatherApiException('unauthorized', 'bad key'));

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->listKeys();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('error', $data['status']);
		$this->assertSame('unauthorized', $data['error']['code']);
	}

	private function stubRequestHeaders($request, string $requestId = 'request-id'): void {
		$request->method('getHeader')
			->willReturnCallback(static function (string $name) use ($requestId): string {
				return $name === 'X-Request-Id' ? $requestId : '';
			});
	}

	private function createController(IRequest $request, WeatherApiClientInterface $client, DrfSchemaService $schemaService): AdminApiKeysController {
		$logger = $this->createMock(LoggerInterface::class);
		return new AdminApiKeysController('farm_intelligence_platform', $request, $client, $schemaService, $logger);
	}
}
