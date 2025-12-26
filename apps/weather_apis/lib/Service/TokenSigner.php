<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

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
			$method,
			$path,
			$queryString,
			$timestamp,
			$nonce,
			$bodyHash,
		]);
	}
}
