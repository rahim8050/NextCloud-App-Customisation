<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Controller;

use OCA\WeatherApis\Controller\AdminConfigController;
use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Service\IntegrationConfig;
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
	public function testGenerateCredentialsSetsClientIdAndSecret(): void {
		$storage = [];
		$controller = $this->createController($storage);

		$response = $controller->generateCredentials();
		$this->assertInstanceOf(JSONResponse::class, $response);
		$data = $this->decodeResponse($response);
		$this->assertSame('ok', $data['status']);
		$this->assertTrue($data['ok']);
		$this->assertSame('Generated credentials. Shown once.', $data['message']);
		$this->assertNotSame('', $data['clientId']);
		$this->assertNotSame('', $data['hmacSecret']);
		$this->assertNotFalse(base64_decode($data['hmacSecret'], true));

		$this->assertSame($data['clientId'], $storage['INTEGRATION_HMAC_CLIENT_ID']);
		$this->assertSame(
			'encrypted:' . json_encode([$data['clientId'] => $data['hmacSecret']], JSON_THROW_ON_ERROR),
			$storage['INTEGRATION_HMAC_CLIENTS_JSON'],
		);

		$headers = $this->getResponseHeaders($response);
		$this->assertSame('no-store', $headers['Cache-Control'] ?? '');
	}

	public function testGenerateCredentialsKeepsExistingClientId(): void {
		$storage = [
			'INTEGRATION_HMAC_CLIENT_ID' => 'existing-client',
		];
		$controller = $this->createController($storage);

		$response = $controller->generateCredentials();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertSame('Generated credentials. Shown once.', $data['message']);
		$this->assertSame('existing-client', $data['clientId']);
		$this->assertSame('existing-client', $storage['INTEGRATION_HMAC_CLIENT_ID']);
		$this->assertSame(
			'encrypted:' . json_encode([$data['clientId'] => $data['hmacSecret']], JSON_THROW_ON_ERROR),
			$storage['INTEGRATION_HMAC_CLIENTS_JSON'],
		);
	}

	public function testRotateHmacSetsSecret(): void {
		$storage = [];
		$controller = $this->createController($storage);

		$response = $controller->rotateHmac();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertTrue($data['ok']);
		$this->assertSame('Rotated secret. Shown once.', $data['message']);
		$this->assertNotSame('', $data['clientId']);
		$this->assertNotFalse(base64_decode($data['hmacSecret'], true));
		$this->assertSame($data['clientId'], $storage['INTEGRATION_HMAC_CLIENT_ID']);
		$this->assertSame(
			'encrypted:' . json_encode([$data['clientId'] => $data['hmacSecret']], JSON_THROW_ON_ERROR),
			$storage['INTEGRATION_HMAC_CLIENTS_JSON'],
		);

		$headers = $this->getResponseHeaders($response);
		$this->assertSame('no-store', $headers['Cache-Control'] ?? '');
	}

	public function testConfigResponseOmitsSecrets(): void {
		$storage = [
			'baseUrl' => 'https://example.com',
			'INTEGRATION_HMAC_CLIENT_ID' => 'client-id',
			'timeoutSeconds' => '12',
			'devAllowHttp' => '1',
			'allowlistHosts' => 'host1',
			'apiKey' => 'encrypted:api',
			'INTEGRATION_HMAC_CLIENTS_JSON' => 'encrypted:{"client-id":"c2VjcmV0"}',
		];
		$controller = $this->createController($storage);

		$response = $controller->getConfig();
		$data = $this->decodeResponse($response);

		$this->assertSame('https://example.com', $data['baseUrl']);
		$this->assertSame('client-id', $data['clientId']);
		$this->assertSame(12, $data['timeoutSeconds']);
		$this->assertTrue($data['devAllowHttp']);
		$this->assertSame('host1', $data['allowlistHosts']);
		$this->assertTrue($data['hasApiKey']);
		$this->assertTrue($data['hasHmacSecret']);
		$this->assertFalse($data['hmacRotation']['hasPrevious']);
		$this->assertNull($data['hmacRotation']['previousExpiresAt']);
		$this->assertTrue($data['integrationStatus']['ok']);

		$this->assertArrayNotHasKey('apiKey', $data);
		$this->assertArrayNotHasKey('hmacSecret', $data);
		$this->assertArrayNotHasKey('hmacSecretPrevious', $data);
	}

	public function testNonAdminIsForbidden(): void {
		$storage = [];
		$controller = $this->createController($storage, false);

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
		$storage = [
			'INTEGRATION_HMAC_CLIENT_ID' => 'client-id',
			'INTEGRATION_HMAC_CLIENTS_JSON' => 'encrypted:{"client-id":"c2VjcmV0"}',
		];

		$request = $this->createMock(IRequest::class);
		$request->method('getHeader')->with('X-Request-Id')->willReturn('request-id');

		$controller = $this->createController($storage, true, $request);

		$response = $controller->testConnection();
		$data = $this->decodeResponse($response);

		$this->assertSame('ok', $data['status']);
		$this->assertTrue($data['ok']);
		$this->assertSame('Integration HMAC configuration is valid.', $data['message']);
		$this->assertSame(true, $data['data']['ok']);
		$this->assertSame(false, $data['data']['legacyPresent']);
	}

	public function testTestConnectionRejectsNonAdmin(): void {
		$storage = [];
		$controller = $this->createController($storage, false);

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

	public function testTestConnectionReturnsLegacyBlocked(): void {
		$storage = [
			'hmacSecret' => 'encrypted:legacy',
		];
		$controller = $this->createController($storage, true);

		$response = $controller->testConnection();
		$data = $this->decodeResponse($response);

		$this->assertSame('error', $data['status']);
		$this->assertSame('blocked_legacy_present', $data['error']['code']);
	}

	private function createController(
		array &$storage,
		bool $isAdmin = true,
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

		$logger = $this->createMock(LoggerInterface::class);
		$appConfig = $this->createAppConfig($storage);
		$integrationConfig = $this->createIntegrationConfig($storage);

		return new AdminConfigController(
			'weather_apis',
			$request,
			$appConfig,
			$integrationConfig,
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

	private function createIntegrationConfig(array &$storage): IntegrationConfig {
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
		$config->method('getSystemValue')->willReturn(null);

		$crypto = $this->createMock(ICrypto::class);
		$crypto->method('decrypt')->willReturnCallback(
			fn (string $value): string => str_starts_with($value, 'encrypted:') ? substr($value, 10) : $value,
		);
		$crypto->method('encrypt')->willReturnCallback(
			fn (string $value): string => 'encrypted:' . $value,
		);

		return new IntegrationConfig($config, $crypto);
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
