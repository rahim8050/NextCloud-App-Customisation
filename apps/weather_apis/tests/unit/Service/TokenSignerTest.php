<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Service;

use OCA\WeatherApis\Service\TokenSigner;
use PHPUnit\Framework\TestCase;

final class TokenSignerTest extends TestCase {
	private function loadHmacFixture(): array {
		$path = dirname(__DIR__, 2) . '/fixtures/hmac_test_vector.json';
		$raw = file_get_contents($path);
		$this->assertNotFalse($raw);
		$decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
		$this->assertIsArray($decoded);

		return $decoded;
	}

	public function testCanonicalStringMatchesContract(): void {
		$signer = new TokenSigner();
		$canonical = $signer->buildCanonicalString(
			'POST',
			'/api/v1/integrations/token/',
			'',
			'1700000000',
			'fixed-nonce',
			TokenSigner::EMPTY_BODY_HASH,
		);

		$this->assertSame(
			"POST\n/api/v1/integrations/token/\n\n1700000000\nfixed-nonce\n" . TokenSigner::EMPTY_BODY_HASH,
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

	public function testGoldenVectorFixtureMatchesTokenContract(): void {
		$fixture = $this->loadHmacFixture();
		$signer = new TokenSigner();
		$body = base64_decode((string)$fixture['body_b64'], true) ?: '';
		$bodyHash = $signer->bodySha256Hex(
			(string)$fixture['method'],
			$body,
		);
		$this->assertSame($fixture['expected_body_sha256'], $bodyHash);

		$canonical = $signer->buildCanonicalString(
			(string)$fixture['method'],
			(string)$fixture['path'],
			(string)$fixture['query_string'],
			(string)$fixture['timestamp'],
			(string)$fixture['nonce'],
			$bodyHash,
		);
		$this->assertSame($fixture['expected_canonical'], $canonical);

		$secret = base64_decode((string)$fixture['secret_b64'], true);
		$this->assertNotFalse($secret);
		$signature = hash_hmac('sha256', $canonical, $secret);
		$this->assertSame($fixture['expected_signature'], $signature);
	}

	public function testSignatureChangesWhenMethodChanges(): void {
		$signer = new TokenSigner();
		$bodyHash = $signer->bodySha256Hex('GET', '');
		$canonicalGet = $signer->buildCanonicalString(
			'GET',
			'/api/v1/integrations/nextcloud/ping/',
			'',
			'1700000000',
			'nonce',
			$bodyHash,
		);
		$canonicalPost = $signer->buildCanonicalString(
			'POST',
			'/api/v1/integrations/nextcloud/ping/',
			'',
			'1700000000',
			'nonce',
			$bodyHash,
		);

		$this->assertNotSame(
			hash_hmac('sha256', $canonicalGet, 'test-secret'),
			hash_hmac('sha256', $canonicalPost, 'test-secret'),
		);
	}

	public function testSignatureChangesWhenPathChanges(): void {
		$signer = new TokenSigner();
		$bodyHash = $signer->bodySha256Hex('GET', '');
		$canonicalPing = $signer->buildCanonicalString(
			'GET',
			'/api/v1/integrations/nextcloud/ping/',
			'',
			'1700000000',
			'nonce',
			$bodyHash,
		);
		$canonicalToken = $signer->buildCanonicalString(
			'GET',
			'/api/v1/integrations/token/',
			'',
			'1700000000',
			'nonce',
			$bodyHash,
		);

		$this->assertNotSame(
			hash_hmac('sha256', $canonicalPing, 'test-secret'),
			hash_hmac('sha256', $canonicalToken, 'test-secret'),
		);
	}
}
