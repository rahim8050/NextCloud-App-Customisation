<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Tests\Unit\Controller;

use OCA\FarmIntelligencePlatform\Controller\PodcastsController;
use OCA\FarmIntelligencePlatform\Service\DrfSchemaService;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class PodcastsControllerTest extends TestCase {
	public function testListHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_podcasts_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_podcasts_list',
				'method' => 'GET',
				'path' => '/api/v1/podcasts/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/podcasts/', [], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->list();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('ok', $data['status']);
		$this->assertSame([], $data['data']['results']);
	}

	public function testGetEncodesPodcastId(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_podcasts_retrieve', 'request-id')
			->willReturn([
				'operationId' => 'v1_podcasts_retrieve',
				'method' => 'GET',
				'path' => '/api/v1/podcasts/{podcast_id}/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/podcasts/bbc-world/', [], null, 'request-id')
			->willReturn(['payload' => ['id' => 'bbc-world', 'title' => 'BBC World'], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->get('bbc-world');
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('BBC World', $data['data']['title']);
	}

	public function testListEpisodesForwardsLimit(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');
		$request->method('getParams')->willReturn(['limit' => 10]);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_podcasts_episodes_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_podcasts_episodes_list',
				'method' => 'GET',
				'path' => '/api/v1/podcasts/{podcast_id}/episodes/',
				'queryParams' => [
					['name' => 'limit', 'type' => 'integer', 'format' => null, 'required' => false],
				],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/podcasts/p1/episodes/', ['limit' => 10], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->listEpisodes('p1', 10);
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame([], $data['data']['results']);
	}

	public function testListEpisodesOmitsLimitWhenNull(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_podcasts_episodes_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_podcasts_episodes_list',
				'method' => 'GET',
				'path' => '/api/v1/podcasts/{podcast_id}/episodes/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/podcasts/p1/episodes/', [], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$controller->listEpisodes('p1');
	}

	public function testGetStreamUrlHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_podcasts_episodes_stream_retrieve', 'request-id')
			->willReturn([
				'operationId' => 'v1_podcasts_episodes_stream_retrieve',
				'method' => 'GET',
				'path' => '/api/v1/podcasts/episodes/{episode_id}/stream/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/podcasts/episodes/42/stream/', [], null, 'request-id')
			->willReturn(['payload' => ['audio_url' => 'https://example.com/audio.mp3'], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getStreamUrl(42);
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('https://example.com/audio.mp3', $data['data']['audio_url']);
	}

	public function testRefreshForwardsBody(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('POST');
		$request->method('getParams')->willReturn(['force' => true]);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_podcasts_refresh_create', 'request-id')
			->willReturn([
				'operationId' => 'v1_podcasts_refresh_create',
				'method' => 'POST',
				'path' => '/api/v1/podcasts/{podcast_id}/refresh/',
				'queryParams' => [],
				'bodyFields' => ['force' => ['type' => 'boolean', 'format' => null, 'required' => false, 'readOnly' => false]],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('POST', '/api/v1/podcasts/p1/refresh/', [], ['force' => true], 'request-id')
			->willReturn(['payload' => ['status' => 'queued'], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->refresh('p1');
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('queued', $data['data']['status']);
	}

	public function testListMethodHasPublicPageAttribute(): void {
		$reflection = new \ReflectionMethod(PodcastsController::class, 'list');
		$this->assertNotEmpty($reflection->getAttributes(PublicPage::class));
	}

	public function testGetMethodHasPublicPageAttribute(): void {
		$reflection = new \ReflectionMethod(PodcastsController::class, 'get');
		$this->assertNotEmpty($reflection->getAttributes(PublicPage::class));
	}

	public function testWeatherApiExceptionSurfacesAsErrorResponse(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_podcasts_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_podcasts_list',
				'method' => 'GET',
				'path' => '/api/v1/podcasts/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willThrowException(new WeatherApiException('backend_error', 'down'));

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->list();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('error', $data['status']);
		$this->assertSame('backend_error', $data['error']['code']);
	}

	private function stubRequestHeaders($request, string $requestId = 'request-id'): void {
		$request->method('getHeader')
			->willReturnCallback(static function (string $name) use ($requestId): string {
				return $name === 'X-Request-Id' ? $requestId : '';
			});
	}

	private function createController(IRequest $request, WeatherApiClientInterface $client, DrfSchemaService $schemaService): PodcastsController {
		$logger = $this->createMock(LoggerInterface::class);
		return new PodcastsController('farm_intelligence_platform', $request, $client, $schemaService, $logger);
	}
}
