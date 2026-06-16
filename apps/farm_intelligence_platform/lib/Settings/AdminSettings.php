<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Settings;

use OCA\FarmIntelligencePlatform\Service\AppConfig;
use OCA\FarmIntelligencePlatform\Service\IntegrationConfig;
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
		return 'farm_intelligence_platform';
	}

	public function getPriority(): int {
		return 10;
	}

	public function getForm(): TemplateResponse {
		if (class_exists('OC')) {
			Util::addScript('farm_intelligence_platform', 'ndvi-latest');
			Util::addScript('farm_intelligence_platform', 'admin-settings');
		}

		$clientId = $this->integrationConfig->getClientIdOrNull() ?? '';
		$hmacSecretSet = $this->integrationConfig->getSecretB64OrNull() !== null;

		$response = new TemplateResponse('farm_intelligence_platform', 'settings/admin', [
			'appName' => $this->appName,
			'baseUrl' => $this->appConfig->getBaseUrl(),
			'clientId' => $clientId,
			'timeoutSeconds' => $this->appConfig->getTimeoutSeconds(),
			'devAllowHttp' => $this->appConfig->isDevAllowHttp(),
			'allowlistHosts' => $this->appConfig->getAllowlistHosts(),
			'hmacSecretSet' => $hmacSecretSet,
			'apiKeySet' => $this->appConfig->hasApiKey(),
			'saveUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.settings.saveAdmin'),
			'generateCredentialsUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminConfig.generateCredentials'),
			'rotateHmacUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminConfig.rotateHmac'),
			'configUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminConfig.getConfig'),
			'testConnectionUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminConfig.testConnection'),
			'diagnosticsUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminConfig.diagnostics'),
			'previewUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminConfig.previewPng'),
			'farmSchemaUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getSchema'),
			'farmListUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.listFarms'),
			'farmCreateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.createFarm'),
			'farmGetUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getFarm', ['id' => '__ID__']),
			'farmUpdateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.updateFarm', ['id' => '__ID__']),
			'farmPatchUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.patchFarm', ['id' => '__ID__']),
			'farmDeleteUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.deleteFarm', ['id' => '__ID__']),
			'farmSyncUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.syncFarm'),
			'farmNdviLatestUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdviLatest', ['farmId' => '__FARM_ID__']),
			'farmNdviTimeseriesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdviTimeseries', ['farmId' => '__FARM_ID__']),
			'farmNdviRasterUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdviRasterPng', ['farmId' => '__FARM_ID__']),
			'farmNdviRasterQueueUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.queueNdviRaster', ['farmId' => '__FARM_ID__']),
			'farmNdviRefreshUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.refreshNdvi', ['farmId' => '__FARM_ID__']),
			'farmWeatherCurrentUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getWeatherCurrent', ['farmId' => '__FARM_ID__']),
			'farmWeatherHourlyUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getWeatherHourly', ['farmId' => '__FARM_ID__']),
			'farmWeatherDailyUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getWeatherDaily', ['farmId' => '__FARM_ID__']),
			'farmStateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getFarmState', ['farmId' => '__FARM_ID__']),
			'farmObservationsUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.listFarmObservations', ['farmId' => '__FARM_ID__']),
			'farmObservationUrl' => $this->urlGenerator->linkToRoute(
				'farm_intelligence_platform.adminFarms.getFarmObservation',
				['farmId' => '__FARM_ID__', 'observationId' => '__OBSERVATION_ID__'],
			),
			'farmActivitiesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.listFarmActivities', ['farmId' => '__FARM_ID__']),
			'farmActivityUrl' => $this->urlGenerator->linkToRoute(
				'farm_intelligence_platform.adminFarms.getFarmActivity',
				['farmId' => '__FARM_ID__', 'activityId' => '__ACTIVITY_ID__'],
			),
			'activitySchemaUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminActivities.getSchema'),
			'activityListUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminActivities.listActivities'),
			'activityCreateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminActivities.createActivity'),
			'activityGetUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminActivities.getActivity', ['id' => '__ID__']),
			'activityUpdateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminActivities.updateActivity', ['id' => '__ID__']),
			'activityPatchUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminActivities.patchActivity', ['id' => '__ID__']),
			'activityDeleteUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminActivities.deleteActivity', ['id' => '__ID__']),
			'radioProvidersUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.listProviders'),
			'radioStationsUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.listStations'),
			'radioStationUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.getStation', ['stationId' => '__STATION_ID__']),
			'radioStreamUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.getStreamUrl', ['stationId' => '__STATION_ID__']),
			'radioNowPlayingUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.getStationNowPlaying', ['stationId' => '__STATION_ID__']),
			'radioAnalyticsUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.getStationAnalytics', ['stationId' => '__STATION_ID__']),
			'radioStationHealthUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.getStationHealthHistory', ['stationId' => '__STATION_ID__']),
			'radioStationHealthHistoryUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.getStationHealthHistory', ['stationId' => '__STATION_ID__']),
			'radioHealthUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.getRadioHealth'),
			'radioEmergencyCurrentUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.getCurrentEmergency'),
			'radioEmergencyHistoryUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.getEmergencyHistory'),
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
