<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Service;

use OCA\WeatherApis\Service\AppConfig;
use OCP\IConfig;
use OCP\Security\ICrypto;
use PHPUnit\Framework\TestCase;

final class AppConfigTest extends TestCase {
	public function testSecretsAreEncryptedBeforeStorage(): void {
		$config = $this->createMock(IConfig::class);
		$config->expects($this->once())
			->method('setAppValue')
			->with(AppConfig::APP_ID, 'api_key', 'encrypted-value');

		$crypto = $this->createMock(ICrypto::class);
		$crypto->expects($this->once())
			->method('encrypt')
			->with('plain-secret')
			->willReturn('encrypted-value');

		$appConfig = new AppConfig($config, $crypto);
		$appConfig->setApiKey('plain-secret');
	}

	public function testDecryptsStoredSecret(): void {
		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')->willReturnMap([
			[AppConfig::APP_ID, 'api_key', '', 'encrypted-secret'],
		]);

		$crypto = $this->createMock(ICrypto::class);
		$crypto->method('decrypt')->with('encrypted-secret')->willReturn('plain-secret');

		$appConfig = new AppConfig($config, $crypto);
		$this->assertSame('plain-secret', $appConfig->getApiKey());
	}

	public function testBaseUrlIsNormalizedOnStore(): void {
		$config = $this->createMock(IConfig::class);
		$config->expects($this->once())->method('setAppValue')->with(AppConfig::APP_ID, 'base_url', 'https://example.com');

		$crypto = $this->createMock(ICrypto::class);
		$appConfig = new AppConfig($config, $crypto);
		$appConfig->setBaseUrl('https://example.com/');
	}

	public function testTimeoutIsClamped(): void {
		$config = $this->createMock(IConfig::class);
		$config->expects($this->exactly(2))
			->method('setAppValue')
			->withConsecutive(
				[AppConfig::APP_ID, 'timeout_seconds', '1'],
				[AppConfig::APP_ID, 'timeout_seconds', '30'],
			);

		$crypto = $this->createMock(ICrypto::class);
		$appConfig = new AppConfig($config, $crypto);
		$appConfig->setTimeoutSeconds(0);
		$appConfig->setTimeoutSeconds(999);
	}

	public function testDevAllowSettingsPersist(): void {
		$config = $this->createMock(IConfig::class);
		$config->expects($this->exactly(3))
			->method('setAppValue')
			->withConsecutive(
				[AppConfig::APP_ID, 'dev_allow_insecure_local_http', '1'],
				[AppConfig::APP_ID, 'dev_allow_insecure_local_http', '0'],
				[AppConfig::APP_ID, 'dev_allowlist_hosts', 'local.example.com'],
			);

		$crypto = $this->createMock(ICrypto::class);
		$appConfig = new AppConfig($config, $crypto);
		$appConfig->setDevAllowInsecureLocalHttp(true);
		$appConfig->setDevAllowInsecureLocalHttp(false);
		$appConfig->setDevAllowlistHosts('  local.example.com  ');
	}

	public function testSecretFlagsReflectPresence(): void {
		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')->willReturnMap([
			[AppConfig::APP_ID, 'api_key', '', 'encrypted'],
			[AppConfig::APP_ID, 'hmac_secret', '', 'encrypted-secret'],
		]);

		$crypto = $this->createMock(ICrypto::class);
		$appConfig = new AppConfig($config, $crypto);
		$this->assertTrue($appConfig->hasApiKey());
		$this->assertTrue($appConfig->hasHmacSecret());
	}
}
