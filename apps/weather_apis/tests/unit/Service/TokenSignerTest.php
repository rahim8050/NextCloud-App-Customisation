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
}
