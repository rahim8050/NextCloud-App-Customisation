<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Tests\Unit\Controller;

use OCA\FarmIntelligencePlatform\Controller\RadioUserController;
use OCA\FarmIntelligencePlatform\Service\DrfSchemaService;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class RadioUserControllerTest extends TestCase {
	public function testListFavoritesHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_favorites_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_favorites_list',
				'method' => 'GET',
				'path' => '/api/v1/radio/favorites/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/favorites/', [], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->listFavorites();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame(['results' => []], $data['data']);
	}

	public function testListFavoritesForwardsPageAndPageSize(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getParams')->willReturn(['page' => 2, 'page_size' => 50]);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_favorites_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_favorites_list',
				'method' => 'GET',
				'path' => '/api/v1/radio/favorites/',
				'queryParams' => [
					['name' => 'page', 'type' => 'integer', 'format' => null, 'required' => false],
					['name' => 'page_size', 'type' => 'integer', 'format' => null, 'required' => false],
				],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/favorites/', ['page' => 2, 'page_size' => 50], null, 'request-id')
			->willReturn(['payload' => ['count' => 100], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->listFavorites(2, 50);
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame(100, $data['data']['count']);
	}

	public function testAddFavoriteForwardsBody(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('POST');
		$request->method('getParams')->willReturn(['station_id' => 'bbc_1xtra']);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_favorites_create', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_favorites_create',
				'method' => 'POST',
				'path' => '/api/v1/radio/favorites/',
				'queryParams' => [],
				'bodyFields' => [
					'station_id' => ['required' => true],
				],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('POST', '/api/v1/radio/favorites/', [], ['station_id' => 'bbc_1xtra'], 'request-id')
			->willReturn(['payload' => ['id' => 1, 'station_id' => 'bbc_1xtra'], 'statusCode' => 201]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->addFavorite();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame('bbc_1xtra', $data['data']['station_id']);
	}

	public function testRemoveFavoriteEncodesStationId(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('DELETE');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_favorites_destroy', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_favorites_destroy',
				'method' => 'DELETE',
				'path' => '/api/v1/radio/favorites/{station_id}/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('DELETE', '/api/v1/radio/favorites/bbc_1xtra/', [], null, 'request-id')
			->willReturn(['payload' => null, 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->removeFavorite('bbc_1xtra');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testListHistoryHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_history_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_history_list',
				'method' => 'GET',
				'path' => '/api/v1/radio/history/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/history/', [], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->listHistory();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testGetRecentHistoryForwardsLimit(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getParams')->willReturn(['limit' => 10]);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_history_recent_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_history_recent_list',
				'method' => 'GET',
				'path' => '/api/v1/radio/history/recent/',
				'queryParams' => [
					['name' => 'limit', 'type' => 'integer', 'format' => null, 'required' => false],
				],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/history/recent/', ['limit' => 10], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getRecentHistory(10);
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testStopSessionHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('POST');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_history_stop_create', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_history_stop_create',
				'method' => 'POST',
				'path' => '/api/v1/radio/history/{session_id}/stop/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('POST', '/api/v1/radio/history/42/stop/', [], null, 'request-id')
			->willReturn(['payload' => null, 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->stopSession(42);
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testGetSignedStreamEncodesStationId(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_stations_signed_stream_retrieve', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_stations_signed_stream_retrieve',
				'method' => 'GET',
				'path' => '/api/v1/radio/stations/{station_id}/stream/signed/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/stations/bbc_1xtra/stream/signed/', [], null, 'request-id')
			->willReturn(['payload' => ['token' => 'jwt...', 'stream_url' => 'https://...'], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getSignedStream('bbc_1xtra');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame('jwt...', $data['data']['token']);
	}

	public function testWeatherApiExceptionSurfacesAsErrorResponse(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_favorites_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_favorites_list',
				'method' => 'GET',
				'path' => '/api/v1/radio/favorites/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willThrowException(new WeatherApiException('upstream_error', 'upstream down', null, null, ['detail' => 'bad gateway']));

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->listFavorites();
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertFalse($data['ok']);
		$this->assertSame('upstream_error', $data['error']['code']);
		$this->assertSame(['detail' => 'bad gateway'], $data['error']['details']);
	}

	public function testUnexpectedThrowableSurfacesAsBackendError(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_history_recent_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_history_recent_list',
				'method' => 'GET',
				'path' => '/api/v1/radio/history/recent/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willThrowException(new \RuntimeException('boom'));

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getRecentHistory();
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('backend_error', $data['error']['code']);
	}

	private function stubRequestHeaders($request, string $requestId = 'request-id'): void {
		$request->method('getHeader')
			->willReturnCallback(static function (string $name) use ($requestId): string {
				return $name === 'X-Request-Id' ? $requestId : '';
			});
	}

	private function createController(
		IRequest $request,
		WeatherApiClientInterface $client,
		DrfSchemaService $schemaService,
	): RadioUserController {
		$logger = $this->createMock(LoggerInterface::class);
		return new RadioUserController('farm_intelligence_platform', $request, $client, $schemaService, $logger);
	}

	private function decodeResponse(JSONResponse $response): array {
		return json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);
	}
}
