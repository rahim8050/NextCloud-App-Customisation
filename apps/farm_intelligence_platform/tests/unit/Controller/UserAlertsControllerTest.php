<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Tests\Unit\Controller;

use OCA\FarmIntelligencePlatform\Controller\UserAlertsController;
use OCA\FarmIntelligencePlatform\Service\DrfSchemaService;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\AppFramework\Http\Attribute\UseSession;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class UserAlertsControllerTest extends TestCase {
	public function testListSubscriptionsHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_alerts_subscriptions_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_alerts_subscriptions_list',
				'method' => 'GET',
				'path' => '/api/v1/alerts/subscriptions/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/alerts/subscriptions/', [], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->listSubscriptions();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('ok', $data['status']);
		$this->assertSame([], $data['data']['results']);
	}

	public function testCreateSubscriptionForwardsBody(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('POST');
		$request->method('getParams')->willReturn(['station_id' => 'bbc']);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_alerts_subscriptions_create', 'request-id')
			->willReturn([
				'operationId' => 'v1_alerts_subscriptions_create',
				'method' => 'POST',
				'path' => '/api/v1/alerts/subscriptions/',
				'queryParams' => [],
				'bodyFields' => ['station_id' => ['type' => 'string', 'format' => null, 'required' => false, 'readOnly' => false]],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('POST', '/api/v1/alerts/subscriptions/', [], ['station_id' => 'bbc'], 'request-id')
			->willReturn(['payload' => ['id' => 'uuid'], 'statusCode' => 201]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->createSubscription();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('uuid', $data['data']['id']);
	}

	public function testGetSubscriptionEncodesSubId(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_alerts_subscriptions_retrieve', 'request-id')
			->willReturn([
				'operationId' => 'v1_alerts_subscriptions_retrieve',
				'method' => 'GET',
				'path' => '/api/v1/alerts/subscriptions/{sub_id}/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/alerts/subscriptions/sub-abc/', [], null, 'request-id')
			->willReturn(['payload' => ['id' => 'sub-abc'], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getSubscription('sub-abc');
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('sub-abc', $data['data']['id']);
	}

	public function testUpdateSubscriptionForwardsBody(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('PATCH');
		$request->method('getParams')->willReturn(['is_active' => false]);

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_alerts_subscriptions_update', 'request-id')
			->willReturn([
				'operationId' => 'v1_alerts_subscriptions_update',
				'method' => 'PATCH',
				'path' => '/api/v1/alerts/subscriptions/{sub_id}/',
				'queryParams' => [],
				'bodyFields' => ['is_active' => ['type' => 'boolean', 'format' => null, 'required' => false, 'readOnly' => false]],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('PATCH', '/api/v1/alerts/subscriptions/sub-abc/', [], ['is_active' => false], 'request-id')
			->willReturn(['payload' => ['id' => 'sub-abc', 'is_active' => false], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->updateSubscription('sub-abc');
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertFalse($data['data']['is_active']);
	}

	public function testDeleteSubscriptionEncodesSubId(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('DELETE');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_alerts_subscriptions_delete', 'request-id')
			->willReturn([
				'operationId' => 'v1_alerts_subscriptions_destroy',
				'method' => 'DELETE',
				'path' => '/api/v1/alerts/subscriptions/{sub_id}/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('DELETE', '/api/v1/alerts/subscriptions/sub-abc/', [], null, 'request-id')
			->willReturn(['payload' => null, 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->deleteSubscription('sub-abc');
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertNull($data['data']);
	}

	public function testListAlertsHitsCorrectEndpoint(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_alerts_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_alerts_alerts_list',
				'method' => 'GET',
				'path' => '/api/v1/alerts/alerts/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/alerts/alerts/', [], null, 'request-id')
			->willReturn(['payload' => ['results' => []], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->listAlerts();
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame([], $data['data']['results']);
	}

	public function testGetAlertEncodesAlertId(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_alerts_retrieve', 'request-id')
			->willReturn([
				'operationId' => 'v1_alerts_alerts_retrieve',
				'method' => 'GET',
				'path' => '/api/v1/alerts/alerts/{alert_id}/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->expects($this->once())
			->method('requestJsonWithStatus')
			->with('GET', '/api/v1/alerts/alerts/alert-xyz/', [], null, 'request-id')
			->willReturn(['payload' => ['id' => 'alert-xyz', 'title' => 'Storm'], 'statusCode' => 200]);

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->getAlert('alert-xyz');
		$data = json_decode($response->render(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertSame('Storm', $data['data']['title']);
	}

	public function testClassHasUseSessionAttribute(): void {
		$reflection = new \ReflectionClass(UserAlertsController::class);
		$this->assertNotEmpty($reflection->getAttributes(UseSession::class));
	}

	public function testWeatherApiExceptionSurfacesAsErrorResponse(): void {
		$request = $this->createMock(IRequest::class);
		$this->stubRequestHeaders($request);
		$request->method('getMethod')->willReturn('GET');

		$schemaService = $this->createMock(DrfSchemaService::class);
		$schemaService->method('getOperation')
			->with('v1_alerts_subscriptions_list', 'request-id')
			->willReturn([
				'operationId' => 'v1_alerts_subscriptions_list',
				'method' => 'GET',
				'path' => '/api/v1/alerts/subscriptions/',
				'queryParams' => [],
				'bodyFields' => [],
			]);

		$client = $this->createMock(WeatherApiClientInterface::class);
		$client->method('requestJsonWithStatus')
			->willThrowException(new WeatherApiException('backend_error', 'down'));

		$controller = $this->createController($request, $client, $schemaService);
		$response = $controller->listSubscriptions();
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

	private function createController(IRequest $request, WeatherApiClientInterface $client, DrfSchemaService $schemaService): UserAlertsController {
		$logger = $this->createMock(LoggerInterface::class);
		return new UserAlertsController('farm_intelligence_platform', $request, $client, $schemaService, $logger);
	}
}
