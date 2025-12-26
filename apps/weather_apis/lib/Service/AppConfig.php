<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

use InvalidArgumentException;
use OCP\IConfig;
use OCP\Security\ICrypto;

final class AppConfig {
	public const APP_ID = 'weather_apis';

	private const KEY_BASE_URL = 'base_url';
	private const KEY_TIMEOUT_SECONDS = 'timeout_seconds';
	private const KEY_API_KEY = 'api_key';
	private const KEY_HMAC_CLIENT_ID = 'hmac_client_id';
	private const KEY_HMAC_SECRET = 'hmac_secret';
	private const KEY_DEV_ALLOW_INSECURE_LOCAL_HTTP = 'dev_allow_insecure_local_http';
	private const KEY_DEV_ALLOWLIST_HOSTS = 'dev_allowlist_hosts';
	private const DEFAULT_TIMEOUT = 10;
	private const MIN_TIMEOUT = 1;
	private const MAX_TIMEOUT = 30;

	public function __construct(
		private readonly IConfig $config,
		private readonly ICrypto $crypto,
	) {
	}

	public function getBaseUrl(): string {
		$value = trim($this->config->getAppValue(self::APP_ID, self::KEY_BASE_URL, ''));
		return rtrim($value, '/');
	}

	public function getTimeoutSeconds(): int {
		$raw = $this->config->getAppValue(self::APP_ID, self::KEY_TIMEOUT_SECONDS, (string)self::DEFAULT_TIMEOUT);
		$value = (int)$raw;

		if ($value < self::MIN_TIMEOUT) {
			return self::MIN_TIMEOUT;
		}

		if ($value > self::MAX_TIMEOUT) {
			return self::MAX_TIMEOUT;
		}

		return $value;
	}

	public function isDevAllowInsecureLocalHttp(): bool {
		return $this->config->getAppValue(self::APP_ID, self::KEY_DEV_ALLOW_INSECURE_LOCAL_HTTP, '0') === '1';
	}

	public function getDevAllowlistHosts(): string {
		return trim($this->config->getAppValue(self::APP_ID, self::KEY_DEV_ALLOWLIST_HOSTS, ''));
	}

	public function setBaseUrl(string $value): void {
		$normalized = rtrim(trim($value), '/');
		$this->config->setAppValue(self::APP_ID, self::KEY_BASE_URL, $normalized);
	}

	public function setTimeoutSeconds(int $value): void {
		$clamped = max(self::MIN_TIMEOUT, min(self::MAX_TIMEOUT, $value));
		$this->config->setAppValue(self::APP_ID, self::KEY_TIMEOUT_SECONDS, (string)$clamped);
	}

	public function setDevAllowInsecureLocalHttp(bool $value): void {
		$this->config->setAppValue(self::APP_ID, self::KEY_DEV_ALLOW_INSECURE_LOCAL_HTTP, $value ? '1' : '0');
	}

	public function setDevAllowlistHosts(string $value): void {
		$this->config->setAppValue(self::APP_ID, self::KEY_DEV_ALLOWLIST_HOSTS, trim($value));
	}

	public function hasApiKey(): bool {
		return $this->config->getAppValue(self::APP_ID, self::KEY_API_KEY, '') !== '';
	}

	public function hasHmacSecret(): bool {
		return $this->config->getAppValue(self::APP_ID, self::KEY_HMAC_SECRET, '') !== '';
	}

	public function getApiKey(): string {
		return $this->decryptSecret(self::KEY_API_KEY, 'API key');
	}

	public function setApiKey(string $value): void {
		$this->encryptAndStore(self::KEY_API_KEY, $value);
	}

	public function getHmacClientId(): string {
		$value = trim($this->config->getAppValue(self::APP_ID, self::KEY_HMAC_CLIENT_ID, ''));
		if ($value === '') {
			throw new InvalidArgumentException('HMAC client ID is not configured.');
		}

		return $value;
	}

	public function setHmacClientId(string $value): void {
		$this->config->setAppValue(self::APP_ID, self::KEY_HMAC_CLIENT_ID, $value);
	}

	public function setClientId(string $value): void {
		$this->setHmacClientId($value);
	}

	public function getHmacSecret(): string {
		return $this->decryptSecret(self::KEY_HMAC_SECRET, 'HMAC secret');
	}

	public function setHmacSecret(string $value): void {
		$this->encryptAndStore(self::KEY_HMAC_SECRET, $value);
	}

	private function decryptSecret(string $key, string $label): string {
		$value = $this->config->getAppValue(self::APP_ID, $key, '');
		if ($value === '') {
			throw new InvalidArgumentException($label . ' is not configured.');
		}

		try {
			return $this->crypto->decrypt($value);
		} catch (\Exception $ex) {
			throw new InvalidArgumentException(sprintf('%s could not be decrypted.', $label), 0, $ex);
		}
	}

	private function encryptAndStore(string $key, string $value): void {
		$encrypted = $this->crypto->encrypt($value);
		$this->config->setAppValue(self::APP_ID, $key, $encrypted);
	}
}
