<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Controller;

use OCA\WeatherApis\Controller\SettingsController;
use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Service\IntegrationConfig;
use OCA\WeatherApis\Service\UrlValidator;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Security\ICrypto;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class SettingsControllerTest extends TestCase {
	public function testAdminCanSaveSettings(): void {
		$params = [
			'baseUrl' => 'https://example.com/',
			'clientId' => 'client-id',
			'timeoutSeconds' => '15',
			'devAllowHttp' => true,
			'allowlistHosts' => 'host1',
			'apiKey' => 'new-key',
			'hmacSecret' => 'bmV3LXNlY3JldA==',
		];

		$request = $this->createRequest($params);
		$storage = [
			'timeoutSeconds' => '10',
			'devAllowHttp' => '0',
			'allowlistHosts' => '',
			'apiKey' => 'encrypted:existing',
			'INTEGRATION_HMAC_CLIENT_ID' => 'client-id',
			'INTEGRATION_HMAC_CLIENTS_JSON' => 'encrypted:{"client-id":"c2VjcmV0"}',
		];

		$validator = new UrlValidator(fn (): array => ['93.184.216.34']);

		$controller = $this->createController($request, $storage, $validator, true);
		$response = $controller->saveAdmin();
		$this->assertInstanceOf(JSONResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame([
			'status' => 'ok',
			'ok' => true,
			'message' => 'Settings saved.',
		], $response->getData());

		$this->assertSame('https://example.com', $storage['baseUrl']);
		$this->assertSame('client-id', $storage['INTEGRATION_HMAC_CLIENT_ID']);
		$this->assertSame('15', $storage['timeoutSeconds']);
		$this->assertSame('1', $storage['devAllowHttp']);
		$this->assertSame('host1', $storage['allowlistHosts']);
		$this->assertSame('encrypted:new-key', $storage['apiKey']);
		$this->assertSame(
			'encrypted:' . json_encode(['client-id' => 'bmV3LXNlY3JldA=='], JSON_THROW_ON_ERROR),
			$storage['INTEGRATION_HMAC_CLIENTS_JSON'],
		);
	}

	public function testSaveAdminReturnsOkContract(): void {
		$request = $this->createRequest([
			'baseUrl' => 'https://example.com',
			'clientId' => 'client-id',
			'timeoutSeconds' => '15',
			'devAllowHttp' => false,
			'allowlistHosts' => '',
		]);

		$storage = [
			'timeoutSeconds' => '10',
			'devAllowHttp' => '0',
			'allowlistHosts' => '',
		];
		$validator = new UrlValidator(fn (): array => ['93.184.216.34']);

		$controller = $this->createController($request, $storage, $validator, true);
		$response = $controller->saveAdmin();

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$data = json_decode(
			json_encode($response->getData(), JSON_THROW_ON_ERROR),
			true,
			512,
			JSON_THROW_ON_ERROR,
		);
		/** @var array<string, mixed> $data */
		$this->assertSame('ok', $data['status'] ?? null);
		$this->assertTrue($data['ok'] ?? false);
	}

	public function testEmptySecretsAreNotWritten(): void {
		$params = [
			'baseUrl' => 'https://example.com',
			'clientId' => 'client-id',
			'timeoutSeconds' => '15',
			'devAllowHttp' => false,
			'allowlistHosts' => '',
			'apiKey' => '',
			'hmacSecret' => '',
		];

		$request = $this->createRequest($params);
		$storage = [
			'timeoutSeconds' => '10',
			'devAllowHttp' => '0',
			'allowlistHosts' => '',
			'apiKey' => 'encrypted:existing',
			'INTEGRATION_HMAC_CLIENT_ID' => 'client-id',
			'INTEGRATION_HMAC_CLIENTS_JSON' => 'encrypted:{"client-id":"c2VjcmV0"}',
		];
		$validator = new UrlValidator(fn (): array => ['93.184.216.34']);

		$controller = $this->createController($request, $storage, $validator, true);
		$response = $controller->saveAdmin();
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame('encrypted:existing', $storage['apiKey']);
		$this->assertSame('encrypted:{"client-id":"c2VjcmV0"}', $storage['INTEGRATION_HMAC_CLIENTS_JSON']);
	}

	public function testJsonPayloadUsesParamsArray(): void {
		$params = [
			'base_url' => 'https://example.com/',
			'hmac_client_id' => 'client-id',
			'timeout_seconds' => 12,
			'dev_allow_insecure_local_http' => false,
			'dev_allowlist_hosts' => 'legacy-host',
			'api_key' => 'new-key',
			'hmac_secret' => 'bmV3LXNlY3JldA==',
		];

		$request = $this->createMock(IRequest::class);
		$request->method('getParam')->willReturnCallback(
			static fn (string $name, mixed $default = null): mixed => $default,
		);
		$request->method('getParams')->willReturn($params);
		$request->method('getHeader')->willReturn('');

		$storage = [
			'timeoutSeconds' => '10',
			'devAllowHttp' => '0',
			'allowlistHosts' => '',
			'apiKey' => 'encrypted:existing',
			'INTEGRATION_HMAC_CLIENT_ID' => 'client-id',
			'INTEGRATION_HMAC_CLIENTS_JSON' => 'encrypted:{"client-id":"c2VjcmV0"}',
		];
		$validator = new UrlValidator(fn (): array => ['93.184.216.34']);

		$controller = $this->createController($request, $storage, $validator, true);
		$response = $controller->saveAdmin();
		$this->assertInstanceOf(JSONResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame([
			'status' => 'ok',
			'ok' => true,
			'message' => 'Settings saved.',
		], $response->getData());

		$this->assertSame('https://example.com', $storage['baseUrl']);
		$this->assertSame('client-id', $storage['INTEGRATION_HMAC_CLIENT_ID']);
		$this->assertSame('12', $storage['timeoutSeconds']);
		$this->assertSame('0', $storage['devAllowHttp']);
		$this->assertSame('legacy-host', $storage['allowlistHosts']);
		$this->assertSame('encrypted:new-key', $storage['apiKey']);
		$this->assertSame(
			'encrypted:' . json_encode(['client-id' => 'bmV3LXNlY3JldA=='], JSON_THROW_ON_ERROR),
			$storage['INTEGRATION_HMAC_CLIENTS_JSON'],
		);
	}

	public function testInvalidBaseUrlYieldsBadRequest(): void {
		$request = $this->createRequest([
			'baseUrl' => 'ftp://example',
			'clientId' => 'client-id',
			'timeoutSeconds' => '15',
			'devAllowHttp' => false,
			'allowlistHosts' => '',
		]);

		$storage = [
			'timeoutSeconds' => '10',
			'devAllowHttp' => '0',
			'allowlistHosts' => '',
		];
		$validator = new UrlValidator(fn (): array => ['93.184.216.34']);

		$controller = $this->createController($request, $storage, $validator, true);
		$response = $controller->saveAdmin();
		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
		$data = json_decode(
			json_encode($response->getData(), JSON_THROW_ON_ERROR),
			true,
			512,
			JSON_THROW_ON_ERROR,
		);
		/** @var array<string, mixed> $data */
		$this->assertSame('invalid_argument', $data['error']['code']);
	}

	public function testInvalidBase64SecretIsRejected(): void {
		$request = $this->createRequest([
			'baseUrl' => 'https://example.com',
			'clientId' => 'client-id',
			'timeoutSeconds' => '15',
			'devAllowHttp' => false,
			'allowlistHosts' => '',
			'hmacSecret' => 'not-base64',
		]);

		$storage = [
			'timeoutSeconds' => '10',
			'devAllowHttp' => '0',
			'allowlistHosts' => '',
		];
		$validator = new UrlValidator(fn (): array => ['93.184.216.34']);

		$controller = $this->createController($request, $storage, $validator, true);
		$response = $controller->saveAdmin();
		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
		$data = json_decode(
			json_encode($response->getData(), JSON_THROW_ON_ERROR),
			true,
			512,
			JSON_THROW_ON_ERROR,
		);
		/** @var array<string, mixed> $data */
		$this->assertSame('invalid_argument', $data['error']['code']);
	}

	public function testTimeoutOutOfRangeIsRejected(): void {
		$request = $this->createRequest([
			'baseUrl' => 'https://example.com',
			'clientId' => 'client-id',
			'timeoutSeconds' => '999',
			'devAllowHttp' => false,
			'allowlistHosts' => '',
		]);

		$storage = [
			'timeoutSeconds' => '10',
			'devAllowHttp' => '0',
			'allowlistHosts' => '',
		];
		$validator = new UrlValidator(fn (): array => ['93.184.216.34']);

		$controller = $this->createController($request, $storage, $validator, true);
		$response = $controller->saveAdmin();
		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
	}

	public function testNonAdminIsForbidden(): void {
		$request = $this->createRequest([
			'baseUrl' => 'https://example.com',
			'clientId' => 'client-id',
			'timeoutSeconds' => '15',
			'devAllowHttp' => false,
			'allowlistHosts' => '',
		]);

		$storage = [
			'timeoutSeconds' => '10',
			'devAllowHttp' => '0',
			'allowlistHosts' => '',
		];
		$validator = new UrlValidator(fn (): array => ['93.184.216.34']);
		$controller = $this->createController($request, $storage, $validator, false);
		$response = $controller->saveAdmin();
		$this->assertSame(Http::STATUS_FORBIDDEN, $response->getStatus());
	}

	public function testXhtmlAcceptBuildsJsonResponse(): void {
		$request = $this->createRequest([]);
		$storage = [
			'timeoutSeconds' => '10',
			'devAllowHttp' => '0',
			'allowlistHosts' => '',
		];
		$validator = new UrlValidator(fn (): array => ['93.184.216.34']);

		$controller = $this->createController($request, $storage, $validator, true);
		$response = $controller->buildResponse(['ok' => true], 'xhtml+xml');

		$this->assertInstanceOf(JSONResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame(['ok' => true], $response->getData());
	}

	private function createAppConfig(array &$storage): AppConfig {
		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')->willReturnCallback(
			function (string $appId, string $key, mixed $default = '') use (&$storage): mixed {
				return $storage[$key] ?? $default;
			},
		);
		$config->method('getSystemValueBool')->willReturn(false);
		$config->method('getSystemValue')->willReturn(null);

		$config->method('setAppValue')->willReturnCallback(
			function (string $appId, string $key, mixed $value) use (&$storage): void {
				$storage[$key] = $value;
			},
		);

		$crypto = $this->createMock(ICrypto::class);
		$crypto->method('encrypt')->willReturnCallback(
			fn (string $value): string => 'encrypted:' . $value,
		);
		$crypto->method('decrypt')->willReturnCallback(
			fn (string $value): string => str_starts_with($value, 'encrypted:') ? substr($value, 10) : '',
		);

		return new AppConfig($config, $crypto);
	}

	private function createIntegrationConfig(array &$storage): IntegrationConfig {
		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')->willReturnCallback(
			function (string $appId, string $key, mixed $default = '') use (&$storage): mixed {
				return $storage[$key] ?? $default;
			},
		);
		$config->method('setAppValue')->willReturnCallback(
			function (string $appId, string $key, mixed $value) use (&$storage): void {
				$storage[$key] = $value;
			},
		);
		$config->method('getSystemValueBool')->willReturn(false);
		$config->method('getSystemValue')->willReturn(null);

		$crypto = $this->createMock(ICrypto::class);
		$crypto->method('encrypt')->willReturnCallback(
			fn (string $value): string => 'encrypted:' . $value,
		);
		$crypto->method('decrypt')->willReturnCallback(
			fn (string $value): string => str_starts_with($value, 'encrypted:') ? substr($value, 10) : '',
		);

		return new IntegrationConfig($config, $crypto);
	}

	private function createController(
		IRequest $request,
		array &$storage,
		UrlValidator $validator,
		bool $isAdmin,
	): SettingsController {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('admin');

		$userSession = $this->createMock(IUserSession::class);
		$userSession->method('getUser')->willReturn($user);

		$groupManager = $this->createMock(IGroupManager::class);
		$groupManager->method('isAdmin')->willReturn($isAdmin);

		$appConfig = $this->createAppConfig($storage);
		$integrationConfig = $this->createIntegrationConfig($storage);

		return new SettingsController(
			'weather_apis',
			$request,
			$appConfig,
			$integrationConfig,
			$validator,
			$userSession,
			$groupManager,
			$this->createMock(LoggerInterface::class),
		);
	}

	private function createRequest(array $params): IRequest {
		$request = $this->createMock(IRequest::class);
		$request->method('getParam')->willReturnCallback(
			fn (string $name, mixed $default = null): mixed => $params[$name] ?? $default,
		);
		$request->method('getParams')->willReturn($params);
		$request->method('getHeader')->willReturn('');

		return $request;
	}
}
