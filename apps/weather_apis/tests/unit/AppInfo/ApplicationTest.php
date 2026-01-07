<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Tests\Unit\AppInfo;

use OCA\WeatherApis\AppInfo\Application;
use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Service\IntegrationConfig;
use OCA\WeatherApis\Service\TokenSigner;
use OCA\WeatherApis\Service\UrlValidator;
use OCA\WeatherApis\Service\WeatherApiClient;
use OCA\WeatherApis\Service\WeatherApiClientInterface;
use OCP\Http\Client\IClientService;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\Security\ICrypto;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

final class ApplicationTest extends TestCase {
	public function testWeatherApiClientInterfaceIsBound(): void {
		$context = new TestRegistrationContext();
		(new Application())->register($context);

		$clientService = $this->createMock(IClientService::class);
		$cache = $this->createMock(ICache::class);
		$cacheFactory = $this->createMock(ICacheFactory::class);
		$cacheFactory->method('createDistributed')->with(Application::APP_ID)->willReturn($cache);

		$container = new TestContainer([
			IClientService::class => $clientService,
			ICacheFactory::class => $cacheFactory,
			IConfig::class => $this->createConfigMock(),
			ICrypto::class => $this->createCryptoMock(),
			UrlValidator::class => new UrlValidator(),
			TokenSigner::class => new TokenSigner(),
			LoggerInterface::class => new FakeLogger(),
		]);

		$factories = $context->getFactories();

		$appConfig = $factories[AppConfig::class]($container);
		$container->addService(AppConfig::class, $appConfig);
		$integrationConfig = $factories[IntegrationConfig::class]($container);
		$container->addService(IntegrationConfig::class, $integrationConfig);

		$client = $factories[WeatherApiClient::class]($container);
		$container->addService(WeatherApiClient::class, $client);
		$interface = $factories[WeatherApiClientInterface::class]($container);

		$this->assertInstanceOf(WeatherApiClient::class, $client);
		$this->assertSame($client, $interface);
	}

	private function createConfigMock(): IConfig {
		$storage = [
			'baseUrl' => 'https://example.com',
			'timeoutSeconds' => '10',
			'devAllowHttp' => '0',
			'allowlistHosts' => '',
			'apiKey' => 'api-key',
			'INTEGRATION_HMAC_CLIENT_ID' => 'client',
			'INTEGRATION_HMAC_CLIENTS_JSON' => 'encrypted:{"client":"cGxhaW4tc2VjcmV0"}',
		];

		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')->willReturnCallback(
			function (string $appId, string $key, $default = '') use ($storage) {
				return $storage[$key] ?? $default;
			},
		);
		$config->method('getSystemValue')->willReturn(null);
		$config->method('getSystemValueBool')->willReturn(false);

		return $config;
	}

	private function createCryptoMock(): ICrypto {
		$crypto = $this->createMock(ICrypto::class);
		$crypto->method('decrypt')->willReturnCallback(static fn (string $value): string => match ($value) {
			'api-key' => 'plain-api',
			'encrypted:{"client":"cGxhaW4tc2VjcmV0"}' => '{"client":"cGxhaW4tc2VjcmV0"}',
			default => 'fallback',
		});
		$crypto->method('encrypt')->willReturnCallback(static fn (string $value, string $password = '') => $value);
		$crypto->method('calculateHMAC')->willReturnCallback(static fn (string $message, string $password = '') => $message);

		return $crypto;
	}
}

final class FakeLogger extends AbstractLogger implements LoggerInterface {
	public function log($level, $message, array $context = []): void {
		// noop
	}
}

final class TestContainer implements ContainerInterface {
	/** @var array<string, mixed> */
	private array $services;

	public function __construct(array $services) {
		$this->services = $services;
	}

	public function get(string $id): mixed {
		if (!array_key_exists($id, $this->services)) {
			throw new \RuntimeException("Service {$id} is not registered.");
		}

		return $this->services[$id];
	}

	public function has(string $id): bool {
		return array_key_exists($id, $this->services);
	}

	public function addService(string $id, mixed $service): void {
		$this->services[$id] = $service;
	}
}

final class TestRegistrationContext implements \OCP\AppFramework\Bootstrap\IRegistrationContext {
	/** @var array<string, callable> */
	private array $factories = [];

	/**
	 * @return array<string, callable>
	 */
	public function getFactories(): array {
		return $this->factories;
	}

	public function registerService(string $name, callable $factory, bool $shared = true): void {
		$this->factories[$name] = $factory;
	}

	public function registerCapability(string $capability): void {
	}

	public function registerCrashReporter(string $reporterClass): void {
	}

	public function registerDashboardWidget(string $widgetClass): void {
	}

	public function registerServiceAlias(string $alias, string $target): void {
	}

	public function registerParameter(string $name, $value): void {
	}

	public function registerEventListener(string $event, string $listener, int $priority = 0): void {
	}

	public function registerMiddleware(string $class, bool $global = false): void {
	}

	public function registerSearchProvider(string $class): void {
	}

	public function registerAlternativeLogin(string $class): void {
	}

	public function registerInitialStateProvider(string $class): void {
	}

	public function registerWellKnownHandler(string $class): void {
	}

	public function registerSpeechToTextProvider(string $providerClass): void {
	}

	public function registerTextProcessingProvider(string $providerClass): void {
	}

	public function registerTextToImageProvider(string $providerClass): void {
	}

	public function registerTemplateProvider(string $providerClass): void {
	}

	public function registerTranslationProvider(string $providerClass): void {
	}

	public function registerNotifierService(string $notifierClass): void {
	}

	public function registerTwoFactorProvider(string $twoFactorProviderClass): void {
	}

	public function registerPreviewProvider(string $previewProviderClass, string $mimeTypeRegex): void {
	}

	public function registerCalendarProvider(string $class): void {
	}

	public function registerReferenceProvider(string $class): void {
	}

	public function registerProfileLinkAction(string $actionClass): void {
	}

	public function registerTalkBackend(string $backend): void {
	}

	public function registerCalendarResourceBackend(string $class): void {
	}

	public function registerCalendarRoomBackend(string $class): void {
	}

	public function registerTeamResourceProvider(string $class): void {
	}

	public function registerUserMigrator(string $migratorClass): void {
	}

	public function registerSensitiveMethods(string $class, array $methods): void {
	}

	public function registerPublicShareTemplateProvider(string $class): void {
	}

	public function registerSetupCheck(string $setupCheckClass): void {
	}

	public function registerDeclarativeSettings(string $declarativeSettingsClass): void {
	}

	public function registerTaskProcessingProvider(string $taskProcessingProviderClass): void {
	}

	public function registerTaskProcessingTaskType(string $taskProcessingTaskTypeClass): void {
	}

	public function registerFileConversionProvider(string $class): void {
	}

	public function registerMailProvider(string $class): void {
	}

	public function registerConfigLexicon(string $configLexiconClass): void {
	}
}
