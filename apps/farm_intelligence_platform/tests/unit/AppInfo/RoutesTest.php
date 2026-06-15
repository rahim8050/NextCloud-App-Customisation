<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Tests\Unit\AppInfo;

use PHPUnit\Framework\TestCase;

final class RoutesTest extends TestCase {
	public function testRoutesUseControllerMethodFormat(): void {
		$routes = require __DIR__ . '/../../../appinfo/routes.php';

		$this->assertArrayHasKey('routes', $routes);
		$this->assertArrayHasKey('ocs', $routes);

		$definedRoutes = [];
		foreach (['routes', 'ocs'] as $group) {
			$definedRoutes = array_merge($definedRoutes, $routes[$group]);
		}

		$this->assertNotEmpty($definedRoutes);

		foreach ($definedRoutes as $route) {
			$this->assertIsArray($route);
			$this->assertArrayHasKey('name', $route);

			$parts = explode('#', (string)$route['name']);
			$this->assertCount(2, $parts, 'Route names must use controller#method format.');
			$this->assertNotSame('', $parts[0]);
			$this->assertNotSame('', $parts[1]);
		}
	}

	public function testNdviRoutesUseFarmIdPlaceholder(): void {
		$routes = require __DIR__ . '/../../../appinfo/routes.php';

		$this->assertArrayHasKey('routes', $routes);
		$definedRoutes = $routes['routes'];

		$ndviNames = [
			'adminFarms#getNdviLatest',
			'adminFarms#getNdviTimeseries',
			'adminFarms#getNdviRasterPng',
			'adminFarms#queueNdviRaster',
			'adminFarms#refreshNdvi',
			'adminFarms#getFarmState',
		];

		foreach ($definedRoutes as $route) {
			if (!in_array($route['name'], $ndviNames, true)) {
				continue;
			}
			$this->assertStringContainsString('{farmId}', $route['url']);
			$this->assertStringNotContainsString('{farm_id}', $route['url']);
		}
	}
}
