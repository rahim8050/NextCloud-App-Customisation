<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Service;

use JsonException;
use OCP\IConfig;
use OCP\Security\ICrypto;

final class IntegrationConfig {
	private const APP_ID = 'farm_intelligence_platform';
	private const KEY_CLIENT_ID = 'INTEGRATION_HMAC_CLIENT_ID';
	private const KEY_CLIENTS_JSON = 'INTEGRATION_HMAC_CLIENTS_JSON';
	private const KEY_LEGACY_ALLOWED = 'INTEGRATION_LEGACY_CONFIG_ALLOWED';
	private const PLAIN_SECRET_PREFIX = 'plain:v1:';
	private const MISSING_VALUE = '__farm_intelligence_platform_missing__';

	private const LEGACY_KEYS = [
		'clientId',
		'hmacSecret',
		'hmacSecretPrevious',
		'hmacSecretPreviousExpiresAt',
		'hmac_client_id',
		'hmac_secret',
		'signingSecret',
	];

	public function __construct(
		private readonly IConfig $config,
		private readonly ICrypto $crypto,
	) {
	}

	public function getClientId(): string {
		$config = $this->requireConfig();
		return $config['clientId'];
	}

	public function getSecretBytes(): string {
		$config = $this->requireConfig();
		return $config['secretBytes'];
	}

	public function getSecretB64(): string {
		$config = $this->requireConfig();
		return $config['secretB64'];
	}

	public function getClientIdOrNull(): ?string {
		$clientId = $this->getRawClientId();
		return $clientId === '' ? null : $clientId;
	}

	public function getSecretB64OrNull(): ?string {
		try {
			return $this->getSecretB64();
		} catch (IntegrationConfigException) {
			return null;
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getStatus(): array {
		$legacyPresent = $this->hasLegacyConfig();
		$legacyAllowed = $this->isLegacyAllowed();
		$clientIdPresent = false;
		$clientsJsonPresent = false;

		try {
			$clientIdPresent = $this->getRawClientId() !== '';
			$clientsJsonPresent = $this->getRawClientsJson() !== '';
		} catch (IntegrationConfigException $exception) {
			return [
				'ok' => false,
				'code' => $exception->getErrorCode(),
				'message' => $exception->getMessage(),
				'legacyPresent' => $legacyPresent,
				'legacyAllowed' => $legacyAllowed,
				'clientIdPresent' => $clientIdPresent,
				'clientsJsonPresent' => $clientsJsonPresent,
			];
		}

		$status = [
			'ok' => false,
			'code' => 'missing_config',
			'message' => 'Integration HMAC configuration is missing.',
			'legacyPresent' => $legacyPresent,
			'legacyAllowed' => $legacyAllowed,
			'clientIdPresent' => $clientIdPresent,
			'clientsJsonPresent' => $clientsJsonPresent,
		];

		try {
			$this->requireConfig();
			$status['ok'] = true;
			$status['code'] = 'ok';
			$status['message'] = 'Integration HMAC configuration is valid.';
			if ($legacyPresent) {
				$status['warning'] = 'legacy_present';
			}
		} catch (IntegrationConfigException $exception) {
			$status['ok'] = false;
			$status['code'] = $exception->getErrorCode();
			$status['message'] = $exception->getMessage();
		}

		return $status;
	}

	public function setCredentials(string $clientId, string $secretB64): void {
		$clientId = trim($clientId);
		$secretB64 = trim($secretB64);
		if ($clientId === '') {
			throw new IntegrationConfigException('missing_config', 'INTEGRATION_HMAC_CLIENT_ID is required.');
		}

		$this->validateSecretB64($secretB64);

		$json = $this->encodeClientsJson([$clientId => $secretB64]);
		$encrypted = $this->crypto->encrypt($json);

		$this->config->setAppValue(self::APP_ID, self::KEY_CLIENT_ID, $clientId);
		$this->config->setAppValue(self::APP_ID, self::KEY_CLIENTS_JSON, $encrypted);
	}

	public function setClientId(string $clientId): void {
		$clientId = trim($clientId);
		if ($clientId === '') {
			throw new IntegrationConfigException('missing_config', 'INTEGRATION_HMAC_CLIENT_ID is required.');
		}

		$this->config->setAppValue(self::APP_ID, self::KEY_CLIENT_ID, $clientId);
	}

	public function validateSecretB64(string $secretB64): void {
		$secretB64 = trim($secretB64);
		if ($secretB64 === '') {
			throw new IntegrationConfigException('bad_base64', 'Secret must be valid base64.');
		}

		$decoded = base64_decode($secretB64, true);
		if ($decoded === false || $decoded === '') {
			throw new IntegrationConfigException('bad_base64', 'Secret must be valid base64.');
		}
	}

	/**
	 * @return array<string, string>
	 */
	private function requireConfig(): array {
		$legacyPresent = $this->hasLegacyConfig();
		$legacyAllowed = $this->isLegacyAllowed();

		$clientId = $this->getRawClientId();
		$clientsJson = $this->getRawClientsJson();

		if ($clientId === '' || $clientsJson === '') {
			if ($legacyPresent && !$legacyAllowed) {
				throw new IntegrationConfigException(
					'blocked_legacy_present',
					'Legacy integration keys are present. Remove them or set INTEGRATION_LEGACY_CONFIG_ALLOWED=1 while migrating.',
				);
			}
			throw new IntegrationConfigException(
				'missing_config',
				'INTEGRATION_HMAC_CLIENT_ID and INTEGRATION_HMAC_CLIENTS_JSON are required.',
			);
		}

		$clients = $this->parseClientsJson($clientsJson);
		if (!array_key_exists($clientId, $clients)) {
			throw new IntegrationConfigException(
				'unknown_client',
				'INTEGRATION_HMAC_CLIENT_ID is not present in INTEGRATION_HMAC_CLIENTS_JSON.',
			);
		}

		$secretB64 = $clients[$clientId];
		$decoded = base64_decode($secretB64, true);
		if ($decoded === false || $decoded === '') {
			throw new IntegrationConfigException(
				'bad_base64',
				'INTEGRATION_HMAC_CLIENTS_JSON contains an invalid base64 secret.',
			);
		}

		return [
			'clientId' => $clientId,
			'secretB64' => $secretB64,
			'secretBytes' => $decoded,
		];
	}

	/**
	 * @return array<string, string>
	 */
	private function parseClientsJson(string $raw): array {
		try {
			$decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException $exception) {
			throw new IntegrationConfigException(
				'bad_json',
				'INTEGRATION_HMAC_CLIENTS_JSON must be valid JSON.',
				$exception,
			);
		}

		if (!is_array($decoded)) {
			throw new IntegrationConfigException(
				'bad_json',
				'INTEGRATION_HMAC_CLIENTS_JSON must be a JSON object.',
			);
		}

		if ($decoded === []) {
			throw new IntegrationConfigException(
				'missing_config',
				'INTEGRATION_HMAC_CLIENTS_JSON must not be empty.',
			);
		}

		$clients = [];
		foreach ($decoded as $rawClientId => $rawSecret) {
			if (!is_string($rawClientId) || !is_string($rawSecret)) {
				throw new IntegrationConfigException(
					'bad_json',
					'INTEGRATION_HMAC_CLIENTS_JSON must map strings to strings.',
				);
			}

			$clientId = trim($rawClientId);
			$secretB64 = trim($rawSecret);
			if ($clientId === '' || $secretB64 === '') {
				throw new IntegrationConfigException(
					'bad_json',
					'INTEGRATION_HMAC_CLIENTS_JSON cannot contain empty keys or values.',
				);
			}
			if (array_key_exists($clientId, $clients)) {
				throw new IntegrationConfigException(
					'bad_json',
					'INTEGRATION_HMAC_CLIENTS_JSON contains duplicate client ids after trimming.',
				);
			}

			$clients[$clientId] = $secretB64;
		}

		return $clients;
	}

	private function encodeClientsJson(array $clients): string {
		try {
			return json_encode($clients, JSON_THROW_ON_ERROR);
		} catch (JsonException $exception) {
			throw new IntegrationConfigException(
				'bad_json',
				'Failed to encode integration config.',
				$exception,
			);
		}
	}

	private function getRawClientId(): string {
		$systemValue = $this->getSystemValue(self::KEY_CLIENT_ID);
		if ($systemValue !== null) {
			if (!is_string($systemValue)) {
				throw new IntegrationConfigException(
					'missing_config',
					'INTEGRATION_HMAC_CLIENT_ID must be a string.',
				);
			}
			return trim($systemValue);
		}

		$appValue = $this->getAppValue(self::KEY_CLIENT_ID);
		return trim($appValue ?? '');
	}

	private function getRawClientsJson(): string {
		$systemValue = $this->getSystemValue(self::KEY_CLIENTS_JSON);
		if ($systemValue !== null) {
			if (!is_string($systemValue)) {
				throw new IntegrationConfigException(
					'bad_json',
					'INTEGRATION_HMAC_CLIENTS_JSON must be a string.',
				);
			}
			return trim($systemValue);
		}

		$appValue = $this->getAppValue(self::KEY_CLIENTS_JSON);
		if ($appValue === null || $appValue === '') {
			return '';
		}

		return $this->decodeStoredSecretValue($appValue);
	}

	private function getSystemValue(string $key): mixed {
		return $this->config->getSystemValue($key, null);
	}

	private function getAppValue(string $key): ?string {
		$value = $this->config->getAppValue(self::APP_ID, $key, self::MISSING_VALUE);
		if ($value === self::MISSING_VALUE) {
			return null;
		}

		return $value;
	}

	private function decodeStoredSecretValue(string $value): string {
		if (str_starts_with($value, self::PLAIN_SECRET_PREFIX)) {
			return substr($value, strlen(self::PLAIN_SECRET_PREFIX));
		}

		try {
			return $this->crypto->decrypt($value);
		} catch (\Throwable $exception) {
			throw new IntegrationConfigException(
				'bad_json',
				'INTEGRATION_HMAC_CLIENTS_JSON could not be decrypted.',
				$exception,
			);
		}
	}

	private function isLegacyAllowed(): bool {
		return $this->config->getSystemValueBool(self::KEY_LEGACY_ALLOWED, false);
	}

	private function hasLegacyConfig(): bool {
		foreach (self::LEGACY_KEYS as $key) {
			$value = $this->config->getAppValue(self::APP_ID, $key, self::MISSING_VALUE);
			if ($value !== self::MISSING_VALUE && $value !== '') {
				return true;
			}
		}

		return false;
	}
}
