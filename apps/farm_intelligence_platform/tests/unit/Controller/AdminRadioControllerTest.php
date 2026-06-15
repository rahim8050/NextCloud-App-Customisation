<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Tests\Unit\Controller;

use OCA\FarmIntelligencePlatform\Controller\AdminRadioController;
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
		$request->method('getPathInfo')->willReturn('/api/v1/admin/radio/providers');
		$request->method('getRequestUri')->willReturn('/api/v1/admin/radio/providers');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/providers/', [], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client);
		$response = $controller->listProviders();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame(['results' => []], $data['data']);
	}

	public function testGetStationNowPlayingEncodesStationId(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getPathInfo')->willReturn('/api/v1/admin/radio/stations/x/now-playing');
		$request->method('getRequestUri')->willReturn('');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/stations/x/now-playing/', [], null, 'request-id')
			->willReturn(['payload' => ['track_title' => 'T', 'artist' => 'A'], 'statusCode' => 200]);

		$controller = $this->createController($request, $client);
		$response = $controller->getStationNowPlaying('x');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame('T', $data['data']['track_title']);
	}

	public function testGetStationAnalyticsForwardsDaysQueryParam(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getPathInfo')->willReturn('/api/v1/admin/radio/stations/x/analytics');
		$request->method('getRequestUri')->willReturn('');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/stations/x/analytics/', ['days' => 14], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client);
		$response = $controller->getStationAnalytics('x', 14);
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testGetStationAnalyticsOmitsDaysWhenNull(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getPathInfo')->willReturn('');
		$request->method('getRequestUri')->willReturn('');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/stations/x/analytics/', [], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client);
		$controller->getStationAnalytics('x');
	}

	public function testGetStationHealthEncodesStationId(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getPathInfo')->willReturn('');
		$request->method('getRequestUri')->willReturn('');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/stations/bbc_1xtra/health/', [], null, 'request-id')
			->willReturn(['payload' => ['is_available' => true], 'statusCode' => 200]);

		$controller = $this->createController($request, $client);
		$response = $controller->getStationHealth('bbc_1xtra');
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertTrue($data['data']['is_available']);
	}

	public function testGetStationHealthHistoryForwardsLimit(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getPathInfo')->willReturn('');
		$request->method('getRequestUri')->willReturn('');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/stations/x/health/history/', ['limit' => 5], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client);
		$response = $controller->getStationHealthHistory('x', 5);
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testGetRadioHealthHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getPathInfo')->willReturn('');
		$request->method('getRequestUri')->willReturn('');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/health/', [], null, 'request-id')
			->willReturn(['payload' => ['total' => 12, 'available' => 11, 'unavailable' => 1], 'statusCode' => 200]);

		$controller = $this->createController($request, $client);
		$response = $controller->getRadioHealth();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame(12, $data['data']['total']);
	}

	public function testGetCurrentEmergencyHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getPathInfo')->willReturn('');
		$request->method('getRequestUri')->willReturn('');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/emergency/current/', [], null, 'request-id')
			->willReturn(['payload' => ['title' => 'Storm warning'], 'statusCode' => 200]);

		$controller = $this->createController($request, $client);
		$response = $controller->getCurrentEmergency();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame('Storm warning', $data['data']['title']);
	}

	public function testGetEmergencyHistoryForwardsLimit(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getPathInfo')->willReturn('');
		$request->method('getRequestUri')->willReturn('');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/radio/emergency/history/', ['limit' => 20], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client);
		$response = $controller->getEmergencyHistory(20);
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
	}

	public function testWeatherApiExceptionSurfacesAsErrorResponse(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getPathInfo')->willReturn('');
		$request->method('getRequestUri')->willReturn('');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willThrowException(new WeatherApiException('upstream_error', 'upstream down', null, null, ['detail' => 'bad gateway']));

		$controller = $this->createController($request, $client);
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
		$request->method('getPathInfo')->willReturn('');
		$request->method('getRequestUri')->willReturn('');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willThrowException(new \RuntimeException('boom'));

		$controller = $this->createController($request, $client);
		$response = $controller->getCurrentEmergency();
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('backend_error', $data['error']['code']);
	}

	/**
	 * @param IRequest&\PHPUnit\Framework\MockObject\MockObject $request
	 */
	private function stubRequestHeaders($request, string $requestId = 'request-id'): void {
		$request->method('getHeader')
			->willReturnCallback(static function (string $name) use ($requestId): string {
				return $name === 'X-Request-Id' ? $requestId : '';
			});
	}

	private function createController(
		IRequest $request,
		WeatherApiClientInterface $client,
	): AdminRadioController {
		$logger = $this->createMock(LoggerInterface::class);
		return new AdminRadioController('farm_intelligence_platform', $request, $client, $logger);
	}

	private function decodeResponse(JSONResponse $response): array {
		return json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);
	}
}
