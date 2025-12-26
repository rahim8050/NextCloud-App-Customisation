<?php

declare(strict_types=1);

namespace OCA\WeatherApis\AppInfo;

use OCA\WeatherApis\Controller\ApiController;
use OCA\WeatherApis\Controller\SettingsController;
use OCA\WeatherApis\Sections\AdminSection;
use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Service\TokenSigner;
use OCA\WeatherApis\Service\UrlValidator;
use OCA\WeatherApis\Service\WeatherApiClient;
use OCA\WeatherApis\Settings\AdminSettings;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Http\Client\IClientService;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\Security\ICrypto;
use Psr\Container\ContainerInterface;

/**
 * Application container & DI wiring for the Weather APIs app.
 */
final class Application extends App implements IBootstrap {
	public const APP_ID = 'weather_apis';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerService(AppConfig::class, function (ContainerInterface $c) {
			return new AppConfig(
				$c->get(IConfig::class),
				$c->get(ICrypto::class),
			);
		});

		$context->registerService(TokenSigner::class, function () {
			return new TokenSigner();
		});

		$context->registerService(UrlValidator::class, function () {
			return new UrlValidator();
		});

		$context->registerService(WeatherApiClient::class, function (ContainerInterface $c) {
			/** @var ICacheFactory $cacheFactory */
			$cacheFactory = $c->get(ICacheFactory::class);

			return new WeatherApiClient(
				$c->get(IClientService::class),
				$c->get(AppConfig::class),
				$c->get(UrlValidator::class),
				$c->get(TokenSigner::class),
				$cacheFactory->createDistributed(self::APP_ID),
				$c->get(ILogger::class),
			);
		});

		$context->registerService(ApiController::class, function (ContainerInterface $c) {
			return new ApiController(
				$c->get('AppName'),
				$c->get(IRequest::class),
				$c->get(WeatherApiClient::class),
				$c->get(IUserSession::class),
				$c->get(IGroupManager::class),
				$c->get(ILogger::class),
			);
		});

		$context->registerService(AdminSection::class, function (ContainerInterface $c) {
			return new AdminSection(
				$c->get('AppName'),
				$c->get(IURLGenerator::class),
				$c->get(IL10N::class),
			);
		});

		$context->registerService(AdminSettings::class, function (ContainerInterface $c) {
			return new AdminSettings(
				$c->get('AppName'),
				$c->get(IL10N::class),
				$c->get(AppConfig::class),
			);
		});

		$context->registerService(SettingsController::class, function (ContainerInterface $c) {
			return new SettingsController(
				$c->get('AppName'),
				$c->get(IRequest::class),
				$c->get(AppConfig::class),
				$c->get(UrlValidator::class),
				$c->get(IUserSession::class),
				$c->get(IGroupManager::class),
				$c->get(ILogger::class),
			);
		});
	}

	public function boot(IBootContext $context): void {
		// Nothing to bootstrap yet.
	}
}
