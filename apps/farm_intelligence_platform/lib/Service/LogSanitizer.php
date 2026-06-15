<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Service;

final class LogSanitizer {
	private const REDACTED = '[redacted]';
	private const SENSITIVE_KEY_FRAGMENTS = [
		'authorization',
		'api-key',
		'api_key',
		'apikey',
		'hmac',
		'secret',
		'signature',
		'token',
		'nonce',
		'body',
		'payload',
		'headers',
		'cookie',
	];

	/**
	 * @param array<string, mixed> $context
	 * @return array<string, mixed>
	 */
	public static function sanitizeContext(array $context): array {
		$sanitized = [];
		foreach ($context as $key => $value) {
			$sanitized[$key] = self::sanitizeValue($value, $key);
		}

		return $sanitized;
	}

	private static function sanitizeValue(mixed $value, ?string $key = null): mixed {
		if ($key !== null && self::isSensitiveKey($key)) {
			return self::REDACTED;
		}

		if (is_array($value)) {
			$sanitized = [];
			foreach ($value as $nestedKey => $nestedValue) {
				$sanitized[$nestedKey] = self::sanitizeValue(
					$nestedValue,
					is_string($nestedKey) ? $nestedKey : null,
				);
			}
			return $sanitized;
		}

		if (is_string($value)) {
			return self::sanitizeString($value);
		}

		return $value;
	}

	private static function isSensitiveKey(string $key): bool {
		$lower = strtolower($key);
		foreach (self::SENSITIVE_KEY_FRAGMENTS as $fragment) {
			if (str_contains($lower, $fragment)) {
				return true;
			}
		}

		return false;
	}

	private static function sanitizeString(string $value): string {
		$value = preg_replace('/Bearer\\s+[^\\s,]+/i', 'Bearer ' . self::REDACTED, $value) ?? $value;
		$value = preg_replace('/\\bwk_live_[A-Za-z0-9_-]+\\b/', 'wk_live_' . self::REDACTED, $value) ?? $value;

		return $value;
	}
}
