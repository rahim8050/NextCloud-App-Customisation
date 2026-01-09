<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

use InvalidArgumentException;
use OCP\IConfig;
use OCP\Security\ICrypto;

final class AppConfig {
	public const APP_ID = 'weather_apis';

	private const MISSING_VALUE = '__weather_apis_missing__';
	private const PLAIN_SECRET_PREFIX = 'plain:v1:';

	private const KEY_BASE_URL = 'baseUrl';
	private const KEY_CLIENT_ID = 'clientId';
	private const KEY_API_KEY = 'apiKey';
	private const KEY_HMAC_SECRET = 'hmacSecret';
	private const KEY_HMAC_SECRET_PREVIOUS = 'hmacSecretPrevious';
	private const KEY_HMAC_SECRET_PREVIOUS_EXPIRES_AT = 'hmacSecretPreviousExpiresAt';
	private const KEY_TIMEOUT_SECONDS = 'timeoutSeconds';
	private const KEY_DEV_ALLOW_HTTP = 'devAllowHttp';
	private const KEY_ALLOWLIST_HOSTS = 'allowlistHosts';

	private const LEGACY_BASE_URL = 'base_url';
	private const LEGACY_CLIENT_ID = 'hmac_client_id';
	private const LEGACY_API_KEY = 'api_key';
	private const LEGACY_HMAC_SECRET = 'hmac_secret';
	private const LEGACY_SIGNING_SECRET = 'signingSecret';
	private const LEGACY_TIMEOUT_SECONDS = 'timeout_seconds';
	private const LEGACY_DEV_ALLOW_HTTP = 'dev_allow_insecure_local_http';
	private const LEGACY_ALLOWLIST_HOSTS = 'dev_allowlist_hosts';
	private const LEGACY_ALLOWLIST_HOSTS_CAMEL = 'devAllowlistHosts';

	private const DEFAULT_TIMEOUT = 10;
	private const MIN_TIMEOUT = 1;
	private const MAX_TIMEOUT = 30;
	private const HMAC_ROTATION_WINDOW_SECONDS = 86400;

	public function __construct(
		private readonly IConfig $config,
		private readonly ICrypto $crypto,
	) {
	}

	public function getBaseUrl(): string {
		$value = trim($this->getValueWithLegacyFallback(
			self::KEY_BASE_URL,
			[self::LEGACY_BASE_URL],
			'',
		));
		return rtrim($value, '/');
	}

	public function getTimeoutSeconds(): int {
		$raw = $this->getValueWithLegacyFallback(
			self::KEY_TIMEOUT_SECONDS,
			[self::LEGACY_TIMEOUT_SECONDS],
			(string)self::DEFAULT_TIMEOUT,
		);
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
		return $this->getValueWithLegacyFallback(
			self::KEY_DEV_ALLOW_HTTP,
			[self::LEGACY_DEV_ALLOW_HTTP],
			'0',
		) === '1';
	}

	public function isDevAllowHttp(): bool {
		return $this->isDevAllowInsecureLocalHttp();
	}

	public function getDevAllowlistHosts(): string {
		return trim($this->getValueWithLegacyFallback(
			self::KEY_ALLOWLIST_HOSTS,
			[self::LEGACY_ALLOWLIST_HOSTS_CAMEL, self::LEGACY_ALLOWLIST_HOSTS],
			'',
		));
	}

	public function getAllowlistHosts(): string {
		return $this->getDevAllowlistHosts();
	}

	public function isAllowLocalRemoteServers(): bool {
		return $this->config->getSystemValueBool('allow_local_remote_servers', false);
	}

	public function isHmacDebugLoggingEnabled(): bool {
		$raw = getenv('WEATHER_APIS_HMAC_DEBUG');
		if ($raw === false) {
			return false;
		}

		$normalized = strtolower(trim($raw));
		return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
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
		$this->config->setAppValue(self::APP_ID, self::KEY_DEV_ALLOW_HTTP, $value ? '1' : '0');
	}

	public function setDevAllowHttp(bool $value): void {
		$this->setDevAllowInsecureLocalHttp($value);
	}

	public function setDevAllowlistHosts(string $value): void {
		$this->config->setAppValue(self::APP_ID, self::KEY_ALLOWLIST_HOSTS, trim($value));
	}

	public function setAllowlistHosts(string $value): void {
		$this->setDevAllowlistHosts($value);
	}

	public function hasApiKey(): bool {
		return $this->hasValue(self::KEY_API_KEY, [self::LEGACY_API_KEY]);
	}

	public function hasHmacSecret(): bool {
		return $this->hasValue(self::KEY_HMAC_SECRET, [self::LEGACY_SIGNING_SECRET, self::LEGACY_HMAC_SECRET]);
	}

	public function hasPreviousHmacSecret(): bool {
		$value = $this->getRawValue(self::KEY_HMAC_SECRET_PREVIOUS);
		return $value !== null && $value !== '';
	}

	public function getHmacSecretPreviousExpiresAt(): ?int {
		$value = $this->getRawValue(self::KEY_HMAC_SECRET_PREVIOUS_EXPIRES_AT);
		if ($value === null || $value === '') {
			return null;
		}

		$parsed = (int)$value;
		return $parsed > 0 ? $parsed : null;
	}

	public function getApiKey(): string {
		return $this->decodeStoredSecret(self::KEY_API_KEY, [self::LEGACY_API_KEY], 'API key');
	}

	public function setApiKey(string $value): void {
		$this->encryptAndStore(self::KEY_API_KEY, $value);
	}

	public function getClientId(): string {
		$value = trim($this->getValueWithLegacyFallback(
			self::KEY_CLIENT_ID,
			[self::LEGACY_CLIENT_ID],
			'',
		));
		if ($value === '') {
			throw new InvalidArgumentException('HMAC client ID is not configured.');
		}

		return $value;
	}

	public function setClientId(string $value): void {
		$this->config->setAppValue(self::APP_ID, self::KEY_CLIENT_ID, $value);
	}

	public function getHmacClientId(): string {
		return $this->getClientId();
	}

	public function getHmacSecret(): string {
		return $this->decodeStoredSecret(
			self::KEY_HMAC_SECRET,
			[self::LEGACY_SIGNING_SECRET, self::LEGACY_HMAC_SECRET],
			'HMAC secret',
		);
	}

	public function setHmacSecret(string $value): void {
		$this->encryptAndStore(self::KEY_HMAC_SECRET, $value);
	}

	public function rotateHmacSecret(string $value, ?int $now = null): void {
		if ($value === '') {
			return;
		}

		$current = $this->getRawValue(self::KEY_HMAC_SECRET);
		if ($current === null || $current === '') {
			foreach ([self::LEGACY_SIGNING_SECRET, self::LEGACY_HMAC_SECRET] as $legacyKey) {
				$legacy = $this->getRawValue($legacyKey);
				if ($legacy !== null && $legacy !== '') {
					$current = $legacy;
					break;
				}
			}
		}
		if ($current !== null && $current !== '') {
			$previous = null;
			try {
				$previous = $this->decodeStoredSecretValue($current, 'HMAC secret');
			} catch (InvalidArgumentException) {
				$previous = null;
			}

			if ($previous !== null && $previous !== '') {
				$this->encryptAndStore(self::KEY_HMAC_SECRET_PREVIOUS, $previous);
			}
			$expiresAt = ($now ?? time()) + self::HMAC_ROTATION_WINDOW_SECONDS;
			$this->config->setAppValue(self::APP_ID, self::KEY_HMAC_SECRET_PREVIOUS_EXPIRES_AT, (string)$expiresAt);
		}

		$this->encryptAndStore(self::KEY_HMAC_SECRET, $value);
	}

	/**
	 * @return list<string>
	 */
	public function getHmacSecretsForVerification(?int $now = null): array {
		$secrets = [];
		try {
			$secrets[] = $this->getHmacSecret();
		} catch (InvalidArgumentException) {
			// no current secret configured yet
		}

		$previous = $this->getRawValue(self::KEY_HMAC_SECRET_PREVIOUS);
		$expiresRaw = $this->getRawValue(self::KEY_HMAC_SECRET_PREVIOUS_EXPIRES_AT);
		if ($previous !== null && $previous !== '' && $expiresRaw !== null && $expiresRaw !== '') {
			$expiresAt = (int)$expiresRaw;
			if ($expiresAt > ($now ?? time())) {
				$secrets[] = $this->decodeStoredSecretValue($previous, 'HMAC previous secret');
			} else {
				$this->clearPreviousHmacSecret();
			}
		}

		if ($secrets === []) {
			throw new InvalidArgumentException('HMAC secret is not configured.');
		}

		return array_values(array_unique($secrets));
	}

	public function migrateLegacyConfig(): void {
		$this->migrateLegacyValue(self::KEY_BASE_URL, [self::LEGACY_BASE_URL], static fn (string $value): string => rtrim(trim($value), '/'));
		$this->migrateLegacyValue(self::KEY_CLIENT_ID, [self::LEGACY_CLIENT_ID]);
		$this->migrateLegacyValue(self::KEY_TIMEOUT_SECONDS, [self::LEGACY_TIMEOUT_SECONDS]);
		$this->migrateLegacyValue(self::KEY_DEV_ALLOW_HTTP, [self::LEGACY_DEV_ALLOW_HTTP]);
		$this->migrateLegacyValue(self::KEY_ALLOWLIST_HOSTS, [self::LEGACY_ALLOWLIST_HOSTS_CAMEL, self::LEGACY_ALLOWLIST_HOSTS], 'trim');

		$this->migrateEncryptedSecret(self::KEY_API_KEY, [self::LEGACY_API_KEY]);
		$this->migrateEncryptedSecret(self::KEY_HMAC_SECRET, [self::LEGACY_SIGNING_SECRET, self::LEGACY_HMAC_SECRET]);
		$this->normalizeEncryptedSecret(self::KEY_HMAC_SECRET_PREVIOUS);
	}

	private function decryptSecret(string $canonicalKey, array $legacyKeys, string $label): string {
		$value = $this->getRawValue($canonicalKey);
		if ($value === null) {
			foreach ($legacyKeys as $legacyKey) {
				$legacy = $this->getRawValue($legacyKey);
				if ($legacy !== null && $legacy !== '') {
					$value = $legacy;
					break;
				}
			}
		}

		if ($value === null || $value === '') {
			throw new InvalidArgumentException($label . ' is not configured.');
		}

		try {
			return $this->crypto->decrypt($value);
		} catch (\Exception $ex) {
			throw new InvalidArgumentException(sprintf('%s could not be decrypted.', $label), 0, $ex);
		}
	}

	private function decodeStoredSecret(string $canonicalKey, array $legacyKeys, string $label): string {
		$value = $this->getRawValue($canonicalKey);
		if ($value === null) {
			foreach ($legacyKeys as $legacyKey) {
				$legacy = $this->getRawValue($legacyKey);
				if ($legacy !== null && $legacy !== '') {
					$value = $legacy;
					break;
				}
			}
		}

		if ($value === null || $value === '') {
			throw new InvalidArgumentException($label . ' is not configured.');
		}

		return $this->decodeStoredSecretValue($value, $label);
	}

	private function decodeStoredSecretValue(string $value, string $label): string {
		if (str_starts_with($value, self::PLAIN_SECRET_PREFIX)) {
			return substr($value, strlen(self::PLAIN_SECRET_PREFIX));
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

	private function getRawValue(string $key): ?string {
		$value = $this->config->getAppValue(self::APP_ID, $key, self::MISSING_VALUE);
		if ($value === self::MISSING_VALUE) {
			return null;
		}

		return $value;
	}

	private function getValueWithLegacyFallback(string $canonicalKey, array $legacyKeys, string $default): string {
		$canonical = $this->getRawValue($canonicalKey);
		if ($canonical !== null) {
			return $canonical;
		}

		foreach ($legacyKeys as $legacyKey) {
			$legacy = $this->getRawValue($legacyKey);
			if ($legacy !== null) {
				return $legacy;
			}
		}

		return $default;
	}

	private function hasValue(string $canonicalKey, array $legacyKeys): bool {
		$value = $this->getRawValue($canonicalKey);
		if ($value !== null) {
			return $value !== '';
		}

		foreach ($legacyKeys as $legacyKey) {
			$legacy = $this->getRawValue($legacyKey);
			if ($legacy !== null && $legacy !== '') {
				return true;
			}
		}

		return false;
	}

	private function migrateLegacyValue(
		string $canonicalKey,
		array $legacyKeys,
		callable|string|null $normalizer = null,
	): void {
		if ($this->getRawValue($canonicalKey) !== null) {
			return;
		}

		foreach ($legacyKeys as $legacyKey) {
			$legacy = $this->getRawValue($legacyKey);
			if ($legacy === null) {
				continue;
			}

			$value = $legacy;
			if ($normalizer !== null) {
				if (is_string($normalizer)) {
					$value = $normalizer($legacy);
				} else {
					$value = $normalizer($legacy);
				}
			}

			$this->config->setAppValue(self::APP_ID, $canonicalKey, $value);
			return;
		}
	}

	private function migrateEncryptedSecret(string $canonicalKey, array $legacyKeys): void {
		$canonical = $this->getRawValue($canonicalKey);
		if ($canonical !== null) {
			if ($canonical !== '' && str_starts_with($canonical, self::PLAIN_SECRET_PREFIX)) {
				$plain = substr($canonical, strlen(self::PLAIN_SECRET_PREFIX));
				$this->encryptAndStore($canonicalKey, $plain);
			}
			return;
		}

		foreach ($legacyKeys as $legacyKey) {
			$legacy = $this->getRawValue($legacyKey);
			if ($legacy === null || $legacy === '') {
				continue;
			}

			if (str_starts_with($legacy, self::PLAIN_SECRET_PREFIX)) {
				$plain = substr($legacy, strlen(self::PLAIN_SECRET_PREFIX));
				$this->encryptAndStore($canonicalKey, $plain);
			} else {
				$this->config->setAppValue(self::APP_ID, $canonicalKey, $legacy);
			}
			return;
		}
	}

	private function normalizeEncryptedSecret(string $key): void {
		$value = $this->getRawValue($key);
		if ($value === null || $value === '') {
			return;
		}

		if (str_starts_with($value, self::PLAIN_SECRET_PREFIX)) {
			$plain = substr($value, strlen(self::PLAIN_SECRET_PREFIX));
			$this->encryptAndStore($key, $plain);
		}
	}

	private function clearPreviousHmacSecret(): void {
		$this->config->setAppValue(self::APP_ID, self::KEY_HMAC_SECRET_PREVIOUS, '');
		$this->config->setAppValue(self::APP_ID, self::KEY_HMAC_SECRET_PREVIOUS_EXPIRES_AT, '');
	}
}
