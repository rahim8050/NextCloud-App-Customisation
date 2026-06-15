<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\AppInfo;

use OCA\FarmIntelligencePlatform\Controller\AdminActivitiesController;
use OCA\FarmIntelligencePlatform\Controller\AdminConfigController;
use OCA\FarmIntelligencePlatform\Controller\AdminFarmsController;
use OCA\FarmIntelligencePlatform\Controller\AdminRadioController;
use OCA\FarmIntelligencePlatform\Controller\ApiController;
use OCA\FarmIntelligencePlatform\Controller\OcsApiController;
use OCA\FarmIntelligencePlatform\Controller\SettingsController;
use OCA\FarmIntelligencePlatform\Listeners\CSPListener;
use OCA\FarmIntelligencePlatform\Sections\AdminSection;
use OCA\FarmIntelligencePlatform\Service\AppConfig;
use OCA\FarmIntelligencePlatform\Service\DrfSchemaService;
use OCA\FarmIntelligencePlatform\Service\FarmSyncService;
use OCA\FarmIntelligencePlatform\Service\FarmSyncServiceInterface;
use OCA\FarmIntelligencePlatform\Service\IntegrationConfig;
use OCA\FarmIntelligencePlatform\Service\OpenApiRegistry;
use OCA\FarmIntelligencePlatform\Service\TokenSigner;
use OCA\FarmIntelligencePlatform\Service\UrlValidator;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClient;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Settings\AdminSettings;
use OCP\App\IAppManager;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Http\Client\IClientService;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;
use OCP\Security\ICrypto;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Application container & DI wiring for the Farm Intelligence Platform app.
 */
final class Application extends App implements IBootstrap {
	public const APP_ID = 'farm_intelligence_platform';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(AddContentSecurityPolicyEvent::class, CSPListener::class);

		$context->registerService(AppConfig::class, function (ContainerInterface $c) {
			return new AppConfig(
				$c->get(IConfig::class),
				$c->get(ICrypto::class),
			);
		});

		$context->registerService(IntegrationConfig::class, function (ContainerInterface $c) {
			return new IntegrationConfig(
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
			$logger = $c->get(LoggerInterface::class);

			return new WeatherApiClient(
				$c->get(IClientService::class),
				$c->get(AppConfig::class),
				$c->get(IntegrationConfig::class),
				$c->get(UrlValidator::class),
				$c->get(TokenSigner::class),
				$cacheFactory->createDistributed(self::APP_ID),
				$logger,
			);
		});

		$context->registerService(WeatherApiClientInterface::class, function (ContainerInterface $c): WeatherApiClientInterface {
			return $c->get(WeatherApiClient::class);
		});

		$context->registerService(ApiController::class, function (ContainerInterface $c) {
			$logger = $c->get(LoggerInterface::class);
			return new ApiController(
				$c->get('AppName'),
				$c->get(IRequest::class),
				$c->get(WeatherApiClientInterface::class),
				$c->get(IUserSession::class),
				$c->get(IGroupManager::class),
				$logger,
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
				$c->get(IntegrationConfig::class),
				$c->get(IURLGenerator::class),
			);
		});

		$context->registerService(SettingsController::class, function (ContainerInterface $c) {
			$logger = $c->get(LoggerInterface::class);
			return new SettingsController(
				$c->get('AppName'),
				$c->get(IRequest::class),
				$c->get(AppConfig::class),
				$c->get(IntegrationConfig::class),
				$c->get(UrlValidator::class),
				$c->get(IUserSession::class),
				$c->get(IGroupManager::class),
				$logger,
			);
		});

		$context->registerService(AdminConfigController::class, function (ContainerInterface $c) {
			$logger = $c->get(LoggerInterface::class);
			return new AdminConfigController(
				$c->get('AppName'),
				$c->get(IRequest::class),
				$c->get(AppConfig::class),
				$c->get(IntegrationConfig::class),
				$c->get(WeatherApiClientInterface::class),
				$c->get(IUserSession::class),
				$c->get(IGroupManager::class),
				$logger,
			);
		});

		$context->registerService(DrfSchemaService::class, function (ContainerInterface $c) {
			return new DrfSchemaService(
				$c->get(WeatherApiClientInterface::class),
				$c->get(ICacheFactory::class),
				$c->get(LoggerInterface::class),
			);
		});

		$context->registerService(FarmSyncService::class, function (ContainerInterface $c) {
			return new FarmSyncService(
				$c->get(WeatherApiClientInterface::class),
				$c->get(LoggerInterface::class),
			);
		});

		$context->registerService(FarmSyncServiceInterface::class, function (ContainerInterface $c): FarmSyncServiceInterface {
			return $c->get(FarmSyncService::class);
		});

		$context->registerService(AdminFarmsController::class, function (ContainerInterface $c) {
			$logger = $c->get(LoggerInterface::class);
			return new AdminFarmsController(
				$c->get('AppName'),
				$c->get(IRequest::class),
				$c->get(DrfSchemaService::class),
				$c->get(WeatherApiClientInterface::class),
				$c->get(FarmSyncServiceInterface::class),
				$logger,
			);
		});

		$context->registerService(OcsApiController::class, function (ContainerInterface $c) {
			$logger = $c->get(LoggerInterface::class);
			return new OcsApiController(
				$c->get('AppName'),
				$c->get(IRequest::class),
				$c->get(WeatherApiClientInterface::class),
				$c->get(IUserSession::class),
				$c->get(IGroupManager::class),
				$logger,
			);
		});

		$context->registerService(AdminActivitiesController::class, function (ContainerInterface $c) {
			$logger = $c->get(LoggerInterface::class);
			return new AdminActivitiesController(
				$c->get('AppName'),
				$c->get(IRequest::class),
				$c->get(DrfSchemaService::class),
				$c->get(WeatherApiClientInterface::class),
				$logger,
			);
		});

		$context->registerService(AdminRadioController::class, function (ContainerInterface $c) {
			$logger = $c->get(LoggerInterface::class);
			return new AdminRadioController(
				$c->get('AppName'),
				$c->get(IRequest::class),
				$c->get(WeatherApiClientInterface::class),
				$logger,
			);
		});

		$context->registerService(OpenApiRegistry::class, function (ContainerInterface $c): OpenApiRegistry {
			return new OpenApiRegistry(
				$c->get(IAppManager::class),
				self::APP_ID,
			);
		});
	}

	public function boot(IBootContext $context): void {
		// Nothing to bootstrap yet.
	}
}
