<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Tests\Unit\Controller;

use OCA\FarmIntelligencePlatform\Controller\AdminActivitiesController;
use OCA\FarmIntelligencePlatform\Service\DrfSchemaService;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class AdminActivitiesControllerGetHealthTest extends TestCase {
	public function testGetHealthPassesCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$request->method('getHeader')->willReturn('req-123');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/activities/health/', [], null, 'req-123')
			->willReturn(['payload' => ['status' => 'healthy'], 'statusCode' => 200]);

		$controller = $this->createController($request, $client);
		$response = $controller->getHealth();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('healthy', $data['status']);
	}

	public function testGetHealthHasAdminRequiredAttribute(): void {
		$reflection = new \ReflectionMethod(AdminActivitiesController::class, 'getHealth');
		$this->assertNotEmpty($reflection->getAttributes(AdminRequired::class));
	}

	public function testGetHealthReturnsRawPayloadWithoutEnvelope(): void {
		$request = $this->createMock(IRequest::class);
		$request->method('getHeader')->willReturn('req-123');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willReturn(['payload' => ['healthy' => true], 'statusCode' => 200]);

		$controller = $this->createController($request, $client);
		$response = $controller->getHealth();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertArrayNotHasKey('status', $data);
		$this->assertArrayNotHasKey('ok', $data);
		$this->assertTrue($data['healthy']);
	}

	public function testGetHealthHandlesWeatherApiException(): void {
		$request = $this->createMock(IRequest::class);
		$request->method('getHeader')->willReturn('req-123');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willThrowException(new WeatherApiException('backend_error', 'down'));

		$controller = $this->createController($request, $client);
		$response = $controller->getHealth();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('error', $data['status']);
		$this->assertSame('backend_error', $data['error']['code']);
	}

	public function testGetHealthHandlesUnexpectedThrowable(): void {
		$request = $this->createMock(IRequest::class);
		$request->method('getHeader')->willReturn('req-123');

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willThrowException(new \RuntimeException('unexpected'));

		$controller = $this->createController($request, $client);
		$response = $controller->getHealth();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('error', $data['status']);
		$this->assertSame('backend_error', $data['error']['code']);
	}

	private function createController(IRequest $request, WeatherApiClientInterface $client): AdminActivitiesController {
		$cache = $this->createMock(ICache::class);
		$cacheFactory = $this->createMock(ICacheFactory::class);
		$cacheFactory->method('createDistributed')->willReturn($cache);

		$logger = $this->createMock(LoggerInterface::class);
		$schemaService = new DrfSchemaService($client, $cacheFactory, $logger);
		return new AdminActivitiesController('farm_intelligence_platform', $request, $schemaService, $client, $logger);
	}
}
