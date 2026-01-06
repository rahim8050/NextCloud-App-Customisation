<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Controller;

use OCA\WeatherApis\Controller\AdminConfigController;
use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Service\WeatherApiClientInterface;
use OCA\WeatherApis\Service\WeatherApiException;
use OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PasswordConfirmationRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Response;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Security\ICrypto;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

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
		$this->assertSame('ok', $data['status']);
		$this->assertTrue($data['ok']);
		$this->assertSame('Generated credentials. Shown once.', $data['message']);
		$this->assertNotSame('', $data['clientId']);
		$this->assertNotSame('', $data['hmacSecret']);
		$this->assertNotSame('old-secret', $data['hmacSecret']);

		$this->assertSame($data['clientId'], $storage['clientId']);
		$this->assertSame('encrypted:' . $data['hmacSecret'], $storage['hmacSecret']);
		$this->assertSame('encrypted:old-secret', $storage['hmacSecretPrevious']);
		$this->assertNotSame('', $storage['hmacSecretPreviousExpiresAt']);

		$headers = $this->getResponseHeaders($response);
		$this->assertSame('no-store', $headers['Cache-Control'] ?? '');
	}

	public function testGenerateCredentialsKeepsExistingClientId(): void {
		$storage = [
			'clientId' => 'existing-client',
		];
		$appConfig = $this->createAppConfig($storage);
		$controller = $this->createController($appConfig);

		$response = $controller->generateCredentials();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame('Generated credentials. Shown once.', $data['message']);
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

		$this->assertSame('ok', $data['status']);
		$this->assertTrue($data['ok']);
		$this->assertSame('Rotated secret. Shown once.', $data['message']);
		$this->assertNotSame('old-secret', $data['hmacSecret']);
		$this->assertSame('encrypted:' . $data['hmacSecret'], $storage['hmacSecret']);
		$this->assertSame('encrypted:old-secret', $storage['hmacSecretPrevious']);
		$this->assertNotSame('', $storage['hmacSecretPreviousExpiresAt']);

		$headers = $this->getResponseHeaders($response);
		$this->assertSame('no-store', $headers['Cache-Control'] ?? '');
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

	public function testCredentialEndpointsRequireAdminAndPasswordConfirmation(): void {
		$this->assertMethodHasAttribute('generateCredentials', AuthorizedAdminSetting::class);
		$this->assertMethodHasAttribute('generateCredentials', PasswordConfirmationRequired::class);
		$this->assertMethodHasAttribute('rotateHmac', AuthorizedAdminSetting::class);
		$this->assertMethodHasAttribute('rotateHmac', PasswordConfirmationRequired::class);
	}

	public function testCredentialEndpointsRequireCsrf(): void {
		$this->assertMethodLacksAttribute('generateCredentials', NoCSRFRequired::class);
		$this->assertMethodLacksAttribute('rotateHmac', NoCSRFRequired::class);
	}

	public function testConfigEndpointRequiresAdmin(): void {
		$this->assertMethodHasAttribute('getConfig', AuthorizedAdminSetting::class);
	}

	public function testTestConnectionReturnsOk(): void {
		$storage = [];
		$appConfig = $this->createAppConfig($storage);

		$request = $this->createMock(IRequest::class);
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->expects($this->once())
			->method('ping')
			->with('request-id');

		$controller = $this->createController($appConfig, true, $weatherApiClient, $request);

		$response = $controller->testConnection();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertTrue($data['ok']);
		$this->assertSame('Connection successful.', $data['message']);
		$this->assertSame(['ok' => true], $data['data']);
	}

	public function testTestConnectionRejectsNonAdmin(): void {
		$storage = [];
		$appConfig = $this->createAppConfig($storage);
		$controller = $this->createController($appConfig, false);

		$response = $controller->testConnection();

		$this->assertSame(403, $response->getStatus());
		$data = $this->decodeResponse($response);
		$this->assertSame('error', $data['status']);
		$this->assertSame('Admin access required.', $data['message']);
	}

	public function testTestConnectionRequiresAdmin(): void {
		$this->assertMethodHasAttribute('testConnection', AuthorizedAdminSetting::class);
		$this->assertMethodLacksAttribute('testConnection', PasswordConfirmationRequired::class);
		$this->assertMethodLacksAttribute('testConnection', NoCSRFRequired::class);
	}

	public function testTestConnectionReturnsBackendError(): void {
		$storage = [];
		$appConfig = $this->createAppConfig($storage);

		$weatherApiClient = $this->createMock(WeatherApiClientInterface::class);
		$weatherApiClient->method('ping')
			->willThrowException(new WeatherApiException('backend_timeout', 'Backend request failed.'));

		$controller = $this->createController($appConfig, true, $weatherApiClient);

		$response = $controller->testConnection();
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('Backend request failed.', $data['message']);
	}

	private function createController(
		AppConfig $appConfig,
		bool $isAdmin = true,
		?WeatherApiClientInterface $weatherApiClient = null,
		?IRequest $request = null,
	): AdminConfigController {
		$request = $request ?? $this->createMock(IRequest::class);
		$request->method('getHeader')->willReturn('');

		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('admin');

		$userSession = $this->createMock(IUserSession::class);
		$userSession->method('getUser')->willReturn($user);

		$groupManager = $this->createMock(IGroupManager::class);
		$groupManager->method('isAdmin')->willReturn($isAdmin);

		$weatherApiClient = $weatherApiClient ?? $this->createMock(WeatherApiClientInterface::class);
		$logger = $this->createMock(LoggerInterface::class);

		return new AdminConfigController(
			'weather_apis',
			$request,
			$appConfig,
			$weatherApiClient,
			$userSession,
			$groupManager,
			$logger,
		);
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

	/**
	 * @return array<string, mixed>
	 */
	private function getResponseHeaders(JSONResponse $response): array {
		$reflection = new \ReflectionProperty(Response::class, 'headers');
		$reflection->setAccessible(true);
		$headers = $reflection->getValue($response);

		return is_array($headers) ? $headers : [];
	}

	private function assertMethodHasAttribute(string $method, string $attributeClass): void {
		$reflection = new \ReflectionMethod(AdminConfigController::class, $method);
		$this->assertNotEmpty(
			$reflection->getAttributes($attributeClass),
			sprintf('%s is missing %s', $method, $attributeClass),
		);
	}

	private function assertMethodLacksAttribute(string $method, string $attributeClass): void {
		$reflection = new \ReflectionMethod(AdminConfigController::class, $method);
		$this->assertSame(
			[],
			$reflection->getAttributes($attributeClass),
			sprintf('%s should not have %s', $method, $attributeClass),
		);
	}
}
