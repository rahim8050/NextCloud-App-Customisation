<?php

declare(strict_types=1);

namespace OC\AppFramework\DependencyInjection {
	if (!class_exists(DIContainer::class)) {
		final class DIContainer {
			public function __construct(string $appName, array $urlParams = []) {
			}
		}
	}
}

namespace {
	if (!class_exists(\OC::class)) {
		final class OC {
			public static object $server;
		}
	}

	if (!class_exists(FakeServer::class)) {
		final class FakeServer {
			private readonly object $config;

			public function __construct() {
				$this->config = new FakeConfig();
			}

			public function get(string $serviceName): mixed {
				if ($serviceName === \OCP\IConfig::class) {
					return $this->config;
				}

				return null;
			}

			public function getRegisteredAppContainer(string $appName): void {
				throw new \OCP\AppFramework\QueryException('Not available in tests.');
			}
		}
	}

	if (!class_exists(FakeConfig::class)) {
		final class FakeConfig {
			public function getSystemValueBool(string $key): bool {
				return false;
			}
		}
	}

	\OC::$server = new FakeServer();

	$autoload = __DIR__ . '/../vendor/autoload.php';
	if (!is_file($autoload)) {
		fwrite(STDERR, "Missing autoload file: {$autoload}\nRun: cd apps/weather_apis && composer install\n");
		exit(1);
	}

	require_once $autoload;
}
