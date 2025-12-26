<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Controller;

use OCA\WeatherApis\Controller\SettingsController;
use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Service\UrlValidator;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
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
			'devAllowlistHosts' => 'host1',
			'apiKey' => 'new-key',
			'signingSecret' => 'new-secret',
		];

		$request = $this->createRequest($params);
		$storage = [
			'timeout_seconds' => '10',
			'dev_allow_insecure_local_http' => '0',
			'dev_allowlist_hosts' => '',
			'api_key' => 'encrypted:existing',
			'hmac_secret' => 'encrypted:existing-secret',
		];
		$config = $this->createAppConfig($storage);

		$validator = new UrlValidator(fn (): array => ['93.184.216.34']);

		$controller = $this->createController($request, $config, $validator, true);
		$response = $controller->saveAdmin();
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame(['ok' => true], $response->getData());

		$this->assertSame('https://example.com', $storage['base_url']);
		$this->assertSame('client-id', $storage['hmac_client_id']);
		$this->assertSame('15', $storage['timeout_seconds']);
		$this->assertSame('1', $storage['dev_allow_insecure_local_http']);
		$this->assertSame('host1', $storage['dev_allowlist_hosts']);
		$this->assertSame('encrypted:new-key', $storage['api_key']);
		$this->assertSame('encrypted:new-secret', $storage['hmac_secret']);
	}

	public function testEmptySecretsAreNotWritten(): void {
		$params = [
			'baseUrl' => 'https://example.com',
			'clientId' => 'client-id',
			'timeoutSeconds' => '15',
			'devAllowHttp' => false,
			'devAllowlistHosts' => '',
			'apiKey' => '',
			'signingSecret' => '',
		];

		$request = $this->createRequest($params);
		$storage = [
			'timeout_seconds' => '10',
			'dev_allow_insecure_local_http' => '0',
			'dev_allowlist_hosts' => '',
			'api_key' => 'encrypted:existing',
			'hmac_secret' => 'encrypted:existing-secret',
		];
		$config = $this->createAppConfig($storage);

		$validator = new UrlValidator(fn (): array => ['93.184.216.34']);

		$controller = $this->createController($request, $config, $validator, true);
		$response = $controller->saveAdmin();
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame('encrypted:existing', $storage['api_key']);
		$this->assertSame('encrypted:existing-secret', $storage['hmac_secret']);
	}

	public function testInvalidBaseUrlYieldsBadRequest(): void {
		$request = $this->createRequest([
			'baseUrl' => 'ftp://example',
			'clientId' => 'client-id',
			'timeoutSeconds' => '15',
			'devAllowHttp' => false,
			'devAllowlistHosts' => '',
		]);

		$storage = [
			'timeout_seconds' => '10',
			'dev_allow_insecure_local_http' => '0',
			'dev_allowlist_hosts' => '',
		];
		$config = $this->createAppConfig($storage);
		$validator = new UrlValidator(fn (): array => ['93.184.216.34']);

		$controller = $this->createController($request, $config, $validator, true);
		$response = $controller->saveAdmin();
		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
		$this->assertSame('invalid_argument', $response->getData()['error']['code']);
	}

	public function testTimeoutOutOfRangeIsRejected(): void {
		$request = $this->createRequest([
			'baseUrl' => 'https://example.com',
			'clientId' => 'client-id',
			'timeoutSeconds' => '999',
			'devAllowHttp' => false,
			'devAllowlistHosts' => '',
		]);

		$storage = [
			'timeout_seconds' => '10',
			'dev_allow_insecure_local_http' => '0',
			'dev_allowlist_hosts' => '',
		];
		$config = $this->createAppConfig($storage);
		$validator = new UrlValidator(fn (): array => ['93.184.216.34']);

		$controller = $this->createController($request, $config, $validator, true);
		$response = $controller->saveAdmin();
		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
	}

	public function testNonAdminIsForbidden(): void {
		$request = $this->createRequest([
			'baseUrl' => 'https://example.com',
			'clientId' => 'client-id',
			'timeoutSeconds' => '15',
			'devAllowHttp' => false,
			'devAllowlistHosts' => '',
		]);

		$storage = [
			'timeout_seconds' => '10',
			'dev_allow_insecure_local_http' => '0',
			'dev_allowlist_hosts' => '',
		];
		$config = $this->createAppConfig($storage);
		$validator = new UrlValidator(fn (): array => ['93.184.216.34']);
		$controller = $this->createController($request, $config, $validator, false);
		$response = $controller->saveAdmin();
		$this->assertSame(Http::STATUS_FORBIDDEN, $response->getStatus());
	}

	private function createAppConfig(array &$storage): AppConfig {
		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')->willReturnCallback(
			fn (string $appId, string $key, $default = '') => $storage[$key] ?? $default,
		);

		$config->method('setAppValue')->willReturnCallback(
			function (string $appId, string $key, $value) use (&$storage): void {
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

	private function createController(
		IRequest $request,
		AppConfig $config,
		UrlValidator $validator,
		bool $isAdmin,
	): SettingsController {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('admin');

		$userSession = $this->createMock(IUserSession::class);
		$userSession->method('getUser')->willReturn($user);

		$groupManager = $this->createMock(IGroupManager::class);
		$groupManager->method('isAdmin')->willReturn($isAdmin);

		return new SettingsController(
			'weather_apis',
			$request,
			$config,
			$validator,
			$userSession,
			$groupManager,
			$this->createMock(LoggerInterface::class),
		);
	}

	private function createRequest(array $params): IRequest {
		$request = $this->createMock(IRequest::class);
		$request->method('getParam')->willReturnCallback(
			fn (string $name, $default = null) => $params[$name] ?? $default,
		);
		$request->method('getHeader')->willReturn('');

		return $request;
	}
}
