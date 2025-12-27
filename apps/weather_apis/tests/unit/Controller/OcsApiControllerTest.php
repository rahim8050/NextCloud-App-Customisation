<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Controller;

use OCA\WeatherApis\Controller\OcsApiController;
use OCA\WeatherApis\Service\WeatherApiClientInterface;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class OcsApiControllerTest extends TestCase {
	public function testIntegrationWhoamiReturnsDataResponse(): void {
		$request = $this->createMock(IRequest::class);
		$request->method('getHeader')->willReturn('');

		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('admin');

		$userSession = $this->createMock(IUserSession::class);
		$userSession->method('getUser')->willReturn($user);

		$groupManager = $this->createMock(IGroupManager::class);
		$groupManager->method('isAdmin')->with('admin')->willReturn(true);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects(self::once())
			->method('whoami')
			->with(self::callback(fn ($id): bool => is_string($id) && $id !== ''))
			->willReturn(['integration' => 'ok']);

		$controller = new OcsApiController(
			'weather_apis',
			$request,
			$weatherApiClient,
			$userSession,
			$groupManager,
			$this->createMock(LoggerInterface::class),
		);

		$response = $controller->getIntegrationWhoami();

		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame([
			'status' => 'ok',
			'data' => ['integration' => 'ok'],
		], $response->getData());
	}
}
