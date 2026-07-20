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

namespace OC {
	if (!class_exists(AppScriptDependency::class)) {
		final class AppScriptDependency {
			public function __construct(
				private readonly string $appId,
				private array $deps = [],
			) {
			}

			public function addDep(string $appId): void {
				$this->deps[] = $appId;
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

	if (!class_exists(\OC_Util::class)) {
		final class OC_Util {
			public static function addStyle(string $application, ?string $file = null, bool $prepend = false): void {
			}

			public static function addScript(string $application, ?string $file = null, bool $prepend = false): void {
			}

			public static function setupFS(?string $userId = null): void {
			}

			public static function obEnd(): void {
			}
		}
	}

	if (!class_exists(FakeServer::class)) {
		final class FakeServer {
			private readonly object $config;
			private readonly object $l10nFactory;

			public function __construct() {
				$this->config = new FakeConfig();
				$this->l10nFactory = new FakeL10nFactory();
			}

			public function get(string $serviceName): mixed {
				if ($serviceName === \OCP\IConfig::class) {
					return $this->config;
				}
				if ($serviceName === \OCP\L10N\IFactory::class) {
					return $this->l10nFactory;
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

	if (!class_exists(FakeL10nFactory::class)) {
		final class FakeL10nFactory {
			public function findLanguage(string $appId): string {
				return 'en';
			}
		}
	}

	\OC::$server = new FakeServer();

	$autoload = __DIR__ . '/../vendor/autoload.php';
	if (!is_file($autoload)) {
		fwrite(STDERR, "Missing autoload file: {$autoload}\nRun: cd apps/farm_intelligence_platform && composer install\n");
		exit(1);
	}

	$httpStatus = __DIR__ . '/../lib/Service/HttpStatus.php';
	if (is_file($httpStatus)) {
		require_once $httpStatus;
	}

	require_once $autoload;

	$farmSyncService = __DIR__ . '/../lib/Service/FarmSyncService.php';
	if (is_file($farmSyncService)) {
		require_once $farmSyncService;
	}
}
