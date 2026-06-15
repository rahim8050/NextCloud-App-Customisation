<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Tests\Unit\Service;

use OCA\FarmIntelligencePlatform\Service\AppConfig;
use OCP\IConfig;
use OCP\Security\ICrypto;
use PHPUnit\Framework\TestCase;

final class AppConfigTest extends TestCase {
	public function testApiKeyIsStoredEncrypted(): void {
		$storage = [];
		$appConfig = $this->createAppConfig($storage);

		$appConfig->setApiKey('plain-secret');

		$this->assertArrayHasKey('apiKey', $storage);
		$this->assertSame('encrypted:plain-secret', $storage['apiKey']);
	}

	public function testApiKeyUsesCanonicalBeforeLegacy(): void {
		$storage = [
			'apiKey' => 'encrypted:canonical',
			'api_key' => 'encrypted:legacy',
		];
		$appConfig = $this->createAppConfig($storage);

		$this->assertSame('canonical', $appConfig->getApiKey());
	}

	public function testApiKeyFallsBackToLegacyWhenCanonicalMissing(): void {
		$storage = [
			'api_key' => 'encrypted:legacy',
		];
		$appConfig = $this->createAppConfig($storage);

		$this->assertSame('legacy', $appConfig->getApiKey());
	}

	public function testBaseUrlIsNormalizedOnStore(): void {
		$storage = [];
		$appConfig = $this->createAppConfig($storage);

		$appConfig->setBaseUrl('https://example.com/');

		$this->assertSame('https://example.com', $storage['baseUrl']);
	}

	public function testTimeoutIsClamped(): void {
		$storage = [];
		$appConfig = $this->createAppConfig($storage);

		$appConfig->setTimeoutSeconds(0);
		$this->assertSame('1', $storage['timeoutSeconds']);

		$appConfig->setTimeoutSeconds(999);
		$this->assertSame('30', $storage['timeoutSeconds']);
	}

	public function testDevAllowSettingsPersist(): void {
		$storage = [];
		$appConfig = $this->createAppConfig($storage);

		$appConfig->setDevAllowHttp(true);
		$appConfig->setDevAllowHttp(false);
		$appConfig->setAllowlistHosts('  local.example.com  ');

		$this->assertSame('0', $storage['devAllowHttp']);
		$this->assertSame('local.example.com', $storage['allowlistHosts']);
	}

	public function testCanonicalValuesWinOverLegacy(): void {
		$storage = [
			'baseUrl' => 'https://canonical.example.com',
			'base_url' => 'https://legacy.example.com',
		];
		$appConfig = $this->createAppConfig($storage);

		$this->assertSame('https://canonical.example.com', $appConfig->getBaseUrl());
	}

	public function testLegacyFallbackIsUsedWhenCanonicalMissing(): void {
		$storage = [
			'base_url' => 'https://legacy.example.com',
		];
		$appConfig = $this->createAppConfig($storage);

		$this->assertSame('https://legacy.example.com', $appConfig->getBaseUrl());
	}

	public function testMigrationCopiesLegacyValuesAndIsIdempotent(): void {
		$storage = [
			'base_url' => 'https://legacy.example.com/',
			'hmac_client_id' => 'legacy-client',
			'timeout_seconds' => '12',
			'dev_allow_insecure_local_http' => '1',
			'dev_allowlist_hosts' => 'legacy-host',
			'api_key' => 'encrypted:legacy-api',
			'hmac_secret' => 'plain:v1:legacy-secret',
		];
		$appConfig = $this->createAppConfig($storage);

		$appConfig->migrateLegacyConfig();

		$this->assertSame('https://legacy.example.com', $storage['baseUrl']);
		$this->assertSame('legacy-client', $storage['clientId']);
		$this->assertSame('12', $storage['timeoutSeconds']);
		$this->assertSame('1', $storage['devAllowHttp']);
		$this->assertSame('legacy-host', $storage['allowlistHosts']);
		$this->assertSame('encrypted:legacy-api', $storage['apiKey']);
		$this->assertSame('encrypted:legacy-secret', $storage['hmacSecret']);

		$storage['base_url'] = 'https://changed.example.com';
		$appConfig->migrateLegacyConfig();

		$this->assertSame('https://legacy.example.com', $storage['baseUrl']);
	}

	public function testPlainSecretsAreReencryptedOnMigration(): void {
		$storage = [
			'apiKey' => 'plain:v1:api',
			'hmacSecret' => 'plain:v1:secret',
			'hmacSecretPrevious' => 'plain:v1:previous',
		];
		$appConfig = $this->createAppConfig($storage);

		$appConfig->migrateLegacyConfig();

		$this->assertSame('encrypted:api', $storage['apiKey']);
		$this->assertSame('encrypted:secret', $storage['hmacSecret']);
		$this->assertSame('encrypted:previous', $storage['hmacSecretPrevious']);
	}

	public function testHmacSecretRotationStoresPreviousAndExpires(): void {
		$storage = [
			'hmacSecret' => 'plain:v1:current',
		];
		$appConfig = $this->createAppConfig($storage);

		$appConfig->rotateHmacSecret('next-secret', 1000);

		$this->assertSame('encrypted:current', $storage['hmacSecretPrevious']);
		$this->assertSame('87400', $storage['hmacSecretPreviousExpiresAt']);
		$this->assertSame('encrypted:next-secret', $storage['hmacSecret']);

		$withinWindow = $appConfig->getHmacSecretsForVerification(2000);
		$this->assertSame(['next-secret', 'current'], $withinWindow);

		$afterWindow = $appConfig->getHmacSecretsForVerification(90000);
		$this->assertSame(['next-secret'], $afterWindow);
		$this->assertSame('', $storage['hmacSecretPrevious']);
		$this->assertSame('', $storage['hmacSecretPreviousExpiresAt']);
	}

	public function testRotationUsesLegacySecretWhenCanonicalMissing(): void {
		$storage = [
			'hmac_secret' => 'encrypted:legacy-secret',
		];
		$appConfig = $this->createAppConfig($storage);

		$appConfig->rotateHmacSecret('next-secret', 1000);

		$this->assertSame('encrypted:legacy-secret', $storage['hmacSecretPrevious']);
		$this->assertSame('87400', $storage['hmacSecretPreviousExpiresAt']);
		$this->assertSame('encrypted:next-secret', $storage['hmacSecret']);

		$withinWindow = $appConfig->getHmacSecretsForVerification(2000);
		$this->assertSame(['next-secret', 'legacy-secret'], $withinWindow);
	}

	private function createAppConfig(array &$storage): AppConfig {
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

		$crypto = $this->createMock(ICrypto::class);
		$crypto->method('encrypt')->willReturnCallback(
			fn (string $value): string => 'encrypted:' . $value,
		);
		$crypto->method('decrypt')->willReturnCallback(
			fn (string $value): string => str_starts_with($value, 'encrypted:') ? substr($value, 10) : $value,
		);

		return new AppConfig($config, $crypto);
	}
}
