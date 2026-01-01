<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Controller;

use OCA\WeatherApis\Controller\AdminConfigController;
use OCA\WeatherApis\Service\AppConfig;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Security\ICrypto;
use PHPUnit\Framework\TestCase;

final class AdminConfigControllerTest extends TestCase {
	public function testGenerateCredentialsSetsClientIdAndRotatesSecret(): void {
		$storage = [
			'hmacSecret' => 'encrypted:old-secret',
		];
		$appConfig = $this->createAppConfig($storage);
		$controller = $this->createController($appConfig);

		$response = $controller->generateCredentials();
		$this->assertInstanceOf(JSONResponse::class, $response);
		$data = $this->decodeResponse($response);
		$this->assertTrue($data['ok']);
		$this->assertNotSame('', $data['clientId']);
		$this->assertNotSame('', $data['hmacSecret']);

		$this->assertSame($data['clientId'], $storage['clientId']);
		$this->assertSame('encrypted:' . $data['hmacSecret'], $storage['hmacSecret']);
		$this->assertSame('encrypted:old-secret', $storage['hmacSecretPrevious']);
		$this->assertNotSame('', $storage['hmacSecretPreviousExpiresAt']);
	}

	public function testGenerateCredentialsKeepsExistingClientId(): void {
		$storage = [
			'clientId' => 'existing-client',
		];
		$appConfig = $this->createAppConfig($storage);
		$controller = $this->createController($appConfig);

		$response = $controller->generateCredentials();
		$data = $this->decodeResponse($response);

		$this->assertSame('existing-client', $data['clientId']);
		$this->assertSame('existing-client', $storage['clientId']);
		$this->assertSame('encrypted:' . $data['hmacSecret'], $storage['hmacSecret']);
	}

	public function testRotateHmacSetsPreviousSecret(): void {
		$storage = [
			'hmacSecret' => 'encrypted:old-secret',
		];
		$appConfig = $this->createAppConfig($storage);
		$controller = $this->createController($appConfig);

		$response = $controller->rotateHmac();
		$data = $this->decodeResponse($response);

		$this->assertTrue($data['ok']);
		$this->assertSame('encrypted:' . $data['hmacSecret'], $storage['hmacSecret']);
		$this->assertSame('encrypted:old-secret', $storage['hmacSecretPrevious']);
		$this->assertNotSame('', $storage['hmacSecretPreviousExpiresAt']);
	}

	public function testConfigResponseOmitsSecrets(): void {
		$storage = [
			'baseUrl' => 'https://example.com',
			'clientId' => 'client-id',
			'timeoutSeconds' => '12',
			'devAllowHttp' => '1',
			'allowlistHosts' => 'host1',
			'apiKey' => 'encrypted:api',
			'hmacSecret' => 'encrypted:secret',
			'hmacSecretPrevious' => 'encrypted:previous',
			'hmacSecretPreviousExpiresAt' => '1700000000',
		];
		$appConfig = $this->createAppConfig($storage);
		$controller = $this->createController($appConfig);

		$response = $controller->getConfig();
		$data = $this->decodeResponse($response);

		$this->assertSame('https://example.com', $data['baseUrl']);
		$this->assertSame('client-id', $data['clientId']);
		$this->assertSame(12, $data['timeoutSeconds']);
		$this->assertTrue($data['devAllowHttp']);
		$this->assertSame('host1', $data['allowlistHosts']);
		$this->assertTrue($data['hasApiKey']);
		$this->assertTrue($data['hasHmacSecret']);
		$this->assertTrue($data['hmacRotation']['hasPrevious']);
		$this->assertSame(1700000000, $data['hmacRotation']['previousExpiresAt']);

		$this->assertArrayNotHasKey('apiKey', $data);
		$this->assertArrayNotHasKey('hmacSecret', $data);
		$this->assertArrayNotHasKey('hmacSecretPrevious', $data);
	}

	public function testNonAdminIsForbidden(): void {
		$storage = [];
		$appConfig = $this->createAppConfig($storage);
		$controller = $this->createController($appConfig, false);

		$response = $controller->generateCredentials();

		$this->assertSame(403, $response->getStatus());
		$data = $this->decodeResponse($response);
		$this->assertFalse($data['ok']);
	}

	private function createController(AppConfig $appConfig, bool $isAdmin = true): AdminConfigController {
		$request = $this->createMock(IRequest::class);

		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('admin');

		$userSession = $this->createMock(IUserSession::class);
		$userSession->method('getUser')->willReturn($user);

		$groupManager = $this->createMock(IGroupManager::class);
		$groupManager->method('isAdmin')->willReturn($isAdmin);

		return new AdminConfigController('weather_apis', $request, $appConfig, $userSession, $groupManager);
	}

	private function createAppConfig(array &$storage): AppConfig {
		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')->willReturnCallback(
			function (string $appId, string $key, $default = '') use (&$storage) {
				return $storage[$key] ?? $default;
			},
		);
		$config->method('setAppValue')->willReturnCallback(
			function (string $appId, string $key, $value) use (&$storage): void {
				$storage[$key] = $value;
			},
		);
		$config->method('getSystemValueBool')->willReturn(false);

		$crypto = $this->createMock(ICrypto::class);
		$crypto->method('decrypt')->willReturnCallback(
			fn (string $value): string => str_starts_with($value, 'encrypted:') ? substr($value, 10) : $value,
		);
		$crypto->method('encrypt')->willReturnCallback(
			fn (string $value): string => 'encrypted:' . $value,
		);

		return new AppConfig($config, $crypto);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function decodeResponse(JSONResponse $response): array {
		$data = json_decode(
			json_encode($response->getData(), JSON_THROW_ON_ERROR),
			true,
			512,
			JSON_THROW_ON_ERROR,
		);

		/** @var array<string, mixed> $data */
		return $data;
	}
}
