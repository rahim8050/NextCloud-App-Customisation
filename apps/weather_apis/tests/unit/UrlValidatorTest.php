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
		$validator->validate('http://example.com', false, '', false);
	}

	public function testAcceptsHttpsPublicHost(): void {
		$validator = $this->createValidator([
			'example.com' => ['93.184.216.34'],
		]);

		$validator->validate('https://example.com', false, '', false);
		$this->assertTrue(true);
	}

	public function testRejectsPrivateIpInProdMode(): void {
		$validator = $this->createValidator([
			'172.25.121.28' => ['172.25.121.28'],
		]);

		$this->expectException(InvalidArgumentException::class);
		$validator->validate('https://172.25.121.28', false, '', false);
	}

	public function testAcceptsHttpPrivateWhenDevOverrideEnabled(): void {
		$validator = $this->createValidator([
			'172.25.121.28' => ['172.25.121.28'],
		]);

		$validator->validate('http://172.25.121.28', true, '172.25.121.28', false);
		$this->assertTrue(true);
	}

	public function testAcceptsLocalhostIpWhenDevOverrideAllowlisted(): void {
		$validator = $this->createValidator([
			'127.0.0.1' => ['127.0.0.1'],
		]);

		$validator->validate('http://127.0.0.1:8001', true, '127.0.0.1', false);
		$this->assertTrue(true);
	}
}
