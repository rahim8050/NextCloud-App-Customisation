<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Tests\Unit\Controller;

use OCA\FarmIntelligencePlatform\Controller\AdminAlertsController;
use OCA\FarmIntelligencePlatform\Service\DrfSchemaService;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class AdminAlertsControllerTest extends TestCase {
	public function testBroadcastHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('POST');
		$request->method('getParams')->willReturn(['title' => 'Emergency']);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_alerts_admin_send', 'request-id')
			->willReturn([
				'operationId' => 'v1_alerts_alerts_admin_send_create',
				'method' => 'POST',
				'path' => '/api/v1/alerts/alerts/admin/send/',
				'queryParams' => [],
				'bodyFields' => ['title' => ['required' => true]],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('POST', '/api/v1/alerts/alerts/admin/send/', [], ['title' => 'Emergency'], 'request-id')
			->willReturn(['payload' => ['status' => 'sent'], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->broadcast();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('ok', $data['status']);
		$this->assertSame('sent', $data['data']['status']);
	}

	public function testBroadcastMethodHasAdminRequired(): void {
		$reflection = new \ReflectionMethod(AdminAlertsController::class, 'broadcast');
		$this->assertNotEmpty($reflection->getAttributes(AdminRequired::class));
	}

	public function testWeatherApiExceptionSurfacesAsErrorResponse(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('POST');
		$request->method('getParams')->willReturn([]);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_alerts_admin_send', 'request-id')
			->willReturn([
				'operationId' => 'v1_alerts_alerts_admin_send_create',
				'method' => 'POST',
				'path' => '/api/v1/alerts/alerts/admin/send/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willThrowException(new WeatherApiException('forbidden', 'no permission'));

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->broadcast();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('error', $data['status']);
		$this->assertSame('forbidden', $data['error']['code']);
	}

	private function stubRequestHeaders($request, string $requestId = 'request-id'): void {
		$request->method('getHeader')
			->willReturnCallback(static function (string $name) use ($requestId): string {
				return $name === 'X-Request-Id' ? $requestId : '';
			});
	}

	private function createController(IRequest $request, WeatherApiClientInterface $client, DrfSchemaService $schemaService): AdminAlertsController {
		$logger = $this->createMock(LoggerInterface::class);
		return new AdminAlertsController('farm_intelligence_platform', $request, $client, $schemaService, $logger);
	}
}
