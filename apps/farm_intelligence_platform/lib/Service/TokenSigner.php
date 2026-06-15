<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Service;

final class TokenSigner {
	public const EMPTY_BODY_HASH = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';

	public function buildCanonicalString(
		string $method,
		string $path,
		string $queryString,
		string $timestamp,
		string $nonce,
		string $bodyHash,
	): string {
		return implode("\n", [
			strtoupper($method),
			$path,
			$this->canonicalizeQuery($queryString),
			$timestamp,
			$nonce,
			$bodyHash,
		]);
	}

	public function bodySha256Hex(string $method, string $body): string {
		if (strtoupper($method) === 'GET') {
			$body = '';
		}

		return hash('sha256', $body);
	}

	public function canonicalizeQuery(string $queryString): string {
		if ($queryString === '') {
			return '';
		}

		$pairs = [];
		foreach (explode('&', $queryString) as $chunk) {
			if ($chunk === '') {
				continue;
			}

			[$rawKey, $rawValue] = array_pad(explode('=', $chunk, 2), 2, '');
			$key = $this->decodeQueryComponent($rawKey);
			$value = $this->decodeQueryComponent($rawValue);
			$pairs[] = [
				$this->encodeQueryComponent($key),
				$this->encodeQueryComponent($value),
			];
		}

		usort($pairs, static function (array $left, array $right): int {
			if ($left[0] === $right[0]) {
				return $left[1] <=> $right[1];
			}

			return $left[0] <=> $right[0];
		});

		$encoded = [];
		foreach ($pairs as [$key, $value]) {
			$encoded[] = $key . '=' . $value;
		}

		return implode('&', $encoded);
	}

	private function decodeQueryComponent(string $value): string {
		$value = str_replace('+', ' ', $value);
		return rawurldecode($value);
	}

	private function encodeQueryComponent(string $value): string {
		$encoded = rawurlencode($value);
		return str_replace('%7E', '~', $encoded);
	}
}
