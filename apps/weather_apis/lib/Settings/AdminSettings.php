<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Settings;

use OCA\WeatherApis\Service\AppConfig;
use OCA\WeatherApis\Service\IntegrationConfig;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IDelegatedSettings;
use OCP\Util;

final class AdminSettings implements IDelegatedSettings {
	public function __construct(
		private readonly string $appName,
		private readonly IL10N $l10n,
		private readonly AppConfig $appConfig,
		private readonly IntegrationConfig $integrationConfig,
		private readonly IURLGenerator $urlGenerator,
	) {
	}

	public function getSection(): string {
		return 'weather_apis';
	}

	public function getPriority(): int {
		return 10;
	}

	public function getForm(): TemplateResponse {
		Util::addScript('weather_apis', 'ndvi-latest');
		Util::addScript('weather_apis', 'admin-settings');

		$clientId = $this->integrationConfig->getClientIdOrNull() ?? '';
		$hmacSecretSet = $this->integrationConfig->getSecretB64OrNull() !== null;

		$response = new TemplateResponse('weather_apis', 'settings/admin', [
			'appName' => $this->appName,
			'baseUrl' => $this->appConfig->getBaseUrl(),
			'clientId' => $clientId,
			'timeoutSeconds' => $this->appConfig->getTimeoutSeconds(),
			'devAllowHttp' => $this->appConfig->isDevAllowHttp(),
			'allowlistHosts' => $this->appConfig->getAllowlistHosts(),
			'hmacSecretSet' => $hmacSecretSet,
			'apiKeySet' => $this->appConfig->hasApiKey(),
			'saveUrl' => $this->urlGenerator->linkToRoute('weather_apis.settings.saveAdmin'),
			'generateCredentialsUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminConfig.generateCredentials'),
			'rotateHmacUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminConfig.rotateHmac'),
			'configUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminConfig.getConfig'),
			'testConnectionUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminConfig.testConnection'),
			'diagnosticsUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminConfig.diagnostics'),
			'previewUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminConfig.previewPng'),
			'farmSchemaUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.getSchema'),
			'farmListUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.listFarms'),
			'farmCreateUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.createFarm'),
			'farmGetUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.getFarm', ['id' => '__ID__']),
			'farmUpdateUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.updateFarm', ['id' => '__ID__']),
			'farmPatchUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.patchFarm', ['id' => '__ID__']),
			'farmDeleteUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.deleteFarm', ['id' => '__ID__']),
			'farmSyncUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.syncFarm'),
			'farmNdviLatestUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.getNdviLatest', ['farmId' => '__FARM_ID__']),
			'farmNdviTimeseriesUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.getNdviTimeseries', ['farmId' => '__FARM_ID__']),
			'farmNdviRasterUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.getNdviRasterPng', ['farmId' => '__FARM_ID__']),
			'farmNdviRasterQueueUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.queueNdviRaster', ['farmId' => '__FARM_ID__']),
			'farmNdviRefreshUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.refreshNdvi', ['farmId' => '__FARM_ID__']),
			'farmWeatherCurrentUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.getWeatherCurrent', ['farmId' => '__FARM_ID__']),
			'farmWeatherHourlyUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.getWeatherHourly', ['farmId' => '__FARM_ID__']),
			'farmWeatherDailyUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.getWeatherDaily', ['farmId' => '__FARM_ID__']),
			'farmStateUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.getFarmState', ['farmId' => '__FARM_ID__']),
			'farmObservationsUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.listFarmObservations', ['farmId' => '__FARM_ID__']),
			'farmObservationUrl' => $this->urlGenerator->linkToRoute(
				'weather_apis.adminFarms.getFarmObservation',
				['farmId' => '__FARM_ID__', 'observationId' => '__OBSERVATION_ID__'],
			),
			'farmActivitiesUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminFarms.listFarmActivities', ['farmId' => '__FARM_ID__']),
			'farmActivityUrl' => $this->urlGenerator->linkToRoute(
				'weather_apis.adminFarms.getFarmActivity',
				['farmId' => '__FARM_ID__', 'activityId' => '__ACTIVITY_ID__'],
			),
			'activitySchemaUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminActivities.getSchema'),
			'activityListUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminActivities.listActivities'),
			'activityCreateUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminActivities.createActivity'),
			'activityGetUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminActivities.getActivity', ['id' => '__ID__']),
			'activityUpdateUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminActivities.updateActivity', ['id' => '__ID__']),
			'activityPatchUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminActivities.patchActivity', ['id' => '__ID__']),
			'activityDeleteUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminActivities.deleteActivity', ['id' => '__ID__']),
			'radioProvidersUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminRadio.listProviders'),
			'radioStationsUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminRadio.listStations'),
			'radioStationUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminRadio.getStation', ['stationId' => '__STATION_ID__']),
			'radioStreamUrl' => $this->urlGenerator->linkToRoute('weather_apis.adminRadio.getStreamUrl', ['stationId' => '__STATION_ID__']),
		]);

		$csp = new ContentSecurityPolicy();
		$csp->addAllowedMediaDomain('*');
		$csp->addAllowedConnectDomain('*');
		$response->setContentSecurityPolicy($csp);

		return $response;
	}

	public function getName(): ?string {
		return 'Weather Apis';
	}

	public function getAuthorizedAppConfig(): array {
		return [
			AppConfig::APP_ID => [
				'/^baseUrl$/',
				'/^INTEGRATION_HMAC_CLIENT_ID$/',
				'/^INTEGRATION_HMAC_CLIENTS_JSON$/',
				'/^apiKey$/',
				'/^hmacSecretPrevious$/',
				'/^hmacSecretPreviousExpiresAt$/',
				'/^timeoutSeconds$/',
				'/^devAllowHttp$/',
				'/^allowlistHosts$/',
				'/^base_url$/',
				'/^timeout_seconds$/',
				'/^dev_allow_insecure_local_http$/',
				'/^dev_allowlist_hosts$/',
				'/^api_key$/',
				'/^hmac_client_id$/',
				'/^hmac_secret$/',
				'/^signingSecret$/',
				'/^devAllowlistHosts$/',
				'/^clientId$/',
				'/^hmacSecret$/',
			],
		];
	}
}
