<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Service;

use InvalidArgumentException;
use OCA\WeatherApis\Service\UrlValidator;
use PHPUnit\Framework\TestCase;

final class UrlValidatorTest extends TestCase {
	private function createValidator(array $hostMap): UrlValidator {
		return new UrlValidator(fn (string $host): array => $hostMap[$host] ?? []);
	}

	public function testRejectsHttpByDefault(): void {
		$validator = $this->createValidator([
			'example.com' => ['93.184.216.34'],
		]);

		$this->expectException(InvalidArgumentException::class);
		$validator->validate('http://example.com', false, '');
	}

	public function testAcceptsHttpsPublicHost(): void {
		$validator = $this->createValidator([
			'example.com' => ['93.184.216.34'],
		]);

		$validator->validate('https://example.com', false, '');
		$this->assertTrue(true);
	}

	public function testRejectsPrivateIpInProdMode(): void {
		$validator = $this->createValidator([
			'172.25.121.28' => ['172.25.121.28'],
		]);

		$this->expectException(InvalidArgumentException::class);
		$validator->validate('https://172.25.121.28', false, '');
	}

	public function testAcceptsHttpPrivateWhenDevOverrideEnabled(): void {
		$validator = $this->createValidator([
			'172.25.121.28' => ['172.25.121.28'],
		]);

		$validator->validate('http://172.25.121.28', true, '172.25.121.28');
		$this->assertTrue(true);
	}
}
