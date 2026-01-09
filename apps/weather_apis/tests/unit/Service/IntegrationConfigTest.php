<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Service;

use OCA\WeatherApis\Service\IntegrationConfig;
use OCA\WeatherApis\Service\IntegrationConfigException;
use OCP\IConfig;
use OCP\Security\ICrypto;
use PHPUnit\Framework\TestCase;

final class IntegrationConfigTest extends TestCase {
	private function loadHmacFixture(): array {
		$path = dirname(__DIR__, 2) . '/fixtures/hmac_test_vector.json';
		$raw = file_get_contents($path);
		$this->assertNotFalse($raw);
		$decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
		$this->assertIsArray($decoded);

		return $decoded;
	}

	public function testValidConfigLoadsSecretBytes(): void {
		$storage = [
			'INTEGRATION_HMAC_CLIENT_ID' => 'client-id',
			'INTEGRATION_HMAC_CLIENTS_JSON' => 'encrypted:{"client-id":"c2VjcmV0"}',
		];
		$config = $this->createIntegrationConfig($storage);

		$this->assertSame('client-id', $config->getClientId());
		$this->assertSame('secret', $config->getSecretBytes());
	}

	public function testMissingConfigThrows(): void {
		$storage = [];
		$config = $this->createIntegrationConfig($storage);

		$this->expectException(IntegrationConfigException::class);
		try {
			$config->getClientId();
		} catch (IntegrationConfigException $exception) {
			$this->assertSame('missing_config', $exception->getErrorCode());
			throw $exception;
		}
	}

	public function testBadJsonThrows(): void {
		$storage = [
			'INTEGRATION_HMAC_CLIENT_ID' => 'client-id',
			'INTEGRATION_HMAC_CLIENTS_JSON' => 'encrypted:{bad json',
		];
		$config = $this->createIntegrationConfig($storage);

		$this->expectException(IntegrationConfigException::class);
		try {
			$config->getSecretBytes();
		} catch (IntegrationConfigException $exception) {
			$this->assertSame('bad_json', $exception->getErrorCode());
			throw $exception;
		}
	}

	public function testUnknownClientThrows(): void {
		$storage = [
			'INTEGRATION_HMAC_CLIENT_ID' => 'client-id',
			'INTEGRATION_HMAC_CLIENTS_JSON' => 'encrypted:{"other-client":"c2VjcmV0"}',
		];
		$config = $this->createIntegrationConfig($storage);

		$this->expectException(IntegrationConfigException::class);
		try {
			$config->getSecretBytes();
		} catch (IntegrationConfigException $exception) {
			$this->assertSame('unknown_client', $exception->getErrorCode());
			throw $exception;
		}
	}

	public function testBadBase64Throws(): void {
		$storage = [
			'INTEGRATION_HMAC_CLIENT_ID' => 'client-id',
			'INTEGRATION_HMAC_CLIENTS_JSON' => 'encrypted:{"client-id":"not-base64"}',
		];
		$config = $this->createIntegrationConfig($storage);

		$this->expectException(IntegrationConfigException::class);
		try {
			$config->getSecretBytes();
		} catch (IntegrationConfigException $exception) {
			$this->assertSame('bad_base64', $exception->getErrorCode());
			throw $exception;
		}
	}

	public function testLegacyPresentBlocksWhenNewMissing(): void {
		$storage = [
			'hmacSecret' => 'encrypted:legacy',
		];
		$config = $this->createIntegrationConfig($storage);

		$this->expectException(IntegrationConfigException::class);
		try {
			$config->getClientId();
		} catch (IntegrationConfigException $exception) {
			$this->assertSame('blocked_legacy_present', $exception->getErrorCode());
			throw $exception;
		}
	}

	public function testFixtureSecretFingerprintMatches(): void {
		$fixture = $this->loadHmacFixture();
		$secret = base64_decode((string)$fixture['secret_b64'], true);
		$this->assertNotFalse($secret);
		$fingerprint = hash('sha256', $secret);
		$this->assertSame($fixture['expected_secret_sha256'], $fingerprint);
	}

	private function createIntegrationConfig(array &$storage, array $system = [], bool $legacyAllowed = false): IntegrationConfig {
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
		$config->method('getSystemValue')->willReturnCallback(
			function (string $key, $default = null) use ($system) {
				return $system[$key] ?? $default;
			},
		);
		$config->method('getSystemValueBool')->willReturnCallback(
			function (string $key, bool $default) use ($legacyAllowed): bool {
				return $legacyAllowed;
			},
		);

		$crypto = $this->createMock(ICrypto::class);
		$crypto->method('decrypt')->willReturnCallback(
			fn (string $value): string => str_starts_with($value, 'encrypted:') ? substr($value, 10) : $value,
		);
		$crypto->method('encrypt')->willReturnCallback(
			fn (string $value): string => 'encrypted:' . $value,
		);

		return new IntegrationConfig($config, $crypto);
	}
}
