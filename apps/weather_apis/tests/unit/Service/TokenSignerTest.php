<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Service;

use OCA\WeatherApis\Service\TokenSigner;
use PHPUnit\Framework\TestCase;

final class TokenSignerTest extends TestCase {
	public function testCanonicalStringMatchesContract(): void {
		$signer = new TokenSigner();
		$canonical = $signer->buildCanonicalString(
			'POST',
			'/api/v1/integration/token/',
			'',
			'1700000000',
			'fixed-nonce',
			TokenSigner::EMPTY_BODY_HASH,
		);

		$this->assertSame(
			"POST\n/api/v1/integration/token/\n\n1700000000\nfixed-nonce\n" . TokenSigner::EMPTY_BODY_HASH,
			$canonical,
		);
	}

	public function testGoldenVectorMatchesDjangoSpec(): void {
		$signer = new TokenSigner();
		$queryString = 'a=2&b=two%20words&plus=%2B&a=1';
		$timestamp = '1766666666';
		$nonce = '550e8400-e29b-41d4-a716-446655440000';
		$bodyHash = $signer->bodySha256Hex('GET', 'ignored');

		$canonical = $signer->buildCanonicalString(
			'get',
			'/api/v1/integrations/nextcloud/ping/',
			$queryString,
			$timestamp,
			$nonce,
			$bodyHash,
		);

		$expectedCanonical = implode("\n", [
			'GET',
			'/api/v1/integrations/nextcloud/ping/',
			'a=1&a=2&b=two%20words&plus=%2B',
			$timestamp,
			$nonce,
			TokenSigner::EMPTY_BODY_HASH,
		]);
		$this->assertSame($expectedCanonical, $canonical);

		$signature = hash_hmac('sha256', $canonical, 'test-shared-secret');
		$this->assertSame(
			'60a6b6568842ac371ba78655d6788e841d61b251dc75157d0dfe4a39f57cc362',
			$signature,
		);
	}
}
