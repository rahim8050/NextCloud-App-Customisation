<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\Settings;

use PHPUnit\Framework\TestCase;

final class AdminTemplateTest extends TestCase {
	public function testAdminTemplateExists(): void {
		$templatePath = __DIR__ . '/../../../templates/settings/admin.php';

		$this->assertFileExists($templatePath);
	}
}
