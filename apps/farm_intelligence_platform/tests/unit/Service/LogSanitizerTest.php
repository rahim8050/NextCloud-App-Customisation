<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Tests\Unit\Service;

use OCA\FarmIntelligencePlatform\Service\LogSanitizer;
use PHPUnit\Framework\TestCase;

final class LogSanitizerTest extends TestCase {
	public function testRedactsSensitiveKeysAndStrings(): void {
		$context = [
			'Authorization' => 'Bearer abc.def.ghi',
			'X-API-Key' => 'wk_live_secret',
			'nested' => [
				'token' => 'secret-token',
				'message' => 'Bearer should-hide',
			],
			'error' => 'request failed for wk_live_abc123',
			'safe' => 'ok',
		];

		$sanitized = LogSanitizer::sanitizeContext($context);

		$this->assertSame('[redacted]', $sanitized['Authorization']);
		$this->assertSame('[redacted]', $sanitized['X-API-Key']);
		$this->assertSame('[redacted]', $sanitized['nested']['token']);
		$this->assertSame('Bearer [redacted]', $sanitized['nested']['message']);
		$this->assertSame('request failed for wk_live_[redacted]', $sanitized['error']);
		$this->assertSame('ok', $sanitized['safe']);
	}
}
