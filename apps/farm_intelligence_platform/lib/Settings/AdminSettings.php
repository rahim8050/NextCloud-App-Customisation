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
			Util::addScript('farm_intelligence_platform', 'leaflet');
			Util::addScript('farm_intelligence_platform', 'admin-settings');
			Util::addStyle('farm_intelligence_platform', 'leaflet');
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
			'farmNdviGeotiffUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdviGeotiff', ['farmId' => '__FARM_ID__']),
			'farmNdviRasterQueueUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.queueNdviRaster', ['farmId' => '__FARM_ID__']),
			'farmNdviRefreshUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.refreshNdvi', ['farmId' => '__FARM_ID__']),
			'farmNdwiLatestUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdwiLatest', ['farmId' => '__FARM_ID__']),
			'farmNdwiTimeseriesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdwiTimeseries', ['farmId' => '__FARM_ID__']),
			'farmNdwiRasterUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdwiRasterPng', ['farmId' => '__FARM_ID__']),
			'farmNdwiGeotiffUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdwiGeotiff', ['farmId' => '__FARM_ID__']),
			'farmNdwiRasterQueueUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.queueNdwiRaster', ['farmId' => '__FARM_ID__']),
			'farmNdwiRefreshUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.refreshNdwi', ['farmId' => '__FARM_ID__']),
			'farmNdwiFarmStateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdwiFarmState', ['farmId' => '__FARM_ID__']),
			'farmNdmiLatestUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdmiLatest', ['farmId' => '__FARM_ID__']),
			'farmNdmiTimeseriesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdmiTimeseries', ['farmId' => '__FARM_ID__']),
			'farmNdmiRasterUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdmiRasterPng', ['farmId' => '__FARM_ID__']),
			'farmNdmiGeotiffUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdmiGeotiff', ['farmId' => '__FARM_ID__']),
			'farmNdmiRasterQueueUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.queueNdmiRaster', ['farmId' => '__FARM_ID__']),
			'farmNdmiRefreshUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.refreshNdmi', ['farmId' => '__FARM_ID__']),
			'farmNdmiFarmStateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdmiFarmState', ['farmId' => '__FARM_ID__']),
			'farmRviLatestUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getRviLatest', ['farmId' => '__FARM_ID__']),
			'farmRviTimeseriesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getRviTimeseries', ['farmId' => '__FARM_ID__']),
			'farmRviRasterUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getRviRasterPng', ['farmId' => '__FARM_ID__']),
			'farmRviGeotiffUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getRviGeotiff', ['farmId' => '__FARM_ID__']),
			'farmRviRasterQueueUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.queueRviRaster', ['farmId' => '__FARM_ID__']),
			'farmRviRefreshUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.refreshRvi', ['farmId' => '__FARM_ID__']),
			'farmRviFarmStateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getRviFarmState', ['farmId' => '__FARM_ID__']),
			'farmS1SmiLatestUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getS1SmiLatest', ['farmId' => '__FARM_ID__']),
			'farmS1SmiTimeseriesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getS1SmiTimeseries', ['farmId' => '__FARM_ID__']),
			'farmS1SmiRasterUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getS1SmiRasterPng', ['farmId' => '__FARM_ID__']),
			'farmS1SmiGeotiffUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getS1SmiGeotiff', ['farmId' => '__FARM_ID__']),
			'farmS1SmiRasterQueueUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.queueS1SmiRaster', ['farmId' => '__FARM_ID__']),
			'farmS1SmiRefreshUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.refreshS1Smi', ['farmId' => '__FARM_ID__']),
			'farmS1SmiFarmStateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getS1SmiFarmState', ['farmId' => '__FARM_ID__']),
			'farmS3LstLatestUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getS3LstLatest', ['farmId' => '__FARM_ID__']),
			'farmS3LstTimeseriesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getS3LstTimeseries', ['farmId' => '__FARM_ID__']),
			'farmS3LstRasterUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getS3LstRasterPng', ['farmId' => '__FARM_ID__']),
			'farmS3LstGeotiffUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getS3LstGeotiff', ['farmId' => '__FARM_ID__']),
			'farmS3LstRasterQueueUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.queueS3LstRaster', ['farmId' => '__FARM_ID__']),
			'farmS3LstRefreshUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.refreshS3Lst', ['farmId' => '__FARM_ID__']),
			'farmS3LstFarmStateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getS3LstFarmState', ['farmId' => '__FARM_ID__']),
			'farmLandsatLstLatestUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getLandsatLstLatest', ['farmId' => '__FARM_ID__']),
			'farmLandsatLstTimeseriesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getLandsatLstTimeseries', ['farmId' => '__FARM_ID__']),
			'farmLandsatLstRasterUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getLandsatLstRasterPng', ['farmId' => '__FARM_ID__']),
			'farmLandsatLstGeotiffUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getLandsatLstGeotiff', ['farmId' => '__FARM_ID__']),
			'farmLandsatLstRasterQueueUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.queueLandsatLstRaster', ['farmId' => '__FARM_ID__']),
			'farmLandsatLstRefreshUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.refreshLandsatLst', ['farmId' => '__FARM_ID__']),
			'farmLandsatLstFarmStateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getLandsatLstFarmState', ['farmId' => '__FARM_ID__']),
			'farmIronOxideLatestUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getIronOxideLatest', ['farmId' => '__FARM_ID__']),
			'farmIronOxideTimeseriesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getIronOxideTimeseries', ['farmId' => '__FARM_ID__']),
			'farmIronOxideRasterUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getIronOxideRasterPng', ['farmId' => '__FARM_ID__']),
			'farmIronOxideGeotiffUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getIronOxideGeotiff', ['farmId' => '__FARM_ID__']),
			'farmIronOxideRasterQueueUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.queueIronOxideRaster', ['farmId' => '__FARM_ID__']),
			'farmIronOxideRefreshUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.refreshIronOxide', ['farmId' => '__FARM_ID__']),
			'farmIronOxideFarmStateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getIronOxideFarmState', ['farmId' => '__FARM_ID__']),
			'farmEviLatestUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getEviLatest', ['farmId' => '__FARM_ID__']),
			'farmEviTimeseriesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getEviTimeseries', ['farmId' => '__FARM_ID__']),
			'farmEviRasterUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getEviRasterPng', ['farmId' => '__FARM_ID__']),
			'farmEviGeotiffUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getEviGeotiff', ['farmId' => '__FARM_ID__']),
			'farmEviRasterQueueUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.queueEviRaster', ['farmId' => '__FARM_ID__']),
			'farmEviRefreshUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.refreshEvi', ['farmId' => '__FARM_ID__']),
			'farmEviFarmStateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getEviFarmState', ['farmId' => '__FARM_ID__']),
			'farmLRviLatestUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getLRviLatest', ['farmId' => '__FARM_ID__']),
			'farmLRviTimeseriesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getLRviTimeseries', ['farmId' => '__FARM_ID__']),
			'farmLRviRasterUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getLRviRasterPng', ['farmId' => '__FARM_ID__']),
			'farmLRviGeotiffUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getLRviGeotiff', ['farmId' => '__FARM_ID__']),
			'farmLRviRasterQueueUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.queueLRviRaster', ['farmId' => '__FARM_ID__']),
			'farmLRviRefreshUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.refreshLRvi', ['farmId' => '__FARM_ID__']),
			'farmLRviFarmStateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getLRviFarmState', ['farmId' => '__FARM_ID__']),
			'farmNisarSmiLatestUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNisarSmiLatest', ['farmId' => '__FARM_ID__']),
			'farmNisarSmiTimeseriesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNisarSmiTimeseries', ['farmId' => '__FARM_ID__']),
			'farmNisarSmiRasterUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNisarSmiRasterPng', ['farmId' => '__FARM_ID__']),
			'farmNisarSmiGeotiffUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNisarSmiGeotiff', ['farmId' => '__FARM_ID__']),
			'farmNisarSmiRasterQueueUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.queueNisarSmiRaster', ['farmId' => '__FARM_ID__']),
			'farmNisarSmiRefreshUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.refreshNisarSmi', ['farmId' => '__FARM_ID__']),
			'farmNisarSmiFarmStateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNisarSmiFarmState', ['farmId' => '__FARM_ID__']),
			'farmNdreLatestUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdreLatest', ['farmId' => '__FARM_ID__']),
			'farmNdreTimeseriesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdreTimeseries', ['farmId' => '__FARM_ID__']),
			'farmNdreRasterUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdreRasterPng', ['farmId' => '__FARM_ID__']),
			'farmNdreRasterQueueUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.queueNdreRaster', ['farmId' => '__FARM_ID__']),
			'farmNdreRefreshUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.refreshNdre', ['farmId' => '__FARM_ID__']),
			'farmNdreFarmStateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getNdreFarmState', ['farmId' => '__FARM_ID__']),
			'farmBiomassLatestUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getBiomassLatest', ['farmId' => '__FARM_ID__']),
			'farmBiomassTimeseriesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getBiomassTimeseries', ['farmId' => '__FARM_ID__']),
			'farmBiomassRasterUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getBiomassRasterPng', ['farmId' => '__FARM_ID__']),
			'farmBiomassRasterQueueUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.queueBiomassRaster', ['farmId' => '__FARM_ID__']),
			'farmBiomassRefreshUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.refreshBiomass', ['farmId' => '__FARM_ID__']),
			'farmBiomassFarmStateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getBiomassFarmState', ['farmId' => '__FARM_ID__']),
			'farmInsituValidationUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getInsituValidation', ['farmId' => '__FARM_ID__']),
			'farmInsituMoistureSamplesUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.listInsituMoistureSamples', ['farmId' => '__FARM_ID__']),
			'farmInsituMoistureSampleCreateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.createInsituMoistureSample', ['farmId' => '__FARM_ID__']),
			'farmInsituMoistureSampleUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getInsituMoistureSample', ['farmId' => '__FARM_ID__', 'sampleId' => '__SAMPLE_ID__']),
			'farmInsituMoistureSampleUpdateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.updateInsituMoistureSample', ['farmId' => '__FARM_ID__', 'sampleId' => '__SAMPLE_ID__']),
			'farmInsituMoistureSampleDeleteUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.deleteInsituMoistureSample', ['farmId' => '__FARM_ID__', 'sampleId' => '__SAMPLE_ID__']),
			'farmInsituHarvestsUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.listInsituHarvests', ['farmId' => '__FARM_ID__']),
			'farmInsituHarvestCreateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.createInsituHarvest', ['farmId' => '__FARM_ID__']),
			'farmInsituHarvestUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getInsituHarvest', ['farmId' => '__FARM_ID__', 'recordId' => '__RECORD_ID__']),
			'farmInsituHarvestUpdateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.updateInsituHarvest', ['farmId' => '__FARM_ID__', 'recordId' => '__RECORD_ID__']),
			'farmInsituHarvestDeleteUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.deleteInsituHarvest', ['farmId' => '__FARM_ID__', 'recordId' => '__RECORD_ID__']),
			'farmInsituBiomassObsUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.listInsituBiomassObs', ['farmId' => '__FARM_ID__']),
			'farmInsituBiomassObsCreateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.createInsituBiomassObs', ['farmId' => '__FARM_ID__']),
			'farmInsituBiomassObsGetUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getInsituBiomassObs', ['farmId' => '__FARM_ID__', 'observationId' => '__OBSERVATION_ID__']),
			'farmInsituBiomassObsUpdateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.updateInsituBiomassObs', ['farmId' => '__FARM_ID__', 'observationId' => '__OBSERVATION_ID__']),
			'farmInsituBiomassObsDeleteUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.deleteInsituBiomassObs', ['farmId' => '__FARM_ID__', 'observationId' => '__OBSERVATION_ID__']),
			'farmInsituTreeSurveysUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.listInsituTreeSurveys', ['farmId' => '__FARM_ID__']),
			'farmInsituTreeSurveyCreateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.createInsituTreeSurvey', ['farmId' => '__FARM_ID__']),
			'farmInsituTreeSurveyUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getInsituTreeSurvey', ['farmId' => '__FARM_ID__', 'sampleId' => '__SAMPLE_ID__']),
			'farmInsituTreeSurveyUpdateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.updateInsituTreeSurvey', ['farmId' => '__FARM_ID__', 'sampleId' => '__SAMPLE_ID__']),
			'farmInsituTreeSurveyDeleteUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.deleteInsituTreeSurvey', ['farmId' => '__FARM_ID__', 'sampleId' => '__SAMPLE_ID__']),
			'farmWeatherCurrentUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getWeatherCurrent', ['farmId' => '__FARM_ID__']),
			'farmWeatherHourlyUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getWeatherHourly', ['farmId' => '__FARM_ID__']),
			'farmWeatherDailyUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getWeatherDaily', ['farmId' => '__FARM_ID__']),
			'farmStateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getFarmState', ['farmId' => '__FARM_ID__']),
			'farmDecisionUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminFarms.getFarmDecision', ['farmId' => '__FARM_ID__']),
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
			'radioEmergencyCreateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.createEmergency'),
			'radioEmergencyUpdateUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.updateEmergency', ['pk' => '__PK__']),
			'radioEmergencyDeleteUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.deleteEmergency', ['pk' => '__PK__']),
			'radioTtsUrl' => $this->urlGenerator->linkToRoute('farm_intelligence_platform.adminRadio.synthesizeTts'),
		]);

		$csp = new ContentSecurityPolicy();
		$csp->addAllowedMediaDomain('*');
		$csp->addAllowedConnectDomain('*');
		$csp->addAllowedImageDomain('*.tile.openstreetmap.org');
		$csp->addAllowedImageDomain('*.tile.osm.org');
		$csp->addAllowedImageDomain('unpkg.com');
		$csp->addAllowedImageDomain('cdn.jsdelivr.net');
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
