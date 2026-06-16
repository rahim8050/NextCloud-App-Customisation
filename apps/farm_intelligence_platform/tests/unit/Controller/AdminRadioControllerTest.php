<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Tests\Unit\Controller;

use OCA\FarmIntelligencePlatform\Controller\AdminRadioController;
use OCA\FarmIntelligencePlatform\Service\DrfSchemaService;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class AdminRadioControllerTest extends TestCase {
	public function testListProvidersProxiesAndUnwrapsPayload(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_providers_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_providers_list',
				'method' => 'GET',
				'path' => '/api/v1/radio/providers/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/providers/', [], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->listProviders();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame(['results' => []], $data['data']);
	}

	public function testGetStationNowPlayingEncodesStationId(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_stations_now_playing_retrieve', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_stations_now_playing_retrieve',
				'method' => 'GET',
				'path' => '/api/v1/radio/stations/{station_id}/now-playing/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/stations/x/now-playing/', [], null, 'request-id')
			->willReturn(['payload' => ['track_title' => 'T', 'artist' => 'A'], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getStationNowPlaying('x');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame('T', $data['data']['track_title']);
	}

	public function testGetStationAnalyticsForwardsDaysQueryParam(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getParams')->willReturn(['days' => 14]);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_stations_analytics_retrieve', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_stations_analytics_retrieve',
				'method' => 'GET',
				'path' => '/api/v1/radio/stations/{station_id}/analytics/',
				'queryParams' => [
					['name' => 'days', 'type' => 'integer', 'format' => null, 'required' => false],
				],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/stations/x/analytics/', ['days' => 14], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getStationAnalytics('x', 14);
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testGetStationAnalyticsOmitsDaysWhenNull(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_stations_analytics_retrieve', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_stations_analytics_retrieve',
				'method' => 'GET',
				'path' => '/api/v1/radio/stations/{station_id}/analytics/',
				'queryParams' => [
					['name' => 'days', 'type' => 'integer', 'format' => null, 'required' => false],
				],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/stations/x/analytics/', [], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$controller->getStationAnalytics('x');
	}

	public function testGetStationHealthEncodesStationId(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_stations_health_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_stations_health_list',
				'method' => 'GET',
				'path' => '/api/v1/radio/stations/{station_id}/health/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/stations/bbc_1xtra/health/', [], null, 'request-id')
			->willReturn(['payload' => ['is_available' => true], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getStationHealthHistory('bbc_1xtra');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertTrue($data['data']['is_available']);
	}

	public function testGetStationHealthHistoryForwardsLimit(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getParams')->willReturn(['limit' => 5]);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_stations_health_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_stations_health_list',
				'method' => 'GET',
				'path' => '/api/v1/radio/stations/{station_id}/health/',
				'queryParams' => [
					['name' => 'limit', 'type' => 'integer', 'format' => null, 'required' => false],
				],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/stations/x/health/', ['limit' => 5], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getStationHealthHistory('x', 5);
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testGetRadioHealthHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_health_retrieve', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_health_retrieve',
				'method' => 'GET',
				'path' => '/api/v1/radio/health/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/health/', [], null, 'request-id')
			->willReturn(['payload' => ['total' => 12, 'available' => 11, 'unavailable' => 1], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getRadioHealth();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame(12, $data['data']['total']);
	}

	public function testGetCurrentEmergencyHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_emergency_current_retrieve', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_emergency_current_retrieve',
				'method' => 'GET',
				'path' => '/api/v1/radio/emergency/current/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/emergency/current/', [], null, 'request-id')
			->willReturn(['payload' => ['title' => 'Storm warning'], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getCurrentEmergency();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame('Storm warning', $data['data']['title']);
	}

	public function testGetEmergencyHistoryForwardsLimit(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getParams')->willReturn(['limit' => 20]);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_emergency_history_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_emergency_history_list',
				'method' => 'GET',
				'path' => '/api/v1/radio/emergency/history/',
				'queryParams' => [
					['name' => 'limit', 'type' => 'integer', 'format' => null, 'required' => false],
				],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/emergency/history/', ['limit' => 20], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getEmergencyHistory(20);
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testCreateEmergencyForwardsBody(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('POST');
		$request->method('getParams')->willReturn(['title' => 'Storm warning', 'severity' => 'high']);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_emergency_create', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_emergency_create',
				'method' => 'POST',
				'path' => '/api/v1/radio/emergency/',
				'queryParams' => [],
				'bodyFields' => [
					'title' => ['required' => true],
					'severity' => ['required' => true],
				],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('POST', '/api/v1/radio/emergency/', [], ['title' => 'Storm warning', 'severity' => 'high'], 'request-id')
			->willReturn(['payload' => ['id' => 1, 'title' => 'Storm warning'], 'statusCode' => 201]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->createEmergency();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame('Storm warning', $data['data']['title']);
	}

	public function testUpdateEmergencyForwardsBody(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('PATCH');
		$request->method('getParams')->willReturn(['title' => 'Updated']);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_emergency_partial_update', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_emergency_partial_update',
				'method' => 'PATCH',
				'path' => '/api/v1/radio/emergency/{pk}/',
				'queryParams' => [],
				'bodyFields' => [
					'title' => ['required' => false],
				],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('PATCH', '/api/v1/radio/emergency/5/', [], ['title' => 'Updated'], 'request-id')
			->willReturn(['payload' => ['id' => 5, 'title' => 'Updated'], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->updateEmergency(5);
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame('Updated', $data['data']['title']);
	}

	public function testDeleteEmergencyHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('DELETE');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_emergency_destroy', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_emergency_destroy',
				'method' => 'DELETE',
				'path' => '/api/v1/radio/emergency/{pk}/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('DELETE', '/api/v1/radio/emergency/5/', [], null, 'request-id')
			->willReturn(['payload' => null, 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->deleteEmergency(5);
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testSynthesizeTtsForwardsBody(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('POST');
		$request->method('getParams')->willReturn(['text' => 'Hello world', 'voice' => 'en-US']);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_tts_create', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_tts_create',
				'method' => 'POST',
				'path' => '/api/v1/radio/tts/',
				'queryParams' => [],
				'bodyFields' => [
					'text' => ['required' => true],
					'voice' => ['required' => true],
				],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('POST', '/api/v1/radio/tts/', [], ['text' => 'Hello world', 'voice' => 'en-US'], 'request-id')
			->willReturn(['payload' => ['mime_type' => 'audio/wav', 'duration_ms' => 1500, 'audio_base64' => 'AAAA'], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->synthesizeTts();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame('audio/wav', $data['data']['mime_type']);
	}

	public function testWeatherApiExceptionSurfacesAsErrorResponse(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_radio_health_retrieve', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_health_retrieve',
				'method' => 'GET',
				'path' => '/api/v1/radio/health/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willThrowException(new WeatherApiException('upstream_error', 'upstream down', null, null, ['detail' => 'bad gateway']));

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getRadioHealth();
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
			->with('v1_radio_emergency_current_retrieve', 'request-id')
			->willReturn([
				'operationId' => 'v1_radio_emergency_current_retrieve',
				'method' => 'GET',
				'path' => '/api/v1/radio/emergency/current/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willThrowException(new \RuntimeException('boom'));

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getCurrentEmergency();
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
	): AdminRadioController {
		$logger = $this->createMock(LoggerInterface::class);
		return new AdminRadioController('farm_intelligence_platform', $request, $client, $schemaService, $logger);
	}

	private function decodeResponse(JSONResponse $response): array {
		return json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);
	}
}
