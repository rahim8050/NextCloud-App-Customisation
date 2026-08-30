(() => {
	'use strict'

	/* global L, Hls */

	if (window.__weatherApisAdminSettingsLoaded) {
		return
	}
	window.__weatherApisAdminSettingsLoaded = true
	console.info('[farm_intelligence_platform] admin-settings loaded')

	const init = () => {
		const form = document.getElementById('farm-intelligence-platform-settings-form')
		const status = document.getElementById('farm-intelligence-platform-settings-status')
		if (!form || !status || typeof fetch === 'undefined') {
			return
		}

		const action = form.getAttribute('action') || ''
		const requestTokenInput = form.querySelector('input[name="requesttoken"]')
		if (!action) {
			return
		}

		const baseUrlInput = form.querySelector('[name="baseUrl"]')
		const clientIdInput = form.querySelector('[name="clientId"]')
		const apiKeyInput = form.querySelector('[name="apiKey"]')
		const hmacSecretInput = form.querySelector('[name="hmacSecret"]')
		const timeoutInput = form.querySelector('[name="timeoutSeconds"]')
		const devAllowHttpInput = form.querySelector('[name="devAllowHttp"]')
		const allowlistInput = form.querySelector('[name="allowlistHosts"]')
		const generateUrl = form.dataset.generateUrl || ''
		const rotateUrl = form.dataset.rotateUrl || ''
		const testConnectionUrl = form.dataset.testConnectionUrl || ''
		const diagnosticsUrl = form.dataset.diagnosticsUrl || ''
		const previewUrl = form.dataset.previewUrl || ''
		const farmSchemaUrl = form.dataset.farmSchemaUrl || ''
		const farmListUrl = form.dataset.farmListUrl || ''
		const farmCreateUrl = form.dataset.farmCreateUrl || ''
		const farmGetUrl = form.dataset.farmGetUrl || ''
		const farmPatchUrl = form.dataset.farmPatchUrl || ''
		const farmDeleteUrl = form.dataset.farmDeleteUrl || ''
		const farmSyncUrl = form.dataset.farmSyncUrl || ''
		const farmNdviLatestUrl = form.dataset.farmNdviLatestUrl || ''
		const farmNdviTimeseriesUrl = form.dataset.farmNdviTimeseriesUrl || ''
		const farmNdviRasterUrl = form.dataset.farmNdviRasterUrl || ''
		const farmNdviRasterQueueUrl = form.dataset.farmNdviRasterQueueUrl || ''
		const farmNdviRefreshUrl = form.dataset.farmNdviRefreshUrl || ''
		const farmNdviGeotiffUrl = form.dataset.farmNdviGeotiffUrl || ''
		const farmNdwiLatestUrl = form.dataset.farmNdwiLatestUrl || ''
		const farmNdwiTimeseriesUrl = form.dataset.farmNdwiTimeseriesUrl || ''
		const farmNdwiRasterUrl = form.dataset.farmNdwiRasterUrl || ''
		const farmNdwiRasterQueueUrl = form.dataset.farmNdwiRasterQueueUrl || ''
		const farmNdwiRefreshUrl = form.dataset.farmNdwiRefreshUrl || ''
		const farmNdwiFarmStateUrl = form.dataset.farmNdwiFarmStateUrl || ''
		const farmNdwiGeotiffUrl = form.dataset.farmNdwiGeotiffUrl || ''
		const farmNdmiLatestUrl = form.dataset.farmNdmiLatestUrl || ''
		const farmNdmiTimeseriesUrl = form.dataset.farmNdmiTimeseriesUrl || ''
		const farmNdmiRasterUrl = form.dataset.farmNdmiRasterUrl || ''
		const farmNdmiRasterQueueUrl = form.dataset.farmNdmiRasterQueueUrl || ''
		const farmNdmiRefreshUrl = form.dataset.farmNdmiRefreshUrl || ''
		const farmNdmiFarmStateUrl = form.dataset.farmNdmiFarmStateUrl || ''
		const farmNdmiGeotiffUrl = form.dataset.farmNdmiGeotiffUrl || ''
		const farmRviLatestUrl = form.dataset.farmRviLatestUrl || ''
		const farmRviTimeseriesUrl = form.dataset.farmRviTimeseriesUrl || ''
		const farmRviRasterUrl = form.dataset.farmRviRasterUrl || ''
		const farmRviRasterQueueUrl = form.dataset.farmRviRasterQueueUrl || ''
		const farmRviRefreshUrl = form.dataset.farmRviRefreshUrl || ''
		const farmRviFarmStateUrl = form.dataset.farmRviFarmStateUrl || ''
		const farmRviGeotiffUrl = form.dataset.farmRviGeotiffUrl || ''
		const farmS1SmiLatestUrl = form.dataset.farmS1SmiLatestUrl || ''
		const farmS1SmiTimeseriesUrl = form.dataset.farmS1SmiTimeseriesUrl || ''
		const farmS1SmiRasterUrl = form.dataset.farmS1SmiRasterUrl || ''
		const farmS1SmiRasterQueueUrl = form.dataset.farmS1SmiRasterQueueUrl || ''
		const farmS1SmiRefreshUrl = form.dataset.farmS1SmiRefreshUrl || ''
		const farmS1SmiFarmStateUrl = form.dataset.farmS1SmiFarmStateUrl || ''
		const farmS1SmiGeotiffUrl = form.dataset.farmS1SmiGeotiffUrl || ''
		const farmS3LstLatestUrl = form.dataset.farmS3LstLatestUrl || ''
		const farmS3LstTimeseriesUrl = form.dataset.farmS3LstTimeseriesUrl || ''
		const farmS3LstRasterUrl = form.dataset.farmS3LstRasterUrl || ''
		const farmS3LstRasterQueueUrl = form.dataset.farmS3LstRasterQueueUrl || ''
		const farmS3LstRefreshUrl = form.dataset.farmS3LstRefreshUrl || ''
		const farmS3LstFarmStateUrl = form.dataset.farmS3LstFarmStateUrl || ''
		const farmS3LstGeotiffUrl = form.dataset.farmS3LstGeotiffUrl || ''
		const farmLandsatLstLatestUrl = form.dataset.farmLandsatLstLatestUrl || ''
		const farmLandsatLstTimeseriesUrl = form.dataset.farmLandsatLstTimeseriesUrl || ''
		const farmLandsatLstRasterUrl = form.dataset.farmLandsatLstRasterUrl || ''
		const farmLandsatLstRasterQueueUrl = form.dataset.farmLandsatLstRasterQueueUrl || ''
		const farmLandsatLstRefreshUrl = form.dataset.farmLandsatLstRefreshUrl || ''
		const farmLandsatLstFarmStateUrl = form.dataset.farmLandsatLstFarmStateUrl || ''
		const farmLandsatLstGeotiffUrl = form.dataset.farmLandsatLstGeotiffUrl || ''
		const farmIronOxideLatestUrl = form.dataset.farmIronOxideLatestUrl || ''
		const farmIronOxideTimeseriesUrl = form.dataset.farmIronOxideTimeseriesUrl || ''
		const farmIronOxideRasterUrl = form.dataset.farmIronOxideRasterUrl || ''
		const farmIronOxideRasterQueueUrl = form.dataset.farmIronOxideRasterQueueUrl || ''
		const farmIronOxideRefreshUrl = form.dataset.farmIronOxideRefreshUrl || ''
		const farmIronOxideFarmStateUrl = form.dataset.farmIronOxideFarmStateUrl || ''
		const farmIronOxideGeotiffUrl = form.dataset.farmIronOxideGeotiffUrl || ''
		const farmEviLatestUrl = form.dataset.farmEviLatestUrl || ''
		const farmEviTimeseriesUrl = form.dataset.farmEviTimeseriesUrl || ''
		const farmEviRasterUrl = form.dataset.farmEviRasterUrl || ''
		const farmEviRasterQueueUrl = form.dataset.farmEviRasterQueueUrl || ''
		const farmEviRefreshUrl = form.dataset.farmEviRefreshUrl || ''
		const farmEviFarmStateUrl = form.dataset.farmEviFarmStateUrl || ''
		const farmEviGeotiffUrl = form.dataset.farmEviGeotiffUrl || ''
		const farmLRviLatestUrl = form.dataset.farmLRviLatestUrl || ''
		const farmLRviTimeseriesUrl = form.dataset.farmLRviTimeseriesUrl || ''
		const farmLRviRasterUrl = form.dataset.farmLRviRasterUrl || ''
		const farmLRviRasterQueueUrl = form.dataset.farmLRviRasterQueueUrl || ''
		const farmLRviRefreshUrl = form.dataset.farmLRviRefreshUrl || ''
		const farmLRviFarmStateUrl = form.dataset.farmLRviFarmStateUrl || ''
		const farmLRviGeotiffUrl = form.dataset.farmLRviGeotiffUrl || ''
		const farmNisarSmiLatestUrl = form.dataset.farmNisarSmiLatestUrl || ''
		const farmNisarSmiTimeseriesUrl = form.dataset.farmNisarSmiTimeseriesUrl || ''
		const farmNisarSmiRasterUrl = form.dataset.farmNisarSmiRasterUrl || ''
		const farmNisarSmiRasterQueueUrl = form.dataset.farmNisarSmiRasterQueueUrl || ''
		const farmNisarSmiRefreshUrl = form.dataset.farmNisarSmiRefreshUrl || ''
		const farmNisarSmiFarmStateUrl = form.dataset.farmNisarSmiFarmStateUrl || ''
		const farmNisarSmiGeotiffUrl = form.dataset.farmNisarSmiGeotiffUrl || ''
		const farmNdreLatestUrl = form.dataset.farmNdreLatestUrl || ''
		const farmNdreTimeseriesUrl = form.dataset.farmNdreTimeseriesUrl || ''
		const farmNdreRasterUrl = form.dataset.farmNdreRasterUrl || ''
		const farmNdreRasterQueueUrl = form.dataset.farmNdreRasterQueueUrl || ''
		const farmNdreRefreshUrl = form.dataset.farmNdreRefreshUrl || ''
		const farmNdreFarmStateUrl = form.dataset.farmNdreFarmStateUrl || ''
		const farmBiomassLatestUrl = form.dataset.farmBiomassLatestUrl || ''
		const farmBiomassTimeseriesUrl = form.dataset.farmBiomassTimeseriesUrl || ''
		const farmBiomassRasterUrl = form.dataset.farmBiomassRasterUrl || ''
		const farmBiomassRasterQueueUrl = form.dataset.farmBiomassRasterQueueUrl || ''
		const farmBiomassRefreshUrl = form.dataset.farmBiomassRefreshUrl || ''
		const farmBiomassFarmStateUrl = form.dataset.farmBiomassFarmStateUrl || ''
		const farmInsituValidationUrl = form.dataset.farmInsituValidationUrl || ''
		const farmInsituMoistureSamplesUrl = form.dataset.farmInsituMoistureSamplesUrl || ''
		const farmInsituMoistureSampleCreateUrl = form.dataset.farmInsituMoistureSampleCreateUrl || ''
		const farmInsituMoistureSampleUrl = form.dataset.farmInsituMoistureSampleUrl || ''
		const farmInsituMoistureSampleUpdateUrl = form.dataset.farmInsituMoistureSampleUpdateUrl || ''
		const farmInsituMoistureSampleDeleteUrl = form.dataset.farmInsituMoistureSampleDeleteUrl || ''
		const farmInsituHarvestsUrl = form.dataset.farmInsituHarvestsUrl || ''
		const farmInsituHarvestCreateUrl = form.dataset.farmInsituHarvestCreateUrl || ''
		const farmInsituHarvestUrl = form.dataset.farmInsituHarvestUrl || ''
		const farmInsituHarvestUpdateUrl = form.dataset.farmInsituHarvestUpdateUrl || ''
		const farmInsituHarvestDeleteUrl = form.dataset.farmInsituHarvestDeleteUrl || ''
		const farmInsituBiomassObsUrl = form.dataset.farmInsituBiomassObsUrl || ''
		const farmInsituBiomassObsCreateUrl = form.dataset.farmInsituBiomassObsCreateUrl || ''
		const farmInsituBiomassObsGetUrl = form.dataset.farmInsituBiomassObsGetUrl || ''
		const farmInsituBiomassObsUpdateUrl = form.dataset.farmInsituBiomassObsUpdateUrl || ''
		const farmInsituBiomassObsDeleteUrl = form.dataset.farmInsituBiomassObsDeleteUrl || ''
		const farmInsituTreeSurveysUrl = form.dataset.farmInsituTreeSurveysUrl || ''
		const farmInsituTreeSurveyCreateUrl = form.dataset.farmInsituTreeSurveyCreateUrl || ''
		const farmInsituTreeSurveyUrl = form.dataset.farmInsituTreeSurveyUrl || ''
		const farmInsituTreeSurveyUpdateUrl = form.dataset.farmInsituTreeSurveyUpdateUrl || ''
		const farmInsituTreeSurveyDeleteUrl = form.dataset.farmInsituTreeSurveyDeleteUrl || ''
		const farmWeatherCurrentUrl = form.dataset.farmWeatherCurrentUrl || ''
		const farmWeatherHourlyUrl = form.dataset.farmWeatherHourlyUrl || ''
		const farmWeatherDailyUrl = form.dataset.farmWeatherDailyUrl || ''
		const farmStateUrl = form.dataset.farmStateUrl || ''
		const farmDecisionUrl = form.dataset.farmDecisionUrl || ''
		const farmObservationsUrl = form.dataset.farmObservationsUrl || ''
		const farmObservationUrl = form.dataset.farmObservationUrl || ''
		const farmActivitiesUrl = form.dataset.farmActivitiesUrl || ''
		const farmActivityUrl = form.dataset.farmActivityUrl || ''
		const credentialsPanel = document.getElementById('farm-intelligence-platform-credentials-result')
		const generatedClientIdInput = document.getElementById('farm-intelligence-platform-generated-client-id')
		const generatedSecretInput = document.getElementById('farm-intelligence-platform-generated-secret')
		const exportSnippetInput = document.getElementById('farm-intelligence-platform-generated-export')
		const copyClientIdButton = document.getElementById('farm-intelligence-platform-copy-client-id')
		const copySecretButton = document.getElementById('farm-intelligence-platform-copy-secret')
		const copyExportButton = document.getElementById('farm-intelligence-platform-copy-export')
		const closeCredentialsButton = document.getElementById('farm-intelligence-platform-credentials-close')
		const generateButton = document.getElementById('farm-intelligence-platform-generate')
		const rotateButton = document.getElementById('farm-intelligence-platform-rotate')
		const testConnectionButton = document.getElementById('farm-intelligence-platform-test-connection')
		const connectionStatus = document.getElementById('farm-intelligence-platform-connection-status')
		const diagnosticsButton = document.getElementById('farm-intelligence-platform-run-diagnostics')
		const diagnosticsSummary = document.getElementById('farm-intelligence-platform-diagnostics-summary')
		const diagnosticsResults = document.getElementById('farm-intelligence-platform-diagnostics-results')
		const diagnosticsTokenRow = document.getElementById('farm-intelligence-platform-diagnostics-token-row')
		const diagnosticsStatusRow = document.getElementById('farm-intelligence-platform-diagnostics-status-row')
		const diagnosticsPngRow = document.getElementById('farm-intelligence-platform-diagnostics-png-row')
		const diagnosticsTokenValue = document.getElementById('farm-intelligence-platform-diagnostics-token')
		const diagnosticsStatusValue = document.getElementById('farm-intelligence-platform-diagnostics-status')
		const diagnosticsPngValue = document.getElementById('farm-intelligence-platform-diagnostics-png')
		const diagnosticsPreviewWrap = document.getElementById('farm-intelligence-platform-diagnostics-preview-wrap')
		const diagnosticsPreview = document.getElementById('farm-intelligence-platform-diagnostics-preview')
		const farmsRoot = document.getElementById('farm-intelligence-platform-farms')
		const farmsWarning = document.getElementById('farm-intelligence-platform-farms-warning')
		const farmsError = document.getElementById('farm-intelligence-platform-farms-error')
		const farmsColumns = document.getElementById('farm-intelligence-platform-farms-columns')
		const farmsBody = document.getElementById('farm-intelligence-platform-farms-body')
		const farmsRefresh = document.getElementById('farm-intelligence-platform-farms-refresh')
		const farmsCreate = document.getElementById('farm-intelligence-platform-farms-create')
		const farmsPagination = document.getElementById('farm-intelligence-platform-farms-pagination')
		const farmsPrev = document.getElementById('farm-intelligence-platform-farms-prev')
		const farmsNext = document.getElementById('farm-intelligence-platform-farms-next')
		const farmsPage = document.getElementById('farm-intelligence-platform-farms-page')
		const farmsNdvi = document.getElementById('farm-intelligence-platform-farms-ndvi')
		const farmsNdviTitle = document.getElementById('farm-intelligence-platform-farms-ndvi-title')
		const ndviLatestButton = document.getElementById('farm-intelligence-platform-ndvi-latest')
		const ndviTimeseriesButton = document.getElementById('farm-intelligence-platform-ndvi-timeseries')
		const ndviRasterButton = document.getElementById('farm-intelligence-platform-ndvi-raster')
		const ndviGeotiffButton = document.getElementById('farm-intelligence-platform-ndvi-geotiff')
		const ndviQueueButton = document.getElementById('farm-intelligence-platform-ndvi-queue')
		const ndviRefreshButton = document.getElementById('farm-intelligence-platform-ndvi-refresh')
		const farmStateButton = document.getElementById('farm-intelligence-platform-farm-state')
		const farmStateOutput = document.getElementById('farm-intelligence-platform-farm-state-output')
		const farmStateContent = document.getElementById('farm-intelligence-platform-farm-state-content')
		const decisionButton = document.getElementById('farm-intelligence-platform-decision')
		const decisionOutput = document.getElementById('farm-intelligence-platform-decision-output')
		const decisionContent = document.getElementById('farm-intelligence-platform-decision-content')
		const ndviStartInput = document.getElementById('farm-intelligence-platform-ndvi-start')
		const ndviEndInput = document.getElementById('farm-intelligence-platform-ndvi-end')
		const ndviDateInput = document.getElementById('farm-intelligence-platform-ndvi-date')
		const ndviError = document.getElementById('farm-intelligence-platform-ndvi-error')
		const ndviOutput = document.getElementById('farm-intelligence-platform-ndvi-output')
		const ndviCalendar = document.getElementById('farm-intelligence-platform-ndvi-calendar')
		const ndviWeekdays = document.getElementById('farm-intelligence-platform-ndvi-weekdays')
		const ndviCalendarGrid = document.getElementById('farm-intelligence-platform-ndvi-calendar-grid')
		const ndviTable = document.getElementById('farm-intelligence-platform-ndvi-table')
		const ndviRasterPreview = document.getElementById('farm-intelligence-platform-ndvi-raster-preview')
		const ndviRasterImg = document.getElementById('farm-intelligence-platform-ndvi-raster-img')
		const rasterMapContainer = document.getElementById('farm-intelligence-platform-raster-map')
		const ndwiLatestButton = document.getElementById('farm-intelligence-platform-ndwi-latest')
		const ndwiTimeseriesButton = document.getElementById('farm-intelligence-platform-ndwi-timeseries')
		const ndwiRasterButton = document.getElementById('farm-intelligence-platform-ndwi-raster')
		const ndwiGeotiffButton = document.getElementById('farm-intelligence-platform-ndwi-geotiff')
		const ndwiQueueButton = document.getElementById('farm-intelligence-platform-ndwi-queue')
		const ndwiRefreshButton = document.getElementById('farm-intelligence-platform-ndwi-refresh')
		const ndwiStateButton = document.getElementById('farm-intelligence-platform-ndwi-state')
		const ndmiLatestButton = document.getElementById('farm-intelligence-platform-ndmi-latest')
		const ndmiTimeseriesButton = document.getElementById('farm-intelligence-platform-ndmi-timeseries')
		const ndmiRasterButton = document.getElementById('farm-intelligence-platform-ndmi-raster')
		const ndmiGeotiffButton = document.getElementById('farm-intelligence-platform-ndmi-geotiff')
		const ndmiQueueButton = document.getElementById('farm-intelligence-platform-ndmi-queue')
		const ndmiRefreshButton = document.getElementById('farm-intelligence-platform-ndmi-refresh')
		const ndmiStateButton = document.getElementById('farm-intelligence-platform-ndmi-state')
		const rviLatestButton = document.getElementById('farm-intelligence-platform-rvi-latest')
		const rviTimeseriesButton = document.getElementById('farm-intelligence-platform-rvi-timeseries')
		const rviRasterButton = document.getElementById('farm-intelligence-platform-rvi-raster')
		const rviGeotiffButton = document.getElementById('farm-intelligence-platform-rvi-geotiff')
		const rviQueueButton = document.getElementById('farm-intelligence-platform-rvi-queue')
		const rviRefreshButton = document.getElementById('farm-intelligence-platform-rvi-refresh')
		const rviStateButton = document.getElementById('farm-intelligence-platform-rvi-state')
		const s1SmiLatestButton = document.getElementById('farm-intelligence-platform-s1-smi-latest')
		const s1SmiTimeseriesButton = document.getElementById('farm-intelligence-platform-s1-smi-timeseries')
		const s1SmiRasterButton = document.getElementById('farm-intelligence-platform-s1-smi-raster')
		const s1SmiGeotiffButton = document.getElementById('farm-intelligence-platform-s1-smi-geotiff')
		const s1SmiQueueButton = document.getElementById('farm-intelligence-platform-s1-smi-queue')
		const s1SmiRefreshButton = document.getElementById('farm-intelligence-platform-s1-smi-refresh')
		const s1SmiStateButton = document.getElementById('farm-intelligence-platform-s1-smi-state')
		const s3LstLatestButton = document.getElementById('farm-intelligence-platform-s3-lst-latest')
		const s3LstTimeseriesButton = document.getElementById('farm-intelligence-platform-s3-lst-timeseries')
		const s3LstRasterButton = document.getElementById('farm-intelligence-platform-s3-lst-raster')
		const s3LstGeotiffButton = document.getElementById('farm-intelligence-platform-s3-lst-geotiff')
		const s3LstQueueButton = document.getElementById('farm-intelligence-platform-s3-lst-queue')
		const s3LstRefreshButton = document.getElementById('farm-intelligence-platform-s3-lst-refresh')
		const s3LstStateButton = document.getElementById('farm-intelligence-platform-s3-lst-state')
		const landsatLstLatestButton = document.getElementById('farm-intelligence-platform-landsat-lst-latest')
		const landsatLstTimeseriesButton = document.getElementById('farm-intelligence-platform-landsat-lst-timeseries')
		const landsatLstRasterButton = document.getElementById('farm-intelligence-platform-landsat-lst-raster')
		const landsatLstGeotiffButton = document.getElementById('farm-intelligence-platform-landsat-lst-geotiff')
		const landsatLstQueueButton = document.getElementById('farm-intelligence-platform-landsat-lst-queue')
		const landsatLstRefreshButton = document.getElementById('farm-intelligence-platform-landsat-lst-refresh')
		const landsatLstStateButton = document.getElementById('farm-intelligence-platform-landsat-lst-state')
		const ironOxideLatestButton = document.getElementById('farm-intelligence-platform-iron-oxide-latest')
		const ironOxideTimeseriesButton = document.getElementById('farm-intelligence-platform-iron-oxide-timeseries')
		const ironOxideRasterButton = document.getElementById('farm-intelligence-platform-iron-oxide-raster')
		const ironOxideGeotiffButton = document.getElementById('farm-intelligence-platform-iron-oxide-geotiff')
		const ironOxideQueueButton = document.getElementById('farm-intelligence-platform-iron-oxide-queue')
		const ironOxideRefreshButton = document.getElementById('farm-intelligence-platform-iron-oxide-refresh')
		const ironOxideStateButton = document.getElementById('farm-intelligence-platform-iron-oxide-state')
		const eviLatestButton = document.getElementById('farm-intelligence-platform-evi-latest')
		const eviTimeseriesButton = document.getElementById('farm-intelligence-platform-evi-timeseries')
		const eviRasterButton = document.getElementById('farm-intelligence-platform-evi-raster')
		const eviGeotiffButton = document.getElementById('farm-intelligence-platform-evi-geotiff')
		const eviQueueButton = document.getElementById('farm-intelligence-platform-evi-queue')
		const eviRefreshButton = document.getElementById('farm-intelligence-platform-evi-refresh')
		const eviStateButton = document.getElementById('farm-intelligence-platform-evi-state')
		const lRviLatestButton = document.getElementById('farm-intelligence-platform-l-rvi-latest')
		const lRviTimeseriesButton = document.getElementById('farm-intelligence-platform-l-rvi-timeseries')
		const lRviRasterButton = document.getElementById('farm-intelligence-platform-l-rvi-raster')
		const lRviQueueButton = document.getElementById('farm-intelligence-platform-l-rvi-queue')
		const lRviRefreshButton = document.getElementById('farm-intelligence-platform-l-rvi-refresh')
		const lRviStateButton = document.getElementById('farm-intelligence-platform-l-rvi-state')
		const lRviGeotiffButton = document.getElementById('farm-intelligence-platform-l-rvi-geotiff')
		const nisarSmiLatestButton = document.getElementById('farm-intelligence-platform-nisar-smi-latest')
		const nisarSmiTimeseriesButton = document.getElementById('farm-intelligence-platform-nisar-smi-timeseries')
		const nisarSmiRasterButton = document.getElementById('farm-intelligence-platform-nisar-smi-raster')
		const nisarSmiQueueButton = document.getElementById('farm-intelligence-platform-nisar-smi-queue')
		const nisarSmiRefreshButton = document.getElementById('farm-intelligence-platform-nisar-smi-refresh')
		const nisarSmiStateButton = document.getElementById('farm-intelligence-platform-nisar-smi-state')
		const nisarSmiGeotiffButton = document.getElementById('farm-intelligence-platform-nisar-smi-geotiff')
		const ndreLatestButton = document.getElementById('farm-intelligence-platform-ndre-latest')
		const ndreTimeseriesButton = document.getElementById('farm-intelligence-platform-ndre-timeseries')
		const ndreRasterButton = document.getElementById('farm-intelligence-platform-ndre-raster')
		const ndreQueueButton = document.getElementById('farm-intelligence-platform-ndre-queue')
		const ndreRefreshButton = document.getElementById('farm-intelligence-platform-ndre-refresh')
		const ndreStateButton = document.getElementById('farm-intelligence-platform-ndre-state')
		const biomassLatestButton = document.getElementById('farm-intelligence-platform-biomass-latest')
		const biomassTimeseriesButton = document.getElementById('farm-intelligence-platform-biomass-timeseries')
		const biomassRasterButton = document.getElementById('farm-intelligence-platform-biomass-raster')
		const biomassQueueButton = document.getElementById('farm-intelligence-platform-biomass-queue')
		const biomassRefreshButton = document.getElementById('farm-intelligence-platform-biomass-refresh')
		const biomassStateButton = document.getElementById('farm-intelligence-platform-biomass-state')
		const farmsWeather = document.getElementById('farm-intelligence-platform-farms-weather')
		const farmsWeatherTitle = document.getElementById('farm-intelligence-platform-farms-weather-title')
		const farmsObservations = document.getElementById('farm-intelligence-platform-farms-observations')
		const farmsObservationsTitle = document.getElementById('farm-intelligence-platform-farms-observations-title')
		const farmsObservationsError = document.getElementById('farm-intelligence-platform-farms-observations-error')
		const farmsObservationsTable = document.getElementById('farm-intelligence-platform-farms-observations-table')
		const farmsObservationsPagination = document.getElementById('farm-intelligence-platform-farms-observations-pagination')
		const farmsObservationsPrev = document.getElementById('farm-intelligence-platform-farms-observations-prev')
		const farmsObservationsNext = document.getElementById('farm-intelligence-platform-farms-observations-next')
		const farmsObservationsPage = document.getElementById('farm-intelligence-platform-farms-observations-page')
		const farmsActivities = document.getElementById('farm-intelligence-platform-farms-activities')
		const farmsActivitiesTitle = document.getElementById('farm-intelligence-platform-farms-activities-title')
		const farmsActivitiesError = document.getElementById('farm-intelligence-platform-farms-activities-error')
		const farmsActivitiesTable = document.getElementById('farm-intelligence-platform-farms-activities-table')
		const farmsActivitiesPagination = document.getElementById('farm-intelligence-platform-farms-activities-pagination')
		const farmsActivitiesPrev = document.getElementById('farm-intelligence-platform-farms-activities-prev')
		const farmsActivitiesNext = document.getElementById('farm-intelligence-platform-farms-activities-next')
		const farmsActivitiesPage = document.getElementById('farm-intelligence-platform-farms-activities-page')
		const observationsStartInput = document.getElementById('farm-intelligence-platform-observations-start')
		const observationsEndInput = document.getElementById('farm-intelligence-platform-observations-end')
		const observationsTypeInput = document.getElementById('farm-intelligence-platform-observations-type')
		const observationsLimitInput = document.getElementById('farm-intelligence-platform-observations-limit')
		const observationsRefresh = document.getElementById('farm-intelligence-platform-observations-refresh')
		const observationsCreate = document.getElementById('farm-intelligence-platform-observations-create')
		const activitiesStatusInput = document.getElementById('farm-intelligence-platform-activities-status')
		const activitiesTypeFilterInput = document.getElementById('farm-intelligence-platform-activities-type-filter')
		const activitiesLimitInput = document.getElementById('farm-intelligence-platform-activities-limit')
		const activitiesRefresh = document.getElementById('farm-intelligence-platform-activities-refresh')
		const activitiesCreate = document.getElementById('farm-intelligence-platform-activities-create')
		const activitiesModal = document.getElementById('farm-intelligence-platform-farms-activity-modal')
		const activitiesModalTitle = document.getElementById('farm-intelligence-platform-farms-activity-modal-title')
		const activitiesModalClose = document.getElementById('farm-intelligence-platform-farms-activity-modal-close')
		const activitiesModalSave = document.getElementById('farm-intelligence-platform-farms-activity-modal-save')
		const activitiesModalFields = document.getElementById('farm-intelligence-platform-farms-activity-fields')
		const observationsModal = document.getElementById('farm-intelligence-platform-farms-observation-modal')
		const observationsModalTitle = document.getElementById('farm-intelligence-platform-farms-observation-modal-title')
		const observationsModalClose = document.getElementById('farm-intelligence-platform-farms-observation-modal-close')
		const observationsModalSave = document.getElementById('farm-intelligence-platform-farms-observation-modal-save')
		const observationObservedAt = document.getElementById('farm-intelligence-platform-observation-observed-at')
		const observationEventType = document.getElementById('farm-intelligence-platform-observation-event-type')
		const observationNote = document.getElementById('farm-intelligence-platform-observation-note')
		const observationSource = document.getElementById('farm-intelligence-platform-observation-source')
		const observationObserver = document.getElementById('farm-intelligence-platform-observation-observer')
		const observationCrop = document.getElementById('farm-intelligence-platform-observation-crop')
		const observationVariety = document.getElementById('farm-intelligence-platform-observation-variety')
		const observationGrowthStage = document.getElementById('farm-intelligence-platform-observation-growth-stage')
		const observationAreaHa = document.getElementById('farm-intelligence-platform-observation-area-ha')
		const observationLocationNote = document.getElementById('farm-intelligence-platform-observation-location-note')
		const observationSeedRate = document.getElementById('farm-intelligence-platform-observation-seed-rate')
		const observationPlantingMethod = document.getElementById('farm-intelligence-platform-observation-planting-method')
		const observationIrrigationType = document.getElementById('farm-intelligence-platform-observation-irrigation-type')
		const observationWaterMm = document.getElementById('farm-intelligence-platform-observation-water-mm')
		const observationFertilizerType = document.getElementById('farm-intelligence-platform-observation-fertilizer-type')
		const observationNutrientN = document.getElementById('farm-intelligence-platform-observation-nutrient-n')
		const observationNutrientP = document.getElementById('farm-intelligence-platform-observation-nutrient-p')
		const observationNutrientK = document.getElementById('farm-intelligence-platform-observation-nutrient-k')
		const observationPest = document.getElementById('farm-intelligence-platform-observation-pest')
		const observationProduct = document.getElementById('farm-intelligence-platform-observation-product')
		const observationDose = document.getElementById('farm-intelligence-platform-observation-dose')
		const observationYield = document.getElementById('farm-intelligence-platform-observation-yield')
		const observationMoisture = document.getElementById('farm-intelligence-platform-observation-moisture')
		const observationPestPressure = document.getElementById('farm-intelligence-platform-observation-pest-pressure')
		const observationSoilPh = document.getElementById('farm-intelligence-platform-observation-soil-ph')
		const observationOrganicMatter = document.getElementById('farm-intelligence-platform-observation-organic-matter')
		const observationFieldGroups = observationsModal
			? observationsModal.querySelectorAll('[data-event-types]')
			: []
		const weatherCurrentTab = document.getElementById('farm-intelligence-platform-weather-current-tab')
		const weatherHourlyTab = document.getElementById('farm-intelligence-platform-weather-hourly-tab')
		const weatherDailyTab = document.getElementById('farm-intelligence-platform-weather-daily-tab')
		const weatherLoading = document.getElementById('farm-intelligence-platform-weather-loading')
		const weatherError = document.getElementById('farm-intelligence-platform-weather-error')
		const weatherCurrentPanel = document.getElementById('farm-intelligence-platform-weather-current')
		const weatherHourlyPanel = document.getElementById('farm-intelligence-platform-weather-hourly')
		const weatherDailyPanel = document.getElementById('farm-intelligence-platform-weather-daily')
		const weatherCurrentGrid = document.getElementById('farm-intelligence-platform-weather-current-grid')
		const weatherHourlyTable = document.getElementById('farm-intelligence-platform-weather-hourly-table')
		const weatherDailyTable = document.getElementById('farm-intelligence-platform-weather-daily-table')
		const farmsModal = document.getElementById('farm-intelligence-platform-farms-modal')
		const farmsModalTitle = document.getElementById('farm-intelligence-platform-farms-modal-title')
		const farmsModalFields = document.getElementById('farm-intelligence-platform-farms-modal-fields')
		const farmsModalSave = document.getElementById('farm-intelligence-platform-farms-modal-save')
		const farmsModalClose = document.getElementById('farm-intelligence-platform-farms-modal-close')
		const farmsSyncModal = document.getElementById('farm-intelligence-platform-farms-sync-modal')
		const farmsSyncModalClose = document.getElementById('farm-intelligence-platform-farms-sync-modal-close')
		const farmsSyncModalCancel = document.getElementById('farm-intelligence-platform-farms-sync-modal-cancel')
		const farmsSyncModalConfirm = document.getElementById('farm-intelligence-platform-farms-sync-modal-confirm')
		const syncExternalFarmIdInput = document.getElementById('farm-intelligence-platform-sync-external-farm-id')
		const syncExternalUserIdInput = document.getElementById('farm-intelligence-platform-sync-external-user-id')
		const syncNameInput = document.getElementById('farm-intelligence-platform-sync-name')
		// TODO: confirm desired auto-hide timeout for generated secrets; 30s keeps the UI usable without lingering secrets.
		const CREDENTIALS_CLEAR_DELAY_MS = 30000
		let credentialsClearTimer = null

		const toText = (value, fallback = '') => {
			if (typeof value === 'string') return value
			if (value instanceof Error && typeof value.message === 'string') return value.message

			if (value && typeof value === 'object') {
				try {
					const json = JSON.stringify(value)
					return json && json !== '{}' ? json : fallback
				} catch {
					return fallback
				}
			}

			return String(value ?? fallback)
		}

		const clearStatus = () => {
			status.textContent = ''
			status.classList.remove('success', 'error')
		}

		const readRequestTokenFromMeta = () => {
			const meta = document.querySelector('meta[name="requesttoken"]')
			return String(meta?.getAttribute('content') ?? '').trim()
		}

		const resolveRequestToken = () => {
			const token = String(
				window.OC?.requestToken
				?? readRequestTokenFromMeta()
				?? requestTokenInput?.value
				?? '',
			).trim()
			if (requestTokenInput && token && requestTokenInput.value !== token) {
				requestTokenInput.value = token
			}
			return token
		}

		const clearConnectionStatus = () => {
			if (!connectionStatus) {
				return
			}
			connectionStatus.textContent = ''
			connectionStatus.classList.remove('success', 'error')
		}

		const clearDiagnosticsSummary = () => {
			if (!diagnosticsSummary) {
				return
			}
			diagnosticsSummary.textContent = ''
			diagnosticsSummary.classList.remove('success', 'error')
		}

		const clearDiagnosticsRows = () => {
			const rows = [diagnosticsTokenRow, diagnosticsStatusRow, diagnosticsPngRow]
			rows.forEach((row) => {
				if (row) {
					row.classList.remove('success', 'error')
				}
			})
			const values = [diagnosticsTokenValue, diagnosticsStatusValue, diagnosticsPngValue]
			values.forEach((el) => {
				if (el) {
					el.textContent = ''
				}
			})
			if (diagnosticsResults) {
				diagnosticsResults.hidden = true
			}
			if (diagnosticsPreviewWrap) {
				diagnosticsPreviewWrap.hidden = true
			}
			if (diagnosticsPreview) {
				diagnosticsPreview.removeAttribute('src')
			}
		}

		const clearDiagnostics = () => {
			clearDiagnosticsSummary()
			clearDiagnosticsRows()
		}

		const setDiagnosticsRow = (row, valueEl, ok, text) => {
			if (row) {
				row.classList.toggle('success', ok)
				row.classList.toggle('error', !ok)
			}
			if (valueEl) {
				valueEl.textContent = text
			}
		}

		const joinParts = (parts) => parts.filter((part) => {
			if (part === null || part === undefined) return false
			return String(part).trim() !== ''
		}).join(' | ')

		const formatHttp = (value) => Number.isFinite(value) ? `HTTP ${value}` : ''

		const pickMessage = (data, fallback) => {
			const errorsArray = Array.isArray(data?.errors) ? data.errors : null
			const firstError = errorsArray && errorsArray.length > 0
				? (typeof errorsArray[0] === 'string' ? errorsArray[0] : null)
				: null
			return toText(
				data?.message
				?? data?.error?.message
				?? data?.error?.details?.drfMessage
				?? data?.error?.details?.message
				?? data?.errors?.detail
				?? firstError
				?? fallback,
				fallback,
			)
		}

		const readObject = (value) => (
			value && typeof value === 'object' && !Array.isArray(value)
				? value
				: null
		)

		const parseEmbeddedErrorData = (value) => {
			if (typeof value !== 'string') {
				return null
			}
			const text = value.trim()
			if (!text || (!text.startsWith('{') && !text.startsWith('['))) {
				return null
			}
			try {
				const parsed = JSON.parse(text)
				return readObject(parsed)
			} catch {
				return null
			}
		}

		const readRetrySeconds = (...values) => {
			for (const value of values) {
				if (value === null || value === undefined || value === '') {
					continue
				}
				const parsed = typeof value === 'number'
					? value
					: Number.parseFloat(String(value))
				if (Number.isFinite(parsed) && parsed >= 0) {
					return Math.ceil(parsed)
				}
			}
			return null
		}

		const formatRetryDelay = (seconds) => {
			const totalSeconds = Math.max(1, Math.ceil(seconds))
			if (totalSeconds < 60) {
				return `${totalSeconds} second${totalSeconds === 1 ? '' : 's'}`
			}
			const minutes = Math.ceil(totalSeconds / 60)
			if (minutes < 60) {
				return `${minutes} minute${minutes === 1 ? '' : 's'}`
			}
			const hours = Math.floor(minutes / 60)
			const remainingMinutes = minutes % 60
			if (remainingMinutes === 0) {
				return `${hours} hour${hours === 1 ? '' : 's'}`
			}
			return `${hours} hour${hours === 1 ? '' : 's'} ${remainingMinutes} minute${remainingMinutes === 1 ? '' : 's'}`
		}

		const buildNdviErrorMessage = (response, data, fallback, rawText = '') => {
			const error = readObject(data?.error)
			const details = readObject(error?.details)
			const nestedErrors = readObject(details?.errors)
			const upstream = parseEmbeddedErrorData(details?.drfMessage)
			const upstreamErrors = readObject(upstream?.errors)
			const httpStatus = Number.isFinite(details?.httpStatus)
				? details.httpStatus
				: response?.status
			const errorCode = typeof error?.code === 'string'
				? error.code
				: (typeof data?.code === 'string' ? data.code : '')
			const errorsArray = Array.isArray(data?.errors) ? data.errors : null
			const firstError = errorsArray && errorsArray.length > 0
				? (typeof errorsArray[0] === 'string' ? errorsArray[0] : null)
				: null
			const detailMessage = toText(
				details?.detail
				?? nestedErrors?.detail
				?? upstreamErrors?.detail
				?? data?.errors?.detail
				?? firstError
				?? '',
				'',
			)
			const genericMessage = pickMessage(data, '')
			const isCooldown = httpStatus === 429
				|| errorCode === 'too_many_requests'
				|| /too many requests/i.test(genericMessage)
				|| /already queued recently/i.test(detailMessage)

			if (isCooldown) {
				const retryAfterHeader = response?.headers?.get
					? response.headers.get('retry-after')
					: null
				const waitSeconds = readRetrySeconds(
					details?.wait,
					nestedErrors?.wait,
					upstreamErrors?.wait,
					details?.retryAfter,
					retryAfterHeader,
				)
				const baseMessage = detailMessage || 'Raster already queued recently.'
				if (/try again/i.test(baseMessage)) {
					return baseMessage
				}
				if (waitSeconds !== null) {
					return `${baseMessage} Try again in about ${formatRetryDelay(waitSeconds)}.`
				}
				return `${baseMessage} Try again in about 15 minutes.`
			}

			const message = detailMessage || genericMessage
			if (message) {
				return message
			}

			const snippet = String(rawText ?? '').trim().slice(0, 200)
			return snippet || fallback
		}

		const shouldToastNdviError = (message) => /already queued recently|try again in about/i.test(String(message ?? ''))

		const buildConnectionErrorMessage = (response, data) => {
			const backendDetails = data?.error?.details && typeof data.error.details === 'object'
				? data.error.details
				: null
			const httpStatus = Number.isFinite(backendDetails?.httpStatus)
				? backendDetails.httpStatus
				: response?.status
			const backendMessage = toText(
				backendDetails?.message
				?? data?.message
				?? data?.error?.message
				?? '',
				'',
			)
			const backendErrors = backendDetails?.errors && typeof backendDetails.errors === 'object'
				? backendDetails.errors
				: null
			const errorCode = typeof backendErrors?.code === 'string' ? backendErrors.code : ''
			const errorReason = typeof backendErrors?.reason === 'string' ? backendErrors.reason : ''
			const topLevelCode = typeof data?.code === 'string'
				? data.code
				: (typeof data?.error?.code === 'string' ? data.error.code : '')

			const parts = []
			if (httpStatus) {
				parts.push(`HTTP ${httpStatus}`)
			}
			if (backendMessage) {
				parts.push(backendMessage)
			}
			if (errorCode || errorReason || topLevelCode) {
				const detailParts = []
				const combinedCode = errorCode || topLevelCode
				if (combinedCode) detailParts.push(`code=${combinedCode}`)
				if (errorReason) detailParts.push(`reason=${errorReason}`)
				parts.push(detailParts.join(' '))
			}

			return parts.join(' | ') || 'Connection failed.'
		}

		const requirePasswordConfirmationAsync = () => new Promise((resolve) => {
			const confirmation = window.OC?.PasswordConfirmation
			// TODO: If OC.PasswordConfirmation is unavailable in this build, wire @nextcloud/password-confirmation + CSS.
			if (!confirmation) {
				resolve()
				return
			}

			// If Nextcloud says no confirmation needed, proceed immediately
			if (typeof confirmation.requiresPasswordConfirmation === 'function') {
				try {
					if (!confirmation.requiresPasswordConfirmation()) {
						resolve()
						return
					}
				} catch {
					// fall through to requirePasswordConfirmation if present
				}
			}

			// Trigger the built-in password confirmation dialog if available
			if (typeof confirmation.requirePasswordConfirmation === 'function') {
				confirmation.requirePasswordConfirmation(() => resolve())
				return
			}

			resolve()
		})

		const isPasswordConfirmationRequired = (response, data) => {
			if (response.status === 412) return true
			if (response.status !== 403) return false
			const msg = pickMessage(data, '')
			return /password confirmation/i.test(msg)
		}

		/**
		 * Toast helper (Nextcloud-native).
		 * Keeps existing behavior intact: if toast API is missing, UI still works via inline status.
		 * @param {unknown} message Toast content or error.
		 */
		const toast = (message) => {
			const text = toText(message, '')
			if (!text) return

			const n = window.OC?.Notification
			if (!n) return

			// Prefer temporary toasts when supported.
			if (typeof n.showTemporary === 'function') {
				try { n.showTemporary(text) } catch { /* noop */ }
				return
			}

			if (typeof n.show === 'function') {
				try { n.show(text) } catch { /* noop */ }
			}
		}

		const readJsonResponse = async (response) => {
			const text = await response.text()
			if (!text) {
				return { parsed: false, data: {}, text }
			}

			try {
				return { parsed: true, data: JSON.parse(text), text }
			} catch {
				return { parsed: false, data: {}, text }
			}
		}

		const showParseError = (text) => {
			const snippet = text.trim().slice(0, 200)
			const message = snippet || 'Unable to parse response.'
			status.textContent = message
			status.classList.add('error')
			toast(message)
		}

		const showConnectionParseError = (text) => {
			if (!connectionStatus) {
				return
			}
			const snippet = text.trim().slice(0, 200)
			const message = snippet || 'Unable to parse response.'
			connectionStatus.textContent = message
			connectionStatus.classList.add('error')
			toast(message)
		}

		const clearCredentialsTimer = () => {
			if (credentialsClearTimer) {
				window.clearTimeout(credentialsClearTimer)
				credentialsClearTimer = null
			}
		}

		const clearCredentialsPanel = () => {
			clearCredentialsTimer()
			if (generatedClientIdInput) {
				generatedClientIdInput.value = ''
			}
			if (generatedSecretInput) {
				generatedSecretInput.value = ''
			}
			if (exportSnippetInput) {
				exportSnippetInput.value = ''
			}
			if (credentialsPanel) {
				credentialsPanel.hidden = true
			}
		}

		const scheduleCredentialsClear = () => {
			clearCredentialsTimer()
			credentialsClearTimer = window.setTimeout(() => {
				clearCredentialsPanel()
			}, CREDENTIALS_CLEAR_DELAY_MS)
		}

		const buildExportSnippet = (clientId, hmacSecret) => {
			if (!clientId || !hmacSecret) return ''
			const json = JSON.stringify({ [clientId]: hmacSecret })
			return `INTEGRATION_HMAC_CLIENT_ID='${clientId}'\nINTEGRATION_HMAC_CLIENTS_JSON='${json}'`
		}

		const showCredentials = (clientId, hmacSecret) => {
			if (generatedClientIdInput) {
				generatedClientIdInput.value = clientId
			}
			if (generatedSecretInput) {
				generatedSecretInput.value = hmacSecret
			}
			if (exportSnippetInput) {
				exportSnippetInput.value = buildExportSnippet(clientId, hmacSecret)
			}
			if (credentialsPanel) {
				credentialsPanel.hidden = false
			}
			scheduleCredentialsClear()
		}

		const copyToClipboard = async (value, inputEl) => {
			if (!value) return false

			if (navigator?.clipboard?.writeText) {
				try {
					await navigator.clipboard.writeText(value)
					return true
				} catch {
					// fall through to legacy copy
				}
			}

			if (!inputEl) return false

			try {
				inputEl.focus()
				inputEl.select()
				inputEl.setSelectionRange(0, inputEl.value.length)
				const copyOk = document.execCommand('copy')
				inputEl.blur()
				return copyOk
			} catch {
				return false
			}
		}

		const buildTokenFormData = () => {
			const formData = new FormData()
			const token = resolveRequestToken()
			if (token) {
				formData.set('requesttoken', token)
			}
			return formData
		}

		const isAbsoluteUrl = (value) => /^https?:\/\//i.test(String(value ?? ''))

		const resolveRequestUrl = (url) => {
			const raw = String(url ?? '').trim()
			if (!raw) {
				return ''
			}
			if (isAbsoluteUrl(raw)) {
				return raw
			}
			if (raw.includes('/index.php/')) {
				return raw
			}
			const webroot = String(window.OC?.webroot ?? '')
			let normalized = raw
			if (webroot && normalized.startsWith(`${webroot}/`)) {
				normalized = normalized.slice(webroot.length)
			}
			const generator = window.OC?.generateUrl
			if (typeof generator === 'function') {
				return generator(normalized)
			}
			return normalized
		}

		const normalizeAxiosUrl = (url, axiosClient) => {
			const baseUrl = axiosClient?.defaults?.baseURL
			if (typeof baseUrl !== 'string' || baseUrl === '') {
				return url
			}
			const normalizedBase = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl
			if (url.startsWith(`${normalizedBase}/`)) {
				const trimmed = url.slice(normalizedBase.length)
				return trimmed.startsWith('/') ? trimmed : `/${trimmed}`
			}
			return url
		}

		const performAdminRequest = async (url) => {
			const resolvedUrl = resolveRequestUrl(url)
			const token = resolveRequestToken()
			console.info('[farm_intelligence_platform] POST', resolvedUrl)
			const response = await fetch(resolvedUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					Accept: 'application/json',
					requesttoken: token,
					'OCS-APIRequest': 'true',
					'X-Requested-With': 'XMLHttpRequest',
				},
				body: buildTokenFormData(),
			})

			const { parsed, data, text } = await readJsonResponse(response)
			return { response, parsed, data, text }
		}

		const performAdminGet = async (url) => {
			const resolvedUrl = resolveRequestUrl(url)
			const token = resolveRequestToken()
			console.info('[farm_intelligence_platform] GET', resolvedUrl)
			const response = await fetch(resolvedUrl, {
				method: 'GET',
				credentials: 'same-origin',
				headers: {
					Accept: 'application/json',
					requesttoken: token,
					'OCS-APIRequest': 'true',
					'X-Requested-With': 'XMLHttpRequest',
				},
			})

			const { parsed, data, text } = await readJsonResponse(response)
			return { response, parsed, data, text }
		}

		const buildQueryString = (params) => {
			if (!params || typeof params !== 'object') {
				return ''
			}
			const search = new URLSearchParams()
			Object.entries(params).forEach(([key, value]) => {
				if (value === undefined || value === null || value === '') {
					return
				}
				if (Array.isArray(value)) {
					value.forEach((item) => {
						if (item === undefined || item === null || item === '') {
							return
						}
						search.append(key, String(item))
					})
					return
				}
				search.append(key, String(value))
			})
			return search.toString()
		}

		const performJsonRequest = async (method, url, options = {}) => {
			const token = resolveRequestToken()
			const headers = {
				Accept: 'application/json',
				'OCS-APIRequest': 'true',
				'X-Requested-With': 'XMLHttpRequest',
			}
			if (token) {
				headers.requesttoken = token
			}
			if (options.headers && typeof options.headers === 'object') {
				Object.entries(options.headers).forEach(([name, value]) => {
					if (value === undefined || value === null) {
						return
					}
					headers[name] = String(value)
				})
			}

			const resolvedUrl = resolveRequestUrl(url)
			const queryString = buildQueryString(options.query)
			const finalUrl = queryString
				? `${resolvedUrl}${resolvedUrl.includes('?') ? '&' : '?'}${queryString}`
				: resolvedUrl

			const axiosClient = window.OC?.axios || window.axios
			if (axiosClient) {
				try {
					const axiosUrl = normalizeAxiosUrl(finalUrl, axiosClient)
					const response = await axiosClient({
						method,
						url: axiosUrl,
						data: options.body,
						headers,
						withCredentials: true,
					})

					let parsed = true
					let data = response.data
					if (typeof data === 'string') {
						try {
							data = JSON.parse(data)
						} catch {
							parsed = false
						}
					}

					return {
						response: { ok: response.status >= 200 && response.status < 300, status: response.status },
						parsed,
						data: parsed ? data : {},
						text: parsed ? '' : String(response.data ?? ''),
					}
				} catch (error) {
					const response = error?.response
					if (response) {
						let parsed = true
						let data = response.data
						if (typeof data === 'string') {
							try {
								data = JSON.parse(data)
							} catch {
								parsed = false
							}
						}
						return {
							response: { ok: false, status: response.status },
							parsed,
							data: parsed ? data : {},
							text: parsed ? '' : String(response.data ?? ''),
						}
					}
					throw error
				}
			}

			const fetchOptions = {
				method,
				credentials: 'same-origin',
				headers,
			}

			if (options.body && method !== 'GET') {
				fetchOptions.headers['Content-Type'] = 'application/json'
				fetchOptions.body = JSON.stringify(options.body)
			}

			const response = await fetch(finalUrl, fetchOptions)
			const { parsed, data, text } = await readJsonResponse(response)
			return { response, parsed, data, text }
		}

		const PNG_SIGNATURE = new Uint8Array([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a])

		const hasPngSignature = (buffer) => {
			if (!buffer || buffer.byteLength < PNG_SIGNATURE.length) {
				return false
			}
			const bytes = new Uint8Array(buffer.slice(0, PNG_SIGNATURE.length))
			for (let i = 0; i < PNG_SIGNATURE.length; i += 1) {
				if (bytes[i] !== PNG_SIGNATURE[i]) {
					return false
				}
			}
			return true
		}

		const buildPreviewUrl = (url) => {
			const resolved = resolveRequestUrl(url)
			if (!resolved) {
				return ''
			}
			const separator = resolved.includes('?') ? '&' : '?'
			return `${resolved}${separator}ts=${Date.now()}`
		}

		const fetchPreviewDiagnostics = async (url) => {
			const requestUrl = buildPreviewUrl(url)
			if (!requestUrl) {
				return null
			}
			const token = resolveRequestToken()
			const headers = {
				Accept: 'image/png',
				'OCS-APIRequest': 'true',
				'X-Requested-With': 'XMLHttpRequest',
			}
			if (token) {
				headers.requesttoken = token
			}
			try {
				const response = await fetch(requestUrl, {
					method: 'GET',
					credentials: 'same-origin',
					cache: 'no-store',
					headers,
				})
				const contentType = response.headers.get('content-type') || ''
				const buffer = await response.arrayBuffer()
				const size = buffer.byteLength
				const signatureOk = hasPngSignature(buffer)
				const ok = response.ok && contentType.includes('image/png') && signatureOk
				return {
					ok,
					http: response.status,
					contentType,
					size,
					signatureOk,
					url: requestUrl,
				}
			} catch (error) {
				const message = error instanceof Error ? error.message : 'Preview request failed.'
				return { ok: false, error: message, url: requestUrl }
			}
		}

		const setupRadio = () => {
			const radioRoot = document.getElementById('farm-intelligence-platform-radio')
			if (!radioRoot) return

			const radioPlayer = document.getElementById('farm-intelligence-platform-radio-player')
			const radioPlayerModal = document.getElementById('farm-intelligence-platform-radio-player-modal')
			const radioPlayerTitle = document.getElementById('farm-intelligence-platform-radio-player-title')
			const radioPlayerSubtitle = document.getElementById('farm-intelligence-platform-radio-player-subtitle')
			const radioPlayerClose = document.getElementById('farm-intelligence-platform-radio-player-close')
			const radioPlayerMinimize = document.getElementById('farm-intelligence-platform-radio-player-minimize')
			const radioPlayerLogo = document.getElementById('farm-intelligence-platform-radio-player-logo')
			const radioPlayerIcon = document.getElementById('farm-intelligence-platform-radio-player-icon')
			const radioPlayerPlay = document.getElementById('farm-intelligence-platform-radio-player-play')
			const radioPlayerRewind = document.getElementById('farm-intelligence-platform-radio-player-rewind')
			const radioPlayerForward = document.getElementById('farm-intelligence-platform-radio-player-forward')
			const radioIconPlay = document.getElementById('farm-intelligence-platform-radio-icon-play')
			const radioIconPause = document.getElementById('farm-intelligence-platform-radio-icon-pause')
			const radioVolume = document.getElementById('farm-intelligence-platform-radio-volume')
			const radioAudio = document.getElementById('farm-intelligence-platform-radio-audio')

			const radioBarLogo = document.getElementById('farm-intelligence-platform-radio-bar-logo')
			const radioBarTitle = document.getElementById('farm-intelligence-platform-radio-bar-title')
			const radioBarTime = document.getElementById('farm-intelligence-platform-radio-bar-time')
			const radioBarLive = document.getElementById('farm-intelligence-platform-radio-bar-live')
			const radioBarRewind = document.getElementById('farm-intelligence-platform-radio-bar-rewind')
			const radioBarForward = document.getElementById('farm-intelligence-platform-radio-bar-forward')
			const radioBarPlay = document.getElementById('farm-intelligence-platform-radio-bar-play')
			const radioBarIconPlay = document.getElementById('farm-intelligence-platform-radio-bar-icon-play')
			const radioBarIconPause = document.getElementById('farm-intelligence-platform-radio-bar-icon-pause')
			const radioBarExpand = document.getElementById('farm-intelligence-platform-radio-bar-expand')
			const radioBarClose = document.getElementById('farm-intelligence-platform-radio-bar-close')

			const radioProgressTrack = document.getElementById('farm-intelligence-platform-radio-progress-track')
			const radioModalProgressTrack = document.getElementById('farm-intelligence-platform-radio-modal-progress-track')
			const radioProgressFill = document.getElementById('farm-intelligence-platform-radio-progress-fill')
			const radioModalProgressFill = document.getElementById('farm-intelligence-platform-radio-modal-progress-fill')
			const radioPlayerTime = document.getElementById('farm-intelligence-platform-radio-player-time')
			const radioPlayerLive = document.getElementById('farm-intelligence-platform-radio-player-live')

			const loadHlsJs = () => {
				if (window.Hls) return Promise.resolve()
				return new Promise((resolve, reject) => {
					const s = document.createElement('script')
					s.src = 'https://cdn.jsdelivr.net/npm/hls.js@latest'
					s.onload = resolve
					s.onerror = reject
					document.head.appendChild(s)
				})
			}
			loadHlsJs().catch(() => { console.warn('[farm_intelligence_platform] failed to load HLS.js') })

			const radioProvidersUrl = form.dataset.radioProvidersUrl || ''
			const radioStationsUrl = form.dataset.radioStationsUrl || ''
			const radioStreamUrl = form.dataset.radioStreamUrl || ''
			const radioNowPlayingUrl = form.dataset.radioNowPlayingUrl || ''
			const radioAnalyticsUrl = form.dataset.radioAnalyticsUrl || ''
			const radioStationHealthUrl = form.dataset.radioStationHealthUrl || ''
			const radioStationHealthHistoryUrl = form.dataset.radioStationHealthHistoryUrl || ''
			const radioHealthUrl = form.dataset.radioHealthUrl || ''
			const radioEmergencyCurrentUrl = form.dataset.radioEmergencyCurrentUrl || ''
			const radioEmergencyHistoryUrl = form.dataset.radioEmergencyHistoryUrl || ''
			const radioEmergencyCreateUrl = form.dataset.radioEmergencyCreateUrl || ''
			const radioEmergencyUpdateUrl = form.dataset.radioEmergencyUpdateUrl || ''
			const radioEmergencyDeleteUrl = form.dataset.radioEmergencyDeleteUrl || ''
			const radioTtsUrl = form.dataset.radioTtsUrl || ''

			const radioRefresh = document.getElementById('farm-intelligence-platform-radio-refresh')
			const radioStationsTab = document.getElementById('farm-intelligence-platform-radio-stations-tab')
			const radioProvidersTab = document.getElementById('farm-intelligence-platform-radio-providers-tab')
			const radioLoading = document.getElementById('farm-intelligence-platform-radio-loading')
			const radioError = document.getElementById('farm-intelligence-platform-radio-error')
			const radioStationsPanel = document.getElementById('farm-intelligence-platform-radio-stations')
			const radioProvidersPanel = document.getElementById('farm-intelligence-platform-radio-providers')
			const radioSearch = document.getElementById('farm-intelligence-platform-radio-search')
			const radioGenreFilter = document.getElementById('farm-intelligence-platform-radio-genre-filter')
			const radioCountryFilter = document.getElementById('farm-intelligence-platform-radio-country-filter')
			const radioStationsColumns = document.getElementById('farm-intelligence-platform-radio-stations-columns')
			const radioStationsBody = document.getElementById('farm-intelligence-platform-radio-stations-body')
			const radioStationsEmpty = document.getElementById('farm-intelligence-platform-radio-stations-empty')
			const radioProvidersColumns = document.getElementById('farm-intelligence-platform-radio-providers-columns')
			const radioProvidersBody = document.getElementById('farm-intelligence-platform-radio-providers-body')
			const radioProvidersEmpty = document.getElementById('farm-intelligence-platform-radio-providers-empty')

			const radioEmergencyBanner = document.getElementById('farm-intelligence-platform-radio-emergency')
			const radioEmergencyTitle = document.getElementById('farm-intelligence-platform-radio-emergency-title')
			const radioEmergencyPriority = document.getElementById('farm-intelligence-platform-radio-emergency-priority')
			const radioEmergencyMessage = document.getElementById('farm-intelligence-platform-radio-emergency-message')
			const radioEmergencyWindow = document.getElementById('farm-intelligence-platform-radio-emergency-window')
			const radioEmergencyHistoryBtn = document.getElementById('farm-intelligence-platform-radio-emergency-history-btn')
			const radioEmergencyHistoryModal = document.getElementById('farm-intelligence-platform-radio-emergency-history-modal')
			const radioEmergencyHistoryModalClose = document.getElementById('farm-intelligence-platform-radio-emergency-history-modal-close')
			const radioEmergencyHistoryBody = document.getElementById('farm-intelligence-platform-radio-emergency-history-body')
			const radioEmergencyHistoryEmpty = document.getElementById('farm-intelligence-platform-radio-emergency-history-empty')
			const radioEmergencyHistoryLoading = document.getElementById('farm-intelligence-platform-radio-emergency-history-loading')

			const radioEmergencyCreateBtn = document.getElementById('farm-intelligence-platform-radio-emergency-create-btn')
			const radioEmergencyMgmtRefresh = document.getElementById('farm-intelligence-platform-radio-emergency-mgmt-refresh')
			const radioEmergencyMgmtBody = document.getElementById('farm-intelligence-platform-radio-emergency-mgmt-body')
			const radioEmergencyMgmtEmpty = document.getElementById('farm-intelligence-platform-radio-emergency-mgmt-empty')
			const radioEmergencyModal = document.getElementById('farm-intelligence-platform-radio-emergency-modal')
			const radioEmergencyModalClose = document.getElementById('farm-intelligence-platform-radio-emergency-modal-close')
			const radioEmergencyModalTitle = document.getElementById('farm-intelligence-platform-radio-emergency-modal-title')
			const radioEmergencyModalTitleInput = document.getElementById('farm-intelligence-platform-radio-emergency-modal-title-input')
			const radioEmergencyModalMessageInput = document.getElementById('farm-intelligence-platform-radio-emergency-modal-message-input')
			const radioEmergencyModalPrioritySelect = document.getElementById('farm-intelligence-platform-radio-emergency-modal-priority-select')
			const radioEmergencyModalStartsInput = document.getElementById('farm-intelligence-platform-radio-emergency-modal-starts-input')
			const radioEmergencyModalEndsInput = document.getElementById('farm-intelligence-platform-radio-emergency-modal-ends-input')
			const radioEmergencyModalSave = document.getElementById('farm-intelligence-platform-radio-emergency-modal-save')
			const radioEmergencyModalCancel = document.getElementById('farm-intelligence-platform-radio-emergency-modal-cancel')
			const radioEmergencyModalError = document.getElementById('farm-intelligence-platform-radio-emergency-modal-error')

			const radioTtsText = document.getElementById('farm-intelligence-platform-radio-tts-text')
			const radioTtsVoice = document.getElementById('farm-intelligence-platform-radio-tts-voice')
			const radioTtsSynthesizeBtn = document.getElementById('farm-intelligence-platform-radio-tts-synthesize-btn')
			const radioTtsDownloadBtn = document.getElementById('farm-intelligence-platform-radio-tts-download-btn')
			const radioTtsAudio = document.getElementById('farm-intelligence-platform-radio-tts-audio')
			const radioTtsDuration = document.getElementById('farm-intelligence-platform-radio-tts-duration')
			const radioTtsError = document.getElementById('farm-intelligence-platform-radio-tts-error')

			const radioHealthBox = document.getElementById('farm-intelligence-platform-radio-health')
			const radioHealthStatus = document.getElementById('farm-intelligence-platform-radio-health-status')
			const radioHealthTotal = document.getElementById('farm-intelligence-platform-radio-health-total')
			const radioHealthAvailable = document.getElementById('farm-intelligence-platform-radio-health-available')
			const radioHealthUnavailable = document.getElementById('farm-intelligence-platform-radio-health-unavailable')
			const radioHealthLastProbe = document.getElementById('farm-intelligence-platform-radio-health-last-probe')

			const stationModal = document.getElementById('farm-intelligence-platform-radio-station-modal')
			const stationModalClose = document.getElementById('farm-intelligence-platform-radio-station-modal-close')
			const stationModalLogo = document.getElementById('farm-intelligence-platform-radio-station-modal-logo')
			const stationModalName = document.getElementById('farm-intelligence-platform-radio-station-modal-name')
			const stationModalProvider = document.getElementById('farm-intelligence-platform-radio-station-modal-provider')
			const stationModalGenre = document.getElementById('farm-intelligence-platform-radio-station-modal-genre')
			const stationModalCountry = document.getElementById('farm-intelligence-platform-radio-station-modal-country')
			const stationModalDescription = document.getElementById('farm-intelligence-platform-radio-station-modal-description')
			const stationTabNowPlaying = document.getElementById('farm-intelligence-platform-radio-station-tab-now-playing')
			const stationTabAnalytics = document.getElementById('farm-intelligence-platform-radio-station-tab-analytics')
			const stationTabHealth = document.getElementById('farm-intelligence-platform-radio-station-tab-health')
			const stationPanelNowPlaying = document.getElementById('farm-intelligence-platform-radio-station-panel-now-playing')
			const stationPanelAnalytics = document.getElementById('farm-intelligence-platform-radio-station-panel-analytics')
			const stationPanelHealth = document.getElementById('farm-intelligence-platform-radio-station-panel-health')
			const stationLoading = document.getElementById('farm-intelligence-platform-radio-station-loading')
			const stationError = document.getElementById('farm-intelligence-platform-radio-station-error')
			const stationNowPlayingTrack = document.getElementById('farm-intelligence-platform-radio-station-now-playing-track')
			const stationNowPlayingArtist = document.getElementById('farm-intelligence-platform-radio-station-now-playing-artist')
			const stationNowPlayingAlbum = document.getElementById('farm-intelligence-platform-radio-station-now-playing-album')
			const stationNowPlayingUpdated = document.getElementById('farm-intelligence-platform-radio-station-now-playing-updated')
			const stationNowPlayingEmpty = document.getElementById('farm-intelligence-platform-radio-station-now-playing-empty')
			const stationNowPlayingArt = document.getElementById('farm-intelligence-platform-radio-station-now-playing-art')
			const stationAnalyticsDays = document.getElementById('farm-intelligence-platform-radio-station-analytics-days')
			const stationAnalyticsRefresh = document.getElementById('farm-intelligence-platform-radio-station-analytics-refresh')
			const stationAnalyticsTotalListens = document.getElementById('farm-intelligence-platform-radio-station-analytics-total-listens')
			const stationAnalyticsTotalDuration = document.getElementById('farm-intelligence-platform-radio-station-analytics-total-duration')
			const stationAnalyticsUniqueUsers = document.getElementById('farm-intelligence-platform-radio-station-analytics-unique-users')
			const stationAnalyticsBody = document.getElementById('farm-intelligence-platform-radio-station-analytics-body')
			const stationAnalyticsEmpty = document.getElementById('farm-intelligence-platform-radio-station-analytics-empty')
			const stationHealthStatus = document.getElementById('farm-intelligence-platform-radio-station-health-status')
			const stationHealthLastProbe = document.getElementById('farm-intelligence-platform-radio-station-health-last-probe')
			const stationHealthLatency = document.getElementById('farm-intelligence-platform-radio-station-health-latency')
			const stationHealthHttp = document.getElementById('farm-intelligence-platform-radio-station-health-http')
			const stationHealthHistoryBody = document.getElementById('farm-intelligence-platform-radio-station-health-history-body')
			const stationHealthHistoryEmpty = document.getElementById('farm-intelligence-platform-radio-station-health-history-empty')

			let stationsData = []
			let providersData = []
			let radioProgressFrame = null

			const clearRadioNotes = () => {
				if (radioError) { radioError.textContent = ''; radioError.hidden = true }
				if (radioLoading) radioLoading.hidden = true
			}

			const stopProgressAnimation = () => {
				if (radioProgressFrame !== null) {
					cancelAnimationFrame(radioProgressFrame)
					radioProgressFrame = null
				}
			}

			const setProgressWidth = (fill, percent) => {
				if (!fill) return
				const clamped = Math.max(0, Math.min(100, percent))
				fill.style.width = `${clamped}%`
			}

			const updateLiveBadges = (isLive) => {
				if (radioBarLive) radioBarLive.hidden = !isLive
				if (radioPlayerLive) radioPlayerLive.hidden = !isLive
			}

			const updateProgressBars = () => {
				if (!radioAudio) return
				const seekable = radioAudio.seekable
				const buffered = radioAudio.buffered
				const hasSeekableWindow = seekable && seekable.length > 0 && Number.isFinite(seekable.end(0)) && seekable.end(0) > seekable.start(0)
				const hasDuration = Number.isFinite(radioAudio.duration) && radioAudio.duration > 0
				const isLive = !hasDuration || radioAudio.duration === Infinity || !hasSeekableWindow
				updateLiveBadges(isLive)

				if (isLive) {
					const windowEnd = hasSeekableWindow ? seekable.end(0) : (buffered && buffered.length > 0 ? buffered.end(buffered.length - 1) : radioAudio.currentTime + 30)
					const windowStart = hasSeekableWindow ? seekable.start(0) : Math.max(0, windowEnd - 30)
					const denominator = Math.max(1, windowEnd - windowStart)
					const percent = ((radioAudio.currentTime - windowStart) / denominator) * 100
					setProgressWidth(radioProgressFill, percent)
					setProgressWidth(radioModalProgressFill, percent)
					return
				}

				const percent = (radioAudio.currentTime / radioAudio.duration) * 100
				setProgressWidth(radioProgressFill, percent)
				setProgressWidth(radioModalProgressFill, percent)
			}

			const seekRadioToPercent = (track, clientX) => {
				if (!radioAudio || !track) return
				const rect = track.getBoundingClientRect()
				if (rect.width <= 0) return

				const offset = Math.max(0, Math.min(rect.width, clientX - rect.left))
				const ratio = offset / rect.width
				const seekable = radioAudio.seekable
				const buffered = radioAudio.buffered
				const hasSeekableWindow = seekable && seekable.length > 0 && Number.isFinite(seekable.end(0)) && seekable.end(0) > seekable.start(0)

				if (hasSeekableWindow) {
					const start = seekable.start(0)
					const end = seekable.end(0)
					radioAudio.currentTime = start + (end - start) * ratio
					updateProgressBars()
					return
				}

				if (Number.isFinite(radioAudio.duration) && radioAudio.duration > 0) {
					radioAudio.currentTime = radioAudio.duration * ratio
					updateProgressBars()
					return
				}

				if (buffered && buffered.length > 0) {
					const start = buffered.start(0)
					const end = buffered.end(buffered.length - 1)
					radioAudio.currentTime = start + (end - start) * ratio
					updateProgressBars()
					return
				}

				showRadioError('This live stream does not expose a rewind buffer.')
			}

			const startProgressAnimation = () => {
				stopProgressAnimation()
				const tick = () => {
					updateProgressBars()
					radioProgressFrame = window.requestAnimationFrame(tick)
				}
				tick()
			}

			const showRadioError = (msg) => {
				if (!radioError) return
				radioError.textContent = msg
				radioError.hidden = false
			}

			const unwrapOcsEnvelope = (data) => {
				if (data?.ocs?.data !== undefined) return data.ocs.data
				return data
			}

			const unwrapResponseData = (data) => {
				const unwrapped = unwrapOcsEnvelope(data)
				if (unwrapped && typeof unwrapped === 'object' && unwrapped.data !== undefined) {
					const inner = unwrapped.data
					if (Array.isArray(inner)) return inner
					if (inner && typeof inner === 'object') {
						if (Array.isArray(inner.results)) return inner.results
						if (Array.isArray(inner.data)) return inner.data
					}
					return inner
				}
				return unwrapped ?? {}
			}

			const isOcsSuccess = (data) => {
				if (data?.ocs?.meta?.status === 'ok') return true
				const inner = unwrapOcsEnvelope(data)
				return inner?.status === 'ok' || inner?.status === 0 || inner?.ok === true
			}

			const loadStations = async () => {
				clearRadioNotes()
				if (!radioStationsUrl) { showRadioError('Radio stations endpoint not available.'); return }
				if (radioLoading) radioLoading.hidden = false
				try {
					const result = await performJsonRequest('GET', radioStationsUrl)
					if (radioLoading) radioLoading.hidden = true
					if (!result.parsed) { console.error('[farm_intelligence_platform] radio parse failed', result.text); showRadioError('Unable to parse radio stations response.'); return }
					console.info('[farm_intelligence_platform] radio stations response', result.data)
					const ok = isOcsSuccess(result.data)
					if (!ok) { console.error('[farm_intelligence_platform] radio not ok', result.data); showRadioError(result.data?.ocs?.meta?.message || result.data?.message || 'Unable to load radio stations.'); return }
					stationsData = unwrapResponseData(result.data)
					if (!Array.isArray(stationsData)) stationsData = []
					renderStations()
					populateFilters()
				} catch (e) {
					if (radioLoading) radioLoading.hidden = true
					showRadioError('Failed to load radio stations.')
				}
			}

			const loadProviders = async () => {
				clearRadioNotes()
				if (!radioProvidersUrl) { showRadioError('Radio providers endpoint not available.'); return }
				if (radioLoading) radioLoading.hidden = false
				try {
					const result = await performJsonRequest('GET', radioProvidersUrl)
					if (radioLoading) radioLoading.hidden = true
					if (!result.parsed) { showRadioError('Unable to parse radio providers response.'); return }
					const ok = isOcsSuccess(result.data)
					if (!ok) { showRadioError(result.data?.ocs?.meta?.message || result.data?.message || 'Unable to load radio providers.'); return }
					providersData = unwrapResponseData(result.data)
					if (!Array.isArray(providersData)) providersData = []
					renderProviders()
				} catch (e) {
					if (radioLoading) radioLoading.hidden = true
					showRadioError('Failed to load radio providers.')
				}
			}

			const renderStations = () => {
				if (!radioStationsBody || !radioStationsColumns) return
				const query = (radioSearch?.value || '').toLowerCase()
				const genre = radioGenreFilter?.value || ''
				const country = radioCountryFilter?.value || ''
				const filtered = stationsData.filter(s => {
					if (genre && s.genre !== genre) return false
					if (country && s.country !== country) return false
					if (query) {
						const text = `${s.name} ${s.genre} ${s.country} ${s.provider_name || ''}`.toLowerCase()
						if (!text.includes(query)) return false
					}
					return true
				})
				radioStationsColumns.innerHTML = ''
				radioStationsBody.innerHTML = ''
				if (filtered.length === 0) {
					if (radioStationsEmpty) radioStationsEmpty.hidden = false
					return
				}
				if (radioStationsEmpty) radioStationsEmpty.hidden = true
				const cols = ['logo', 'name', 'provider_name', 'genre', 'country', 'language']
				cols.forEach(c => {
					const th = document.createElement('th')
					th.textContent = c === 'logo' ? '' : c.replace(/_/g, ' ')
					radioStationsColumns.appendChild(th)
				})
				const th = document.createElement('th')
				th.textContent = 'Details'
				radioStationsColumns.appendChild(th)
				const thPlay = document.createElement('th')
				thPlay.textContent = 'Play'
				radioStationsColumns.appendChild(thPlay)
				filtered.forEach(station => {
					const tr = document.createElement('tr')
					cols.forEach(c => {
						const td = document.createElement('td')
						if (c === 'logo') {
							const logoUrl = station.logo_url || station.provider_logo_url || ''
							if (logoUrl) {
								const img = document.createElement('img')
								img.src = logoUrl
								img.alt = ''
								img.style.cssText = 'width:24px;height:24px;border-radius:4px;object-fit:cover;vertical-align:middle;'
								img.onerror = () => { img.hidden = true }
								td.appendChild(img)
							} else {
								td.textContent = '—'
							}
						} else if (c === 'name') {
							const nameLink = document.createElement('button')
							nameLink.type = 'button'
							nameLink.className = 'button farm-intelligence-platform-radio__name-link'
							nameLink.textContent = station.name || '—'
							nameLink.title = 'Open station details'
							nameLink.addEventListener('click', () => openStationModal(station))
							td.appendChild(nameLink)
						} else {
							td.textContent = station[c] || '—'
						}
						tr.appendChild(td)
					})
					const detailsTd = document.createElement('td')
					const detailsBtn = document.createElement('button')
					detailsBtn.type = 'button'
					detailsBtn.className = 'button'
					detailsBtn.textContent = 'Details'
					detailsBtn.addEventListener('click', () => openStationModal(station))
					detailsTd.appendChild(detailsBtn)
					tr.appendChild(detailsTd)
					const playTd = document.createElement('td')
					const playBtn = document.createElement('button')
					playBtn.type = 'button'
					playBtn.className = 'button'
					playBtn.textContent = 'Play'
					playBtn.addEventListener('click', () => playStation(station))
					playTd.appendChild(playBtn)
					tr.appendChild(playTd)
					radioStationsBody.appendChild(tr)
				})
			}

			const renderProviders = () => {
				if (!radioProvidersBody || !radioProvidersColumns) return
				radioProvidersColumns.innerHTML = ''
				radioProvidersBody.innerHTML = ''
				if (providersData.length === 0) {
					if (radioProvidersEmpty) radioProvidersEmpty.hidden = false
					return
				}
				if (radioProvidersEmpty) radioProvidersEmpty.hidden = true
				const cols = ['name', 'slug', 'website_url']
				cols.forEach(c => {
					const th = document.createElement('th')
					th.textContent = c.replace(/_/g, ' ')
					radioProvidersColumns.appendChild(th)
				})
				providersData.forEach(p => {
					const tr = document.createElement('tr')
					cols.forEach(c => {
						const td = document.createElement('td')
						td.textContent = p[c] || '—'
						tr.appendChild(td)
					})
					radioProvidersBody.appendChild(tr)
				})
			}

			const populateFilters = () => {
				if (!radioGenreFilter || !radioCountryFilter) return
				const genres = [...new Set(stationsData.map(s => s.genre).filter(Boolean))].sort()
				const countries = [...new Set(stationsData.map(s => s.country).filter(Boolean))].sort()
				radioGenreFilter.innerHTML = '<option value="">All genres</option>'
				genres.forEach(g => {
					const opt = document.createElement('option')
					opt.value = g
					opt.textContent = g
					radioGenreFilter.appendChild(opt)
				})
				radioCountryFilter.innerHTML = '<option value="">All countries</option>'
				countries.forEach(c => {
					const opt = document.createElement('option')
					opt.value = c
					opt.textContent = c
					radioCountryFilter.appendChild(opt)
				})
			}

			const setStationPanelError = (msg) => {
				if (!stationError) return
				if (msg) {
					stationError.textContent = msg
					stationError.hidden = false
				} else {
					stationError.textContent = ''
					stationError.hidden = true
				}
			}

			const setStationPanelLoading = (on) => {
				if (stationLoading) stationLoading.hidden = !on
			}

			const formatDateTime = (raw) => {
				if (!raw) return '—'
				const d = new Date(raw)
				if (Number.isNaN(d.getTime())) return raw
				try { return d.toLocaleString() } catch (e) { return d.toString() }
			}

			const formatDuration = (seconds) => {
				if (seconds === null || seconds === undefined) return '—'
				const n = Number(seconds)
				if (!Number.isFinite(n) || n < 0) return '—'
				if (n < 60) return `${n.toFixed(0)} s`
				const h = Math.floor(n / 3600)
				const m = Math.floor((n % 3600) / 60)
				const s = Math.floor(n % 60)
				if (h > 0) return `${h}h ${m}m`
				if (m > 0) return `${m}m ${s}s`
				return `${s}s`
			}

			const loadRadioHealth = async () => {
				if (!radioHealthUrl || !radioHealthBox) return
				try {
					const result = await performJsonRequest('GET', radioHealthUrl)
					if (!result.parsed || !isOcsSuccess(result.data)) { return }
					const payload = unwrapResponseData(result.data)
					const data = payload?.data ?? payload
					if (!data || typeof data !== 'object') return
					radioHealthBox.hidden = false
					const total = data.total ?? data.stations_total ?? data.total_stations
					const available = data.available ?? data.stations_available
					const unavailable = data.unavailable ?? data.stations_unavailable
					const lastProbe = data.timestamp ?? data.last_probe_at ?? data.last_health_check_at
					const healthy = data.healthy ?? data.ok ?? (unavailable === 0)
					if (radioHealthTotal) radioHealthTotal.textContent = total ?? '—'
					if (radioHealthAvailable) radioHealthAvailable.textContent = available ?? '—'
					if (radioHealthUnavailable) radioHealthUnavailable.textContent = unavailable ?? '—'
					if (radioHealthLastProbe) radioHealthLastProbe.textContent = formatDateTime(lastProbe)
					if (radioHealthStatus) {
						radioHealthStatus.textContent = healthy ? 'Healthy' : 'Degraded'
						radioHealthStatus.classList.toggle('ok', !!healthy)
						radioHealthStatus.classList.toggle('error', !healthy)
					}
				} catch (e) {
					console.warn('[farm_intelligence_platform] radio health load failed', e)
				}
			}

			const loadCurrentEmergency = async () => {
				if (!radioEmergencyCurrentUrl || !radioEmergencyBanner) return
				try {
					const result = await performJsonRequest('GET', radioEmergencyCurrentUrl)
					if (!result.parsed || !isOcsSuccess(result.data)) { return }
					const payload = unwrapResponseData(result.data)
					const data = payload?.data ?? payload
					if (!data) return
					if (data === null || (typeof data === 'object' && Object.keys(data).length === 0)) {
						radioEmergencyBanner.hidden = true
						return
					}
					const priority = (data.priority || 'low').toLowerCase()
					const priorityClass = ['low', 'medium', 'high', 'critical'].includes(priority) ? priority : 'low'
					radioEmergencyBanner.hidden = false
					radioEmergencyBanner.classList.remove('low', 'medium', 'high', 'critical')
					radioEmergencyBanner.classList.add(priorityClass)
					if (radioEmergencyTitle) radioEmergencyTitle.textContent = data.title || 'Emergency broadcast'
					if (radioEmergencyPriority) radioEmergencyPriority.textContent = (priority || 'low').toUpperCase()
					if (radioEmergencyMessage) radioEmergencyMessage.textContent = data.message || ''
					const windowText = `${formatDateTime(data.starts_at)} → ${formatDateTime(data.ends_at)}`
					if (radioEmergencyWindow) radioEmergencyWindow.textContent = windowText
				} catch (e) {
					console.warn('[farm_intelligence_platform] emergency load failed', e)
				}
			}

			const loadEmergencyHistory = async () => {
				if (!radioEmergencyHistoryUrl || !radioEmergencyHistoryBody) return
				if (radioEmergencyHistoryLoading) radioEmergencyHistoryLoading.hidden = false
				radioEmergencyHistoryBody.innerHTML = ''
				try {
					const result = await performJsonRequest('GET', `${radioEmergencyHistoryUrl}?limit=50`)
					if (radioEmergencyHistoryLoading) radioEmergencyHistoryLoading.hidden = true
					if (!result.parsed || !isOcsSuccess(result.data)) {
						if (radioEmergencyHistoryEmpty) {
							radioEmergencyHistoryEmpty.textContent = 'Unable to load emergency history.'
							radioEmergencyHistoryEmpty.hidden = false
						}
						return
					}
					const payload = unwrapResponseData(result.data)
					const data = payload?.data ?? payload
					const rows = Array.isArray(data) ? data : (Array.isArray(data?.results) ? data.results : [])
					if (rows.length === 0) {
						if (radioEmergencyHistoryEmpty) radioEmergencyHistoryEmpty.hidden = false
						return
					}
					if (radioEmergencyHistoryEmpty) radioEmergencyHistoryEmpty.hidden = true
					rows.forEach(item => {
						const tr = document.createElement('tr')
						const tdTitle = document.createElement('td')
						tdTitle.textContent = item.title || '—'
						const tdPriority = document.createElement('td')
						tdPriority.textContent = (item.priority || '—').toUpperCase()
						const tdStarts = document.createElement('td')
						tdStarts.textContent = formatDateTime(item.starts_at)
						const tdEnds = document.createElement('td')
						tdEnds.textContent = formatDateTime(item.ends_at)
						const tdActive = document.createElement('td')
						tdActive.textContent = item.is_active ? 'Yes' : 'No'
						tr.append(tdTitle, tdPriority, tdStarts, tdEnds, tdActive)
						radioEmergencyHistoryBody.appendChild(tr)
					})
				} catch (e) {
					if (radioEmergencyHistoryLoading) radioEmergencyHistoryLoading.hidden = true
					console.warn('[farm_intelligence_platform] emergency history load failed', e)
				}
			}

			const openEmergencyHistoryModal = () => {
				if (!radioEmergencyHistoryModal) return
				radioEmergencyHistoryModal.hidden = false
				loadEmergencyHistory()
			}

			const closeEmergencyHistoryModal = () => {
				if (radioEmergencyHistoryModal) radioEmergencyHistoryModal.hidden = true
			}

			let editingEmergencyPk = null

			const loadEmergencyBroadcasts = async () => {
				if (!radioEmergencyMgmtBody || !radioEmergencyMgmtEmpty) return
				try {
					const resp = await performJsonRequest('GET', radioEmergencyHistoryUrl)
					const list = resp?.data ?? []
					if (list.length === 0) {
						radioEmergencyMgmtBody.innerHTML = ''
						radioEmergencyMgmtBody.closest('.farm-intelligence-platform-radio__table-wrap')?.querySelector('table')?.setAttribute('hidden', '')
						radioEmergencyMgmtEmpty.hidden = false
						return
					}
					radioEmergencyMgmtEmpty.hidden = true
					radioEmergencyMgmtBody.closest('.farm-intelligence-platform-radio__table-wrap')?.querySelector('table')?.removeAttribute('hidden')
					radioEmergencyMgmtBody.innerHTML = list.map(item => {
						const pk = item.id ?? item.pk ?? ''
						const starts = item.starts_at || item.start_time || item.starts || ''
						const ends = item.ends_at || item.end_time || item.ends || ''
						return `<tr>
							<td>${OC.Util.escapeHTML(String(pk))}</td>
							<td>${OC.Util.escapeHTML(item.title || '')}</td>
							<td>${OC.Util.escapeHTML(item.priority || '')}</td>
							<td>${item.is_active || item.active ? 'Yes' : 'No'}</td>
							<td>${OC.Util.escapeHTML(starts)}</td>
							<td>${OC.Util.escapeHTML(ends)}</td>
							<td>
								<button type="button" class="button" data-action="edit" data-pk="${OC.Util.escapeHTML(String(pk))}">Edit</button>
								<button type="button" class="button" data-action="delete" data-pk="${OC.Util.escapeHTML(String(pk))}">Delete</button>
							</td>
						</tr>`
					}).join('')
					radioEmergencyMgmtBody.querySelectorAll('[data-action="edit"]').forEach(btn => {
						btn.addEventListener('click', () => editEmergency(btn.dataset.pk))
					})
					radioEmergencyMgmtBody.querySelectorAll('[data-action="delete"]').forEach(btn => {
						btn.addEventListener('click', () => deleteEmergency(btn.dataset.pk))
					})
				} catch (e) {
					console.warn('[farm_intelligence_platform] Failed to load emergency broadcasts', e)
				}
			}

			const openEmergencyModal = (title = '') => {
				if (!radioEmergencyModal) return
				editingEmergencyPk = null
				radioEmergencyModalTitleInput.value = ''
				radioEmergencyModalMessageInput.value = ''
				radioEmergencyModalPrioritySelect.value = 'high'
				radioEmergencyModalStartsInput.value = ''
				radioEmergencyModalEndsInput.value = ''
				radioEmergencyModalError.hidden = true
				radioEmergencyModalError.textContent = ''
				radioEmergencyModalTitle.textContent = title || t('farm_intelligence_platform', 'Create Emergency Broadcast')
				radioEmergencyModalSave.textContent = t('farm_intelligence_platform', 'Create')
				radioEmergencyModal.hidden = false
			}

			const closeEmergencyModal = () => {
				if (radioEmergencyModal) radioEmergencyModal.hidden = true
				editingEmergencyPk = null
			}

			const editEmergency = async (pk) => {
				if (!pk) return
				try {
					const resp = await performJsonRequest('GET', radioEmergencyHistoryUrl)
					const list = resp?.data ?? []
					const item = list.find(i => String(i.id ?? i.pk ?? '') === String(pk))
					if (!item) {
						OC.Notification.showTemporary(t('farm_intelligence_platform', 'Emergency broadcast not found'))
						return
					}
					editingEmergencyPk = pk
					radioEmergencyModalTitleInput.value = item.title || ''
					radioEmergencyModalMessageInput.value = item.message || item.description || ''
					radioEmergencyModalPrioritySelect.value = item.priority || 'high'
					if (item.starts_at || item.start_time || item.starts) {
						const d = new Date(item.starts_at || item.start_time || item.starts)
						if (!isNaN(d.getTime())) radioEmergencyModalStartsInput.value = d.toISOString().slice(0, 16)
					}
					if (item.ends_at || item.end_time || item.ends) {
						const d = new Date(item.ends_at || item.end_time || item.ends)
						if (!isNaN(d.getTime())) radioEmergencyModalEndsInput.value = d.toISOString().slice(0, 16)
					}
					radioEmergencyModalError.hidden = true
					radioEmergencyModalError.textContent = ''
					radioEmergencyModalTitle.textContent = t('farm_intelligence_platform', 'Edit Emergency Broadcast')
					radioEmergencyModalSave.textContent = t('farm_intelligence_platform', 'Update')
					radioEmergencyModal.hidden = false
				} catch (e) {
					console.warn('[farm_intelligence_platform] Failed to load emergency for editing', e)
				}
			}

			const saveEmergency = async () => {
				if (!radioEmergencyModalTitleInput || !radioEmergencyModalSave) return
				const title = radioEmergencyModalTitleInput.value.trim()
				if (!title) {
					radioEmergencyModalError.hidden = false
					radioEmergencyModalError.textContent = t('farm_intelligence_platform', 'Title is required')
					return
				}
				radioEmergencyModalSave.disabled = true
				radioEmergencyModalError.hidden = true
				radioEmergencyModalError.textContent = ''
				const body = {
					title,
					message: radioEmergencyModalMessageInput?.value?.trim() || '',
					priority: radioEmergencyModalPrioritySelect?.value || 'high',
					starts_at: radioEmergencyModalStartsInput?.value || null,
					ends_at: radioEmergencyModalEndsInput?.value || null,
				}
				try {
					if (editingEmergencyPk) {
						const url = radioEmergencyUpdateUrl.replace('__PK__', editingEmergencyPk)
						await performJsonRequest('PATCH', url, { body })
					} else {
						await performJsonRequest('POST', radioEmergencyCreateUrl, { body })
					}
					closeEmergencyModal()
					await loadEmergencyBroadcasts()
					OC.Notification.showTemporary(t('farm_intelligence_platform', editingEmergencyPk ? 'Emergency broadcast updated' : 'Emergency broadcast created'))
				} catch (e) {
					const msg = e?.response?.data?.error?.message || e?.message || t('farm_intelligence_platform', 'Failed to save emergency broadcast')
					radioEmergencyModalError.hidden = false
					radioEmergencyModalError.textContent = msg
				} finally {
					radioEmergencyModalSave.disabled = false
				}
			}

			const deleteEmergency = async (pk) => {
				if (!pk) return
				if (!confirm(t('farm_intelligence_platform', 'Are you sure you want to delete this emergency broadcast?'))) return
				try {
					const url = radioEmergencyDeleteUrl.replace('__PK__', pk)
					await performJsonRequest('DELETE', url)
					await loadEmergencyBroadcasts()
					OC.Notification.showTemporary(t('farm_intelligence_platform', 'Emergency broadcast deleted'))
				} catch (e) {
					const msg = e?.response?.data?.error?.message || e?.message || t('farm_intelligence_platform', 'Failed to delete emergency broadcast')
					OC.Notification.showTemporary(msg)
				}
			}

			const synthesizeTts = async () => {
				if (!radioTtsText || !radioTtsSynthesizeBtn) return
				const text = radioTtsText.value.trim()
				if (!text) {
					radioTtsError.hidden = false
					radioTtsError.textContent = t('farm_intelligence_platform', 'Please enter text to synthesize')
					return
				}
				radioTtsSynthesizeBtn.disabled = true
				radioTtsError.hidden = true
				radioTtsError.textContent = ''
				if (radioTtsAudio) radioTtsAudio.hidden = true
				if (radioTtsDownloadBtn) radioTtsDownloadBtn.hidden = true
				if (radioTtsDuration) radioTtsDuration.hidden = true
				try {
					const voice = radioTtsVoice?.value || 'en-US'
					const resp = await performJsonRequest('POST', radioTtsUrl, { body: { text, voice } })
					const phpEnvelope = resp?.data || {}
					const djangoEnvelope = phpEnvelope?.data || {}
					const djangoData = djangoEnvelope?.data || {}
					const audioBase64 = djangoData.audio_base64 || ''
					const durationMs = djangoData.duration_ms || 0
					const mimeType = djangoData.mime_type || 'audio/wav'
					if (!audioBase64) {
						const errMsg = djangoEnvelope?.message || djangoEnvelope?.errors?.detail || phpEnvelope?.message || ''
						const debugInfo = 'phpKeys:' + Object.keys(phpEnvelope).join(',') + '|djKeys:' + Object.keys(djangoEnvelope).join(',') + '|dataKeys:' + Object.keys(djangoData).join(',') + '|djStatus:' + djangoEnvelope?.status
						radioTtsError.hidden = false
						radioTtsError.textContent = errMsg || ('No audio – ' + debugInfo)
						return
					}
					const byteChars = atob(audioBase64)
					const byteNumbers = new Array(byteChars.length)
					for (let i = 0; i < byteChars.length; i++) {
						byteNumbers[i] = byteChars.charCodeAt(i)
					}
					const blob = new Blob([new Uint8Array(byteNumbers)], { type: mimeType })
					const audioUrl = URL.createObjectURL(blob)
					if (radioTtsAudio) {
						radioTtsAudio.src = audioUrl
						radioTtsAudio.hidden = false
						radioTtsAudio.play().catch(() => {})
					}
					if (radioTtsDuration && durationMs) {
						const seconds = Math.round(durationMs / 1000)
						radioTtsDuration.textContent = t('farm_intelligence_platform', 'Duration: {seconds}s', { seconds })
						radioTtsDuration.hidden = false
					}
					if (radioTtsDownloadBtn) {
						radioTtsDownloadBtn.href = audioUrl
						radioTtsDownloadBtn.hidden = false
					}
				} catch (e) {
					const msg = e?.response?.data?.error?.message || e?.message || t('farm_intelligence_platform', 'Failed to synthesize speech')
					radioTtsError.hidden = false
					radioTtsError.textContent = msg
				} finally {
					radioTtsSynthesizeBtn.disabled = false
				}
			}

			const switchStationTab = (tab) => {
				const tabs = [
					{ btn: stationTabNowPlaying, panel: stationPanelNowPlaying, key: 'now-playing' },
					{ btn: stationTabAnalytics, panel: stationPanelAnalytics, key: 'analytics' },
					{ btn: stationTabHealth, panel: stationPanelHealth, key: 'health' },
				]
				tabs.forEach(t => {
					if (!t.btn || !t.panel) return
					const active = t.key === tab
					t.btn.classList.toggle('primary', active)
					t.panel.hidden = !active
				})
			}

			const openStationModal = (station) => {
				if (!stationModal || !station) return
				stationModal.dataset.stationId = station.id
				if (stationModalName) stationModalName.textContent = station.name || station.id
				if (stationModalProvider) stationModalProvider.textContent = station.provider_name ? `· ${station.provider_name}` : ''
				if (stationModalGenre) stationModalGenre.textContent = station.genre ? `· ${station.genre}` : ''
				if (stationModalCountry) stationModalCountry.textContent = station.country ? `· ${station.country}` : ''
				if (stationModalDescription) {
					if (station.description) {
						stationModalDescription.textContent = station.description
						stationModalDescription.hidden = false
					} else {
						stationModalDescription.textContent = ''
						stationModalDescription.hidden = true
					}
				}
				const logoUrl = station.logo_url || station.provider_logo_url || ''
				if (stationModalLogo) {
					if (logoUrl) {
						stationModalLogo.src = logoUrl
						stationModalLogo.hidden = false
					} else {
						stationModalLogo.hidden = true
					}
				}
				stationModal.hidden = false
				switchStationTab('now-playing')
				loadStationNowPlaying(station.id)
			}

			const closeStationModal = () => {
				if (stationModal) stationModal.hidden = true
			}

			const loadStationNowPlaying = async (stationId) => {
				if (!radioNowPlayingUrl) return
				setStationPanelError(null)
				setStationPanelLoading(true)
				try {
					const url = radioNowPlayingUrl.replace('__STATION_ID__', encodeURIComponent(stationId))
					const result = await performJsonRequest('GET', url)
					setStationPanelLoading(false)
					if (!result.parsed || !isOcsSuccess(result.data)) {
						setStationPanelError('Unable to load now-playing.')
						return
					}
					const payload = unwrapResponseData(result.data)
					const data = payload?.data ?? payload
					if (!data) {
						if (stationNowPlayingEmpty) stationNowPlayingEmpty.hidden = false
						if (stationNowPlayingTrack) stationNowPlayingTrack.textContent = '—'
						if (stationNowPlayingArtist) stationNowPlayingArtist.textContent = '—'
						if (stationNowPlayingAlbum) stationNowPlayingAlbum.textContent = '—'
						if (stationNowPlayingUpdated) stationNowPlayingUpdated.textContent = ''
						return
					}
					const track = data.track_title || data.title || ''
					const artist = data.artist || ''
					const album = data.album || ''
					if (stationNowPlayingEmpty) stationNowPlayingEmpty.hidden = !!(track || artist || album)
					if (stationNowPlayingTrack) stationNowPlayingTrack.textContent = track || '—'
					if (stationNowPlayingArtist) stationNowPlayingArtist.textContent = artist || '—'
					if (stationNowPlayingAlbum) stationNowPlayingAlbum.textContent = album || '—'
					if (stationNowPlayingUpdated) stationNowPlayingUpdated.textContent = data.updated_at ? `Updated ${formatDateTime(data.updated_at)}` : ''
					if (stationNowPlayingArt && data.artwork_url) {
						const img = document.createElement('img')
						img.src = data.artwork_url
						img.alt = ''
						img.className = 'farm-intelligence-platform-radio__now-playing-img'
						stationNowPlayingArt.innerHTML = ''
						stationNowPlayingArt.appendChild(img)
					}
				} catch (e) {
					setStationPanelLoading(false)
					setStationPanelError('Failed to load now-playing.')
				}
			}

			const loadStationAnalytics = async (stationId, days) => {
				if (!radioAnalyticsUrl) return
				setStationPanelError(null)
				setStationPanelLoading(true)
				try {
					const url = radioAnalyticsUrl.replace('__STATION_ID__', encodeURIComponent(stationId))
					const result = await performJsonRequest('GET', `${url}?days=${encodeURIComponent(days)}`)
					setStationPanelLoading(false)
					if (!result.parsed || !isOcsSuccess(result.data)) {
						setStationPanelError('Unable to load analytics.')
						return
					}
					const payload = unwrapResponseData(result.data)
					const data = payload?.data ?? payload
					const rows = Array.isArray(data) ? data : (Array.isArray(data?.results) ? data.results : [])
					let totalListens = 0
					let totalDuration = 0
					let uniqueUsers = 0
					rows.forEach(r => {
						totalListens += Number(r.total_listens || 0)
						totalDuration += Number(r.total_duration_seconds || 0)
						uniqueUsers += Number(r.unique_users || 0)
					})
					if (stationAnalyticsTotalListens) stationAnalyticsTotalListens.textContent = totalListens.toLocaleString()
					if (stationAnalyticsTotalDuration) stationAnalyticsTotalDuration.textContent = formatDuration(totalDuration)
					if (stationAnalyticsUniqueUsers) stationAnalyticsUniqueUsers.textContent = uniqueUsers.toLocaleString()
					if (stationAnalyticsBody) stationAnalyticsBody.innerHTML = ''
					if (rows.length === 0) {
						if (stationAnalyticsEmpty) stationAnalyticsEmpty.hidden = false
						return
					}
					if (stationAnalyticsEmpty) stationAnalyticsEmpty.hidden = true
					rows.forEach(r => {
						const tr = document.createElement('tr')
						const tdDate = document.createElement('td')
						tdDate.textContent = r.date || '—'
						const tdListens = document.createElement('td')
						tdListens.textContent = (r.total_listens ?? 0).toLocaleString()
						const tdDuration = document.createElement('td')
						tdDuration.textContent = (r.total_duration_seconds ?? 0).toLocaleString()
						const tdUsers = document.createElement('td')
						tdUsers.textContent = (r.unique_users ?? 0).toLocaleString()
						tr.append(tdDate, tdListens, tdDuration, tdUsers)
						stationAnalyticsBody.appendChild(tr)
					})
				} catch (e) {
					setStationPanelLoading(false)
					setStationPanelError('Failed to load analytics.')
				}
			}

			const loadStationHealth = async (stationId) => {
				if (!radioStationHealthUrl) return
				setStationPanelError(null)
				setStationPanelLoading(true)
				try {
					const url = radioStationHealthUrl.replace('__STATION_ID__', encodeURIComponent(stationId))
					const result = await performJsonRequest('GET', url)
					setStationPanelLoading(false)
					if (!result.parsed || !isOcsSuccess(result.data)) {
						setStationPanelError('Unable to load station health.')
						return
					}
					const payload = unwrapResponseData(result.data)
					const list = Array.isArray(payload) ? payload : (Array.isArray(payload?.data) ? payload.data : [])
					const entry = list[0] || {}
					const reachable = entry?.reachable ?? entry?.is_reachable
					const lastProbe = entry?.checked_at ?? entry?.last_probe_at ?? entry?.last_checked_at
					const latency = entry?.response_time_ms ?? entry?.latency_ms
					const http = entry?.status_code ?? entry?.http_status
					if (stationHealthStatus) {
						const ok = reachable === true
						stationHealthStatus.textContent = ok ? 'Reachable' : 'Unreachable'
						stationHealthStatus.classList.toggle('ok', ok)
						stationHealthStatus.classList.toggle('error', !ok)
					}
					if (stationHealthLastProbe) stationHealthLastProbe.textContent = formatDateTime(lastProbe)
					if (stationHealthLatency) stationHealthLatency.textContent = latency != null ? `${Number(latency).toFixed(0)} ms` : '—'
					if (stationHealthHttp) stationHealthHttp.textContent = http != null ? String(http) : '—'
				} catch (e) {
					setStationPanelLoading(false)
					setStationPanelError('Failed to load station health.')
				}
			}

			const loadStationHealthHistory = async (stationId) => {
				if (!radioStationHealthHistoryUrl || !stationHealthHistoryBody) return
				try {
					const url = radioStationHealthHistoryUrl.replace('__STATION_ID__', encodeURIComponent(stationId))
					const result = await performJsonRequest('GET', `${url}?limit=20`)
					if (!result.parsed || !isOcsSuccess(result.data)) { return }
					const payload = unwrapResponseData(result.data)
					const rows = Array.isArray(payload) ? payload : (Array.isArray(payload?.data) ? payload.data : [])
					stationHealthHistoryBody.innerHTML = ''
					if (rows.length === 0) {
						if (stationHealthHistoryEmpty) stationHealthHistoryEmpty.hidden = false
						return
					}
					if (stationHealthHistoryEmpty) stationHealthHistoryEmpty.hidden = true
					rows.forEach(r => {
						const tr = document.createElement('tr')
						const tdChecked = document.createElement('td')
						tdChecked.textContent = formatDateTime(r.checked_at)
						const tdReachable = document.createElement('td')
						tdReachable.textContent = r.is_reachable ? 'Yes' : 'No'
						const tdHttp = document.createElement('td')
						tdHttp.textContent = r.http_status != null ? String(r.http_status) : '—'
						const tdLatency = document.createElement('td')
						tdLatency.textContent = r.latency_ms != null ? `${Number(r.latency_ms).toFixed(0)} ms` : '—'
						const tdError = document.createElement('td')
						tdError.textContent = r.error_message || '—'
						tr.append(tdChecked, tdReachable, tdHttp, tdLatency, tdError)
						stationHealthHistoryBody.appendChild(tr)
					})
				} catch (e) {
					console.warn('[farm_intelligence_platform] station health history load failed', e)
				}
			}

			let hlsInstance = null

			const playStation = async (station) => {
				if (!radioStreamUrl || !radioPlayerModal) return
				const url = radioStreamUrl.replace('__STATION_ID__', encodeURIComponent(station.id))
				try {
					const result = await performJsonRequest('GET', url)
					console.info('[farm_intelligence_platform] radio stream response', result.data)
					if (!result.parsed || !isOcsSuccess(result.data)) { console.error('[farm_intelligence_platform] stream not ok', result.data); showRadioError('Unable to get stream URL.'); return }
					const data = unwrapResponseData(result.data)
					console.info('[farm_intelligence_platform] unwrapped stream data', data)
					let streamUrl = data?.stream_url || data?.data?.stream_url || data?.url
					if (!streamUrl) { console.error('[farm_intelligence_platform] no stream URL in', data); showRadioError('No stream URL available.'); return }
					if (streamUrl.startsWith('http://') && window.location.protocol === 'https:') {
						streamUrl = streamUrl.replace('http://', 'https://')
					}
					console.info('[farm_intelligence_platform] playing stream', streamUrl)
					const logoUrl = station.logo_url || station.provider_logo_url || ''
					const updateBarLogo = () => {
						if (radioBarLogo) {
							if (logoUrl) {
								radioBarLogo.src = logoUrl
								radioBarLogo.hidden = false
							} else {
								radioBarLogo.hidden = true
							}
						}
					}
					if (radioPlayerTitle) radioPlayerTitle.textContent = station.name
					if (radioBarTitle) radioBarTitle.textContent = station.name
					if (radioPlayerSubtitle) radioPlayerSubtitle.textContent = `${station.genre || 'Unknown genre'} · ${station.country || 'Unknown'}`
					if (radioPlayerLogo) {
						if (logoUrl) {
							radioPlayerLogo.src = logoUrl
							radioPlayerLogo.hidden = false
							radioPlayerIcon.hidden = true
						} else {
							radioPlayerLogo.hidden = true
							radioPlayerIcon.hidden = false
						}
					}
					updateBarLogo()
					if (radioAudio) {
						if (hlsInstance) { hlsInstance.destroy(); hlsInstance = null }
						radioAudio.src = ''
						const isHls = streamUrl.includes('.m3u8')
						if (isHls && Hls && Hls.isSupported()) {
							console.info('[farm_intelligence_platform] using HLS.js for stream')
							hlsInstance = new Hls()
							hlsInstance.loadSource(streamUrl)
							hlsInstance.attachMedia(radioAudio)
							hlsInstance.on(Hls.Events.MANIFEST_PARSED, () => {
								radioAudio.play().catch(err => { console.error('[farm_intelligence_platform] audio play failed', err) })
								updatePlayPauseIcon(true)
							})
							hlsInstance.on(Hls.Events.ERROR, (event, data) => {
								console.error('[farm_intelligence_platform] HLS.js error', data.type, data.details, data.fatal, data)
								if (!data.fatal) {
									if (data.type === Hls.ErrorTypes.NETWORK_ERROR) {
										hlsInstance.startLoad()
									} else if (data.type === Hls.ErrorTypes.MEDIA_ERROR) {
										hlsInstance.recoverMediaError()
									}
									return
								}
								const msg = data.details === 'manifestLoadError' ? 'Stream manifest blocked (CORS/geo-restriction).' : 'Stream playback failed.'
								showRadioError(msg)
							})
						} else if (isHls && radioAudio.canPlayType('application/vnd.apple.mpegurl')) {
							console.info('[farm_intelligence_platform] using native HLS (Safari)')
							radioAudio.src = streamUrl
							radioAudio.play().catch(err => { console.error('[farm_intelligence_platform] audio play failed', err) })
							updatePlayPauseIcon(true)
						} else {
							console.info('[farm_intelligence_platform] using direct audio playback')
							radioAudio.src = streamUrl
							radioAudio.play().catch(err => { console.error('[farm_intelligence_platform] audio play failed', err) })
							updatePlayPauseIcon(true)
						}
						updateProgressBars()
					}
					if (radioPlayer) {
						radioPlayer.hidden = false
						if (radioPlayer.parentElement !== document.body) {
							document.body.appendChild(radioPlayer)
						}
						console.info('[farm_intelligence_platform] player shown, parent:', radioPlayer.parentElement?.tagName)
					}
				} catch (e) {
					console.error('[farm_intelligence_platform] stream load error', e)
					showRadioError('Failed to load stream URL.')
				}
			}

			let radioElapsedSeconds = 0
			let radioElapsedTimer = null

			const updatePlayPauseIcon = (isPlaying) => {
				if (radioIconPlay) radioIconPlay.hidden = isPlaying
				if (radioIconPause) radioIconPause.hidden = !isPlaying
				if (radioBarIconPlay) radioBarIconPlay.hidden = isPlaying
				if (radioBarIconPause) radioBarIconPause.hidden = !isPlaying
			}

			const formatTime = (seconds) => {
				if (seconds < 0) return '0:00'
				const mins = Math.floor(seconds / 60)
				const secs = Math.floor(seconds % 60)
				return `${mins}:${String(secs).padStart(2, '0')}`
			}

			const startElapsedTimer = () => {
				stopElapsedTimer()
				radioElapsedSeconds = 0
				radioElapsedTimer = setInterval(() => {
					radioElapsedSeconds++
					const timeStr = formatTime(radioElapsedSeconds)
					if (radioBarTime) radioBarTime.textContent = timeStr
					if (radioPlayerTime) radioPlayerTime.textContent = timeStr
				}, 1000)
			}

			const stopElapsedTimer = () => {
				if (radioElapsedTimer) {
					clearInterval(radioElapsedTimer)
					radioElapsedTimer = null
				}
			}

			const togglePlayPause = () => {
				if (!radioAudio) return
				if (radioAudio.paused) {
					radioAudio.play().catch(() => {})
					updatePlayPauseIcon(true)
				} else {
					radioAudio.pause()
					updatePlayPauseIcon(false)
				}
			}

			const minimizePlayer = () => {
				if (radioPlayerModal) radioPlayerModal.hidden = true
			}

			const expandPlayer = () => {
				if (radioPlayerModal) radioPlayerModal.hidden = false
			}

			const closePlayer = () => {
				console.info('[farm_intelligence_platform] closing radio player')
				if (radioPlayer) radioPlayer.hidden = true
				if (radioPlayerModal) radioPlayerModal.hidden = true
				if (hlsInstance) { hlsInstance.destroy(); hlsInstance = null }
				if (radioAudio) { radioAudio.pause(); radioAudio.src = '' }
				stopElapsedTimer()
				updatePlayPauseIcon(false)
			}

			const switchTab = (tab) => {
				if (tab === 'stations') {
					if (radioStationsPanel) radioStationsPanel.hidden = false
					if (radioProvidersPanel) radioProvidersPanel.hidden = true
					if (radioStationsTab) { radioStationsTab.classList.add('primary') }
					if (radioProvidersTab) radioProvidersTab.classList.remove('primary')
				} else {
					if (radioStationsPanel) radioStationsPanel.hidden = true
					if (radioProvidersPanel) radioProvidersPanel.hidden = false
					if (radioStationsTab) radioStationsTab.classList.remove('primary')
					if (radioProvidersTab) radioProvidersTab.classList.add('primary')
				}
			}

			if (radioRefresh) radioRefresh.addEventListener('click', () => { loadStations(); loadProviders(); loadRadioHealth(); loadCurrentEmergency() })
			if (radioStationsTab) radioStationsTab.addEventListener('click', () => switchTab('stations'))
			if (radioProvidersTab) radioProvidersTab.addEventListener('click', () => switchTab('providers'))
			if (radioSearch) radioSearch.addEventListener('input', renderStations)
			if (radioGenreFilter) radioGenreFilter.addEventListener('change', renderStations)
			if (radioCountryFilter) radioCountryFilter.addEventListener('change', renderStations)
			if (radioEmergencyHistoryBtn) radioEmergencyHistoryBtn.addEventListener('click', openEmergencyHistoryModal)
			if (radioEmergencyHistoryModalClose) radioEmergencyHistoryModalClose.addEventListener('click', closeEmergencyHistoryModal)
			if (radioEmergencyHistoryModal) {
				radioEmergencyHistoryModal.addEventListener('click', (event) => {
					if (event.target === radioEmergencyHistoryModal) closeEmergencyHistoryModal()
				})
			}
			if (radioEmergencyCreateBtn) radioEmergencyCreateBtn.addEventListener('click', () => openEmergencyModal())
			if (radioEmergencyMgmtRefresh) radioEmergencyMgmtRefresh.addEventListener('click', loadEmergencyBroadcasts)
			if (radioEmergencyModalClose) radioEmergencyModalClose.addEventListener('click', closeEmergencyModal)
			if (radioEmergencyModal) {
				radioEmergencyModal.addEventListener('click', (event) => {
					if (event.target === radioEmergencyModal) closeEmergencyModal()
				})
			}
			if (radioEmergencyModalSave) radioEmergencyModalSave.addEventListener('click', saveEmergency)
			if (radioEmergencyModalCancel) radioEmergencyModalCancel.addEventListener('click', closeEmergencyModal)
			if (radioTtsSynthesizeBtn) radioTtsSynthesizeBtn.addEventListener('click', synthesizeTts)
			if (stationModalClose) stationModalClose.addEventListener('click', closeStationModal)
			if (stationModal) {
				stationModal.addEventListener('click', (event) => {
					if (event.target === stationModal) closeStationModal()
				})
			}
			if (stationTabNowPlaying) {
				stationTabNowPlaying.addEventListener('click', () => {
					switchStationTab('now-playing')
					const id = stationModal?.dataset.stationId
					if (id) loadStationNowPlaying(id)
				})
			}
			if (stationTabAnalytics) {
				stationTabAnalytics.addEventListener('click', () => {
					switchStationTab('analytics')
					const id = stationModal?.dataset.stationId
					const days = stationAnalyticsDays?.value || '30'
					if (id) loadStationAnalytics(id, days)
				})
			}
			if (stationTabHealth) {
				stationTabHealth.addEventListener('click', () => {
					switchStationTab('health')
					const id = stationModal?.dataset.stationId
					if (id) { loadStationHealth(id); loadStationHealthHistory(id) }
				})
			}
			if (stationAnalyticsRefresh) {
				stationAnalyticsRefresh.addEventListener('click', () => {
					const id = stationModal?.dataset.stationId
					const days = stationAnalyticsDays?.value || '30'
					if (id) loadStationAnalytics(id, days)
				})
			}
			if (stationAnalyticsDays) {
				stationAnalyticsDays.addEventListener('change', () => {
					const id = stationModal?.dataset.stationId
					const days = stationAnalyticsDays.value || '30'
					if (id) loadStationAnalytics(id, days)
				})
			}
			if (radioPlayerClose) radioPlayerClose.addEventListener('click', closePlayer)
			if (radioPlayerMinimize) radioPlayerMinimize.addEventListener('click', minimizePlayer)
			if (radioPlayerPlay) radioPlayerPlay.addEventListener('click', togglePlayPause)
			if (radioBarPlay) radioBarPlay.addEventListener('click', togglePlayPause)
			if (radioBarExpand) radioBarExpand.addEventListener('click', expandPlayer)
			if (radioBarClose) radioBarClose.addEventListener('click', closePlayer)
			if (radioVolume) radioVolume.addEventListener('input', () => { if (radioAudio) radioAudio.volume = radioVolume.value / 100 })
			const seekRadioBySeconds = (deltaSeconds) => {
				if (!radioAudio) return
				const current = Number.isFinite(radioAudio.currentTime) ? radioAudio.currentTime : 0
				const target = Math.max(0, current + deltaSeconds)
				const seekable = radioAudio.seekable
				if (seekable && seekable.length > 0) {
					const start = seekable.start(0)
					const end = seekable.end(0)
					radioAudio.currentTime = Math.max(start, Math.min(end, target))
					updateProgressBars()
					return
				}
				if (Number.isFinite(radioAudio.duration) && radioAudio.duration > 0) {
					radioAudio.currentTime = Math.min(radioAudio.duration, target)
					updateProgressBars()
					return
				}
				showRadioError('This live stream does not expose a rewind buffer.')
			}

			if (radioProgressTrack) radioProgressTrack.addEventListener('click', (event) => seekRadioToPercent(radioProgressTrack, event.clientX))
			if (radioModalProgressTrack) radioModalProgressTrack.addEventListener('click', (event) => seekRadioToPercent(radioModalProgressTrack, event.clientX))
			if (radioPlayerRewind) radioPlayerRewind.addEventListener('click', () => seekRadioBySeconds(-10))
			if (radioPlayerForward) radioPlayerForward.addEventListener('click', () => seekRadioBySeconds(10))
			if (radioBarRewind) radioBarRewind.addEventListener('click', () => seekRadioBySeconds(-10))
			if (radioBarForward) radioBarForward.addEventListener('click', () => seekRadioBySeconds(10))
			if (radioAudio) {
				radioAudio.volume = 0.8
				radioAudio.addEventListener('play', () => { updatePlayPauseIcon(true); startElapsedTimer(); startProgressAnimation() })
				radioAudio.addEventListener('pause', () => { updatePlayPauseIcon(false); stopElapsedTimer(); stopProgressAnimation() })
				radioAudio.addEventListener('timeupdate', updateProgressBars)
				radioAudio.addEventListener('durationchange', updateProgressBars)
				radioAudio.addEventListener('progress', updateProgressBars)
				radioAudio.addEventListener('loadedmetadata', updateProgressBars)
				radioAudio.addEventListener('seeking', updateProgressBars)
				radioAudio.addEventListener('seeked', updateProgressBars)
			}
			if (radioPlayerModal) {
				radioPlayerModal.addEventListener('click', (e) => {
					if (e.target === radioPlayerModal) minimizePlayer()
				})
			}

			loadStations()
			loadProviders()
			loadRadioHealth()
			loadCurrentEmergency()
			loadEmergencyBroadcasts()
		}

		const setupFarms = () => {
			if (!farmsRoot) {
				return
			}

			const ncGenerateUrl = resolveRequestUrl

			const fieldOrder = [
				'name',
				'centroid_lat',
				'centroid_lon',
				'bbox_south',
				'bbox_west',
				'bbox_north',
				'bbox_east',
				'area_ha',
				'is_active',
				'created_at',
			]
			const DEFAULT_WEATHER_HOURS = 48
			const DEFAULT_WEATHER_DAYS = 7

			let farmSchema = null
			let farmFields = {}
			let farmFieldsCreate = {}
			let farmFieldsUpdate = {}
			let farmColumns = []
			let farmOperations = {}
			let activeColumns = []
			let nextParams = null
			let prevParams = null
			let selectedFarm = null
			let observationsLimit = 50
			let observationsOffset = 0
			let currentObservationId = null
			let modalInitial = {}
			let ndviTouched = { start: false, end: false, raster: false }
			let ndviRasterObjectUrl = null
			let rasterMap = null
			let latestNdviState = null
			let timeseriesNdviState = null
			let latestNdwiState = null
			let timeseriesNdwiState = null
			let latestNdmiState = null
			let timeseriesNdmiState = null
			let latestRviState = null
			let timeseriesRviState = null
			let latestS1SmiState = null
			let timeseriesS1SmiState = null
			let latestS3LstState = null
			let timeseriesS3LstState = null
			let latestLandsatLstState = null
			let timeseriesLandsatLstState = null
			let latestIronOxideState = null
			let timeseriesIronOxideState = null
			let latestEviState = null
			let timeseriesEviState = null
			let latestLRviState = null
			let timeseriesLRviState = null
			let latestNisarSmiState = null
			let timeseriesNisarSmiState = null
			let latestNdreState = null
			let timeseriesNdreState = null
			let latestBiomassState = null
			let timeseriesBiomassState = null
			let weatherCache = { current: null, hourly: null, daily: null }
			let schemaReady = false
			let schemaLoadPromise = null
			let currentSyncFarmId = null
			let currentSyncFarmData = null
			let reduceLatestState = () => ({ status: 'no_data', vm: null, payload: null, message: '' })
			let reduceTimeseriesState = () => ({
				status: 'no_data',
				vm: {
					rangeStart: null,
					rangeEnd: null,
					receivedCount: 0,
					shownCount: 0,
					points: [],
					filterWarning: false,
					raw: null,
				},
				payload: null,
				message: '',
			})

			const clearFarmsNotes = () => {
				if (farmsWarning) {
					farmsWarning.textContent = ''
					farmsWarning.hidden = true
				}
				if (farmsError) {
					farmsError.textContent = ''
					farmsError.hidden = true
				}
			}

			const showFarmsWarning = (message) => {
				if (!farmsWarning) {
					return
				}
				farmsWarning.textContent = message
				farmsWarning.hidden = false
			}

			const showFarmsError = (message) => {
				if (!farmsError) {
					return
				}
				farmsError.textContent = message
				farmsError.hidden = false
				toast(message)
			}

			const logFarms = (message, context = {}) => {
				if (typeof console === 'undefined' || typeof console.info !== 'function') {
					return
				}
				console.info('[farm_intelligence_platform] farms', message, context)
			}

			const setFarmsCreateEnabled = (enabled) => {
				if (farmsCreate) {
					farmsCreate.disabled = !enabled
				}
			}

			const setFarmsActionsEnabled = (enabled) => {
				if (farmsRefresh) {
					farmsRefresh.disabled = !enabled
				}
				setFarmsCreateEnabled(enabled)
				if (!enabled) {
					if (farmsPrev) farmsPrev.disabled = true
					if (farmsNext) farmsNext.disabled = true
				}
				if (ndviLatestButton) ndviLatestButton.disabled = !enabled
				if (ndviRefreshButton) ndviRefreshButton.disabled = !enabled
				if (ndwiLatestButton) ndwiLatestButton.disabled = !enabled
				if (ndwiRefreshButton) ndwiRefreshButton.disabled = !enabled
				if (ndmiLatestButton) ndmiLatestButton.disabled = !enabled
				if (ndmiRefreshButton) ndmiRefreshButton.disabled = !enabled
				if (rviLatestButton) rviLatestButton.disabled = !enabled
				if (rviRefreshButton) rviRefreshButton.disabled = !enabled
				if (s1SmiLatestButton) s1SmiLatestButton.disabled = !enabled
				if (s1SmiRefreshButton) s1SmiRefreshButton.disabled = !enabled
				if (s3LstLatestButton) s3LstLatestButton.disabled = !enabled
				if (s3LstRefreshButton) s3LstRefreshButton.disabled = !enabled
				if (landsatLstLatestButton) landsatLstLatestButton.disabled = !enabled
				if (landsatLstRefreshButton) landsatLstRefreshButton.disabled = !enabled
				if (ironOxideLatestButton) ironOxideLatestButton.disabled = !enabled
				if (ironOxideRefreshButton) ironOxideRefreshButton.disabled = !enabled
				if (eviLatestButton) eviLatestButton.disabled = !enabled
				if (eviRefreshButton) eviRefreshButton.disabled = !enabled
				if (lRviLatestButton) lRviLatestButton.disabled = !enabled
				if (lRviRefreshButton) lRviRefreshButton.disabled = !enabled
				if (nisarSmiLatestButton) nisarSmiLatestButton.disabled = !enabled
				if (nisarSmiRefreshButton) nisarSmiRefreshButton.disabled = !enabled
				if (ndreLatestButton) ndreLatestButton.disabled = !enabled
				if (ndreRefreshButton) ndreRefreshButton.disabled = !enabled
				if (ndreStateButton) ndreStateButton.disabled = !enabled
				if (biomassLatestButton) biomassLatestButton.disabled = !enabled
				if (biomassRefreshButton) biomassRefreshButton.disabled = !enabled
				if (biomassStateButton) biomassStateButton.disabled = !enabled
				if (weatherCurrentTab) weatherCurrentTab.disabled = !enabled
				if (weatherHourlyTab) weatherHourlyTab.disabled = !enabled
				if (weatherDailyTab) weatherDailyTab.disabled = !enabled
				if (!enabled) {
					if (ndviTimeseriesButton) ndviTimeseriesButton.disabled = true
					if (ndviQueueButton) ndviQueueButton.disabled = true
					if (ndviRasterButton) ndviRasterButton.disabled = true
					if (ndwiTimeseriesButton) ndwiTimeseriesButton.disabled = true
					if (ndwiQueueButton) ndwiQueueButton.disabled = true
					if (ndwiRasterButton) ndwiRasterButton.disabled = true
					if (ndmiTimeseriesButton) ndmiTimeseriesButton.disabled = true
					if (ndmiQueueButton) ndmiQueueButton.disabled = true
					if (ndmiRasterButton) ndmiRasterButton.disabled = true
					if (rviTimeseriesButton) rviTimeseriesButton.disabled = true
					if (rviQueueButton) rviQueueButton.disabled = true
					if (rviRasterButton) rviRasterButton.disabled = true
					if (s1SmiTimeseriesButton) s1SmiTimeseriesButton.disabled = true
					if (s1SmiQueueButton) s1SmiQueueButton.disabled = true
					if (s1SmiRasterButton) s1SmiRasterButton.disabled = true
					if (s3LstTimeseriesButton) s3LstTimeseriesButton.disabled = true
					if (s3LstQueueButton) s3LstQueueButton.disabled = true
					if (s3LstRasterButton) s3LstRasterButton.disabled = true
					if (landsatLstTimeseriesButton) landsatLstTimeseriesButton.disabled = true
					if (landsatLstQueueButton) landsatLstQueueButton.disabled = true
					if (landsatLstRasterButton) landsatLstRasterButton.disabled = true
					if (ironOxideTimeseriesButton) ironOxideTimeseriesButton.disabled = true
					if (ironOxideQueueButton) ironOxideQueueButton.disabled = true
					if (ironOxideRasterButton) ironOxideRasterButton.disabled = true
					if (lRviTimeseriesButton) lRviTimeseriesButton.disabled = true
					if (lRviQueueButton) lRviQueueButton.disabled = true
					if (lRviRasterButton) lRviRasterButton.disabled = true
					if (nisarSmiTimeseriesButton) nisarSmiTimeseriesButton.disabled = true
					if (nisarSmiQueueButton) nisarSmiQueueButton.disabled = true
					if (nisarSmiRasterButton) nisarSmiRasterButton.disabled = true
					if (ndreTimeseriesButton) ndreTimeseriesButton.disabled = true
					if (ndreQueueButton) ndreQueueButton.disabled = true
					if (ndreRasterButton) ndreRasterButton.disabled = true
					if (ndreStateButton) ndreStateButton.disabled = true
					if (biomassTimeseriesButton) biomassTimeseriesButton.disabled = true
					if (biomassQueueButton) biomassQueueButton.disabled = true
					if (biomassRasterButton) biomassRasterButton.disabled = true
					if (biomassStateButton) biomassStateButton.disabled = true
				}
			}

			const unwrapResponseData = (data) => {
				if (data && typeof data === 'object' && data.data !== undefined) {
					return data.data
				}
				return data ?? {}
			}

			const pickObject = (...candidates) => {
				for (const candidate of candidates) {
					if (candidate && typeof candidate === 'object' && !Array.isArray(candidate)) {
						if (Object.keys(candidate).length > 0) {
							return candidate
						}
					}
				}
				return {}
			}

			const pickArray = (...candidates) => {
				for (const candidate of candidates) {
					if (Array.isArray(candidate) && candidate.length > 0) {
						return candidate
					}
				}
				return []
			}

			const unwrapSchemaContainer = (value) => {
				let current = value
				for (let depth = 0; depth < 4; depth++) {
					if (current && typeof current === 'object' && current.schema && typeof current.schema === 'object') {
						current = current.schema
						continue
					}
					break
				}
				return current ?? {}
			}

			const normalizeFieldDefinition = (def = {}) => {
				const normalized = { ...def }
				if (normalized.type === 'string' && normalized.format === 'decimal') {
					normalized.type = 'number'
				}
				return normalized
			}

			const normalizeFields = (fields) => {
				if (!fields || typeof fields !== 'object') {
					return {}
				}
				const out = {}
				Object.entries(fields).forEach(([name, def]) => {
					if (!def || typeof def !== 'object') {
						return
					}
					out[name] = normalizeFieldDefinition(def)
				})
				return out
			}

			const filterWritableFields = (fields) => {
				const out = {}
				Object.entries(fields || {}).forEach(([name, def]) => {
					if (def?.readOnly) {
						return
					}
					out[name] = def
				})
				return out
			}

			const extractOpenApiFarmFields = (schema) => {
				const farm = schema?.components?.schemas?.Farm
				const properties = farm?.properties
				if (!properties || typeof properties !== 'object') {
					return {}
				}
				const required = Array.isArray(farm?.required) ? farm.required.map(String) : []
				const fields = {}
				Object.entries(properties).forEach(([name, prop]) => {
					if (!prop || typeof prop !== 'object') {
						return
					}
					const enumValues = Array.isArray(prop.enum) ? prop.enum : null
					fields[name] = normalizeFieldDefinition({
						type: prop.type || 'string',
						format: prop.format || null,
						required: required.includes(name),
						readOnly: Boolean(prop.readOnly),
						enum: enumValues,
					})
				})
				return fields
			}

			const getSchemaReady = async (source = 'unknown') => {
				if (schemaReady) {
					return true
				}
				if (!schemaLoadPromise) {
					logFarms('schema load started', { source })
					setFarmsActionsEnabled(false)
					schemaLoadPromise = (async () => {
						const schemaOk = await loadSchema()
						schemaReady = schemaOk
						logFarms(schemaOk ? 'schema load ok' : 'schema load failed', { source })
						setFarmsActionsEnabled(schemaOk)
						updateNdviActionState()
						return schemaOk
					})().catch((error) => {
						schemaReady = false
						logFarms('schema load failed', { source, error: toText(error) })
						setFarmsActionsEnabled(false)
						updateNdviActionState()
						return false
					})
				}

				const schemaOk = await schemaLoadPromise
				setFarmsActionsEnabled(schemaOk)
				updateNdviActionState()
				return schemaOk
			}

			const resolveOperation = (key) => (farmOperations && farmOperations[key]) || null

			const resolveFarmId = (farm) => {
				if (!farm || typeof farm !== 'object') {
					return null
				}
				if (farm.id !== undefined && farm.id !== null && farm.id !== '') {
					return farm.id
				}
				return null
			}

			const renderColumns = () => {
				if (!farmsColumns) {
					return
				}
				let derived = pickArray(farmColumns)
				if (derived.length === 0) {
					const fallback = Object.keys(farmFields || {})
					derived = fallback.length > 0
						? fallback
						: fieldOrder.filter((name) => Object.prototype.hasOwnProperty.call(farmFields, name))
				}
				activeColumns = derived
				farmsColumns.innerHTML = ''
				activeColumns.forEach((name) => {
					const th = document.createElement('th')
					th.textContent = name
					farmsColumns.appendChild(th)
				})
				const actions = document.createElement('th')
				actions.textContent = 'Actions'
				farmsColumns.appendChild(actions)
			}

			const formatValue = (value, field) => {
				if (value === null || value === undefined) {
					return '—'
				}
				if (field?.type === 'boolean') {
					return value ? 'true' : 'false'
				}
				return String(value)
			}

			const renderRows = (items) => {
				if (!farmsBody) {
					return
				}
				farmsBody.innerHTML = ''
				if (!Array.isArray(items) || items.length === 0) {
					const row = document.createElement('tr')
					const cell = document.createElement('td')
					cell.colSpan = activeColumns.length + 1
					cell.textContent = 'No farms found.'
					row.appendChild(cell)
					farmsBody.appendChild(row)
					return
				}

				items.forEach((farm) => {
					const row = document.createElement('tr')
					activeColumns.forEach((name) => {
						const cell = document.createElement('td')
						cell.textContent = formatValue(farm?.[name], farmFields?.[name])
						row.appendChild(cell)
					})

					const actions = document.createElement('td')
					const editButton = document.createElement('button')
					editButton.type = 'button'
					editButton.className = 'button'
					editButton.textContent = 'Edit'
					const deleteButton = document.createElement('button')
					deleteButton.type = 'button'
					deleteButton.className = 'button'
					deleteButton.textContent = 'Delete'
					const syncButton = document.createElement('button')
					syncButton.type = 'button'
					syncButton.className = 'button'
					syncButton.textContent = 'Sync'
					const ndviButton = document.createElement('button')
					ndviButton.type = 'button'
					ndviButton.className = 'button'
					ndviButton.textContent = 'NDVI'
					const weatherButton = document.createElement('button')
					weatherButton.type = 'button'
					weatherButton.className = 'button'
					weatherButton.textContent = 'Weather'
					const observationsButton = document.createElement('button')
					observationsButton.type = 'button'
					observationsButton.className = 'button'
					observationsButton.textContent = 'Observations'
					const activitiesButton = document.createElement('button')
					activitiesButton.type = 'button'
					activitiesButton.className = 'button'
					activitiesButton.textContent = 'Activities'

					const farmId = resolveFarmId(farm)
					if (farmId === null) {
						editButton.disabled = true
						deleteButton.disabled = true
						syncButton.disabled = true
						ndviButton.disabled = true
						weatherButton.disabled = true
						observationsButton.disabled = true
						activitiesButton.disabled = true
					} else {
						editButton.addEventListener('click', () => openFarmModal('edit', farmId))
						deleteButton.addEventListener('click', () => deleteFarm(farmId))
						syncButton.addEventListener('click', () => openSyncFarmModal(farmId, farm))
						ndviButton.addEventListener('click', () => openNdviPanel(farmId, farm))
						weatherButton.addEventListener('click', () => openWeatherPanel(farmId, farm))
						observationsButton.addEventListener('click', () => openObservationsPanel(farmId, farm))
						activitiesButton.addEventListener('click', () => openActivitiesPanel(farmId, farm))
					}

					actions.appendChild(editButton)
					actions.appendChild(deleteButton)
					actions.appendChild(syncButton)
					actions.appendChild(ndviButton)
					actions.appendChild(weatherButton)
					actions.appendChild(observationsButton)
					actions.appendChild(activitiesButton)
					row.appendChild(actions)
					farmsBody.appendChild(row)
				})
			}

			const parseQueryParams = (url) => {
				if (!url || typeof url !== 'string') {
					return null
				}
				const queryIndex = url.indexOf('?')
				if (queryIndex === -1) {
					return null
				}
				const params = new URLSearchParams(url.slice(queryIndex + 1))
				const result = {}
				params.forEach((value, key) => {
					if (Object.prototype.hasOwnProperty.call(result, key)) {
						if (Array.isArray(result[key])) {
							result[key].push(value)
						} else {
							result[key] = [result[key], value]
						}
					} else {
						result[key] = value
					}
				})
				return result
			}

			const renderPagination = (payload, itemCount) => {
				if (!farmsPagination || !farmsPage || !farmsPrev || !farmsNext) {
					return
				}
				const next = payload?.next ?? null
				const previous = payload?.previous ?? null
				const count = Number.isFinite(payload?.count) ? payload.count : null
				nextParams = parseQueryParams(next)
				prevParams = parseQueryParams(previous)

				if (!nextParams && !prevParams && count === null) {
					farmsPagination.hidden = true
					return
				}

				farmsPagination.hidden = false
				farmsPrev.disabled = !prevParams || !schemaReady
				farmsNext.disabled = !nextParams || !schemaReady
				farmsPage.textContent = count !== null
					? `${itemCount} of ${count}`
					: 'Pagination available'
			}

			const loadSchema = async () => {
				clearFarmsNotes()
				if (!farmSchemaUrl) {
					showFarmsError('Farm schema endpoint is not available.')
					return false
				}

				const result = await performJsonRequest('GET', farmSchemaUrl)
				if (!result.parsed) {
					showFarmsError('Unable to parse farm schema response.')
					return false
				}
				const respOk = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
				if (!respOk) {
					const message = pickMessage(result.data, 'Unable to load farm schema.')
					showFarmsError(message)
					return false
				}

				const payload = unwrapResponseData(result.data)
				const schemaContainer = payload?.schema ?? payload ?? {}
				const unwrappedSchema = unwrapSchemaContainer(schemaContainer)
				const openApiFields = extractOpenApiFarmFields(unwrappedSchema)
				const openApiWritable = filterWritableFields(openApiFields)

				farmSchema = unwrappedSchema ?? {}
				farmFields = normalizeFields(pickObject(
					payload?.fields,
					farmSchema?.fields,
					schemaContainer?.fields,
					openApiFields,
				))
				farmFieldsCreate = normalizeFields(pickObject(
					payload?.fieldsCreate,
					farmSchema?.fieldsCreate,
					schemaContainer?.fieldsCreate,
					openApiWritable,
					farmFields,
				))
				farmFieldsUpdate = normalizeFields(pickObject(
					payload?.fieldsUpdate,
					farmSchema?.fieldsUpdate,
					schemaContainer?.fieldsUpdate,
					openApiWritable,
					farmFieldsCreate,
					farmFields,
				))
				farmColumns = pickArray(
					payload?.columns,
					farmSchema?.columns,
					schemaContainer?.columns,
					Object.keys(openApiFields),
				)
				farmOperations = pickObject(payload?.operations, farmSchema?.operations, schemaContainer?.operations)

				if (!farmFields || Object.keys(farmFields).length === 0) {
					showFarmsError('Farm schema did not return any fields.')
					return false
				}
				if (!farmColumns || farmColumns.length === 0) {
					farmColumns = Object.keys(farmFields)
				}
				if (!farmColumns || farmColumns.length === 0) {
					showFarmsError('Farm schema did not return any columns.')
					return false
				}

				if (payload?.warning) {
					showFarmsWarning(payload.warning)
				}

				renderColumns()
				return true
			}

			const refreshFarms = async (params = null) => {
				const schemaOk = await getSchemaReady('refresh farms')
				logFarms('refresh schema gate', { ok: schemaOk })
				if (!schemaOk) {
					return
				}
				clearFarmsNotes()
				if (!farmListUrl) {
					showFarmsError('Farm list endpoint is not available.')
					return
				}

				const result = await performJsonRequest('POST', farmListUrl, {
					body: params || {},
				})
				if (!result.parsed) {
					showFarmsError('Unable to parse farm list response.')
					return
				}
				const okList = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
				if (!okList) {
					const message = pickMessage(result.data, 'Unable to load farms.')
					showFarmsError(message)
					return
				}

				const payload = unwrapResponseData(result.data)
				const items = Array.isArray(payload)
					? payload
					: Array.isArray(payload?.results)
						? payload.results
						: []
				renderRows(items)
				renderPagination(payload, items.length)
			}

			const resolveModalFields = (mode) => {
				const preferred = mode === 'edit' ? farmFieldsUpdate : farmFieldsCreate
				if (preferred && typeof preferred === 'object' && Object.keys(preferred).length > 0) {
					return preferred
				}
				return farmFields || {}
			}

			const openFarmModal = async (mode, farmId) => {
				const schemaOk = await getSchemaReady(`${mode} farm`)
				logFarms(`${mode} farm schema gate`, { ok: schemaOk })
				if (!schemaOk) {
					return
				}
				if (!farmsModal || !farmsModalFields || !farmsModalTitle || !farmsModalSave) {
					return
				}
				clearFarmsNotes()
				modalInitial = {}
				let existing = {}

				if (mode === 'edit') {
					const url = farmGetUrl.replace('__ID__', encodeURIComponent(farmId))
					const result = await performJsonRequest('GET', url)
					if (!result.parsed) {
						showFarmsError('Unable to parse farm response.')
						return
					}
					const respOk = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
					if (!respOk) {
						const message = pickMessage(result.data, 'Unable to load farm.')
						showFarmsError(message)
						return
					}
					existing = unwrapResponseData(result.data) ?? {}
					modalInitial = existing
				}

				farmsModalTitle.textContent = mode === 'edit' ? 'Edit farm' : 'Create farm'
				farmsModalFields.innerHTML = ''

				const fieldSet = resolveModalFields(mode)
				const entries = Object.entries(fieldSet).filter(([, def]) => !def?.readOnly)
				if (entries.length === 0) {
					showFarmsError('Farm schema did not return any writable fields.')
					return
				}
				entries.forEach(([name, def]) => {
					const row = document.createElement('div')
					const label = document.createElement('label')
					label.textContent = `${name}${def?.required ? ' *' : ''}`
					const input = document.createElement('input')
					input.dataset.fieldName = name
					if (def?.type === 'boolean') {
						input.type = 'checkbox'
						input.checked = Boolean(existing?.[name])
					} else if (def?.type === 'integer' || def?.type === 'number') {
						input.type = 'number'
						input.step = def?.type === 'integer' ? '1' : 'any'
						if (existing?.[name] !== undefined && existing?.[name] !== null) {
							input.value = String(existing[name])
						}
					} else if (def?.format === 'date') {
						input.type = 'date'
						if (existing?.[name]) {
							input.value = String(existing[name]).slice(0, 10)
						}
					} else {
						input.type = 'text'
						if (existing?.[name] !== undefined && existing?.[name] !== null) {
							input.value = String(existing[name])
						}
					}
					if (def?.required) {
						input.required = true
					}
					row.appendChild(label)
					row.appendChild(input)
					farmsModalFields.appendChild(row)
				})

				farmsModal.hidden = false

				farmsModalSave.onclick = async () => {
					const payload = {}
					const entries = Object.entries(fieldSet).filter(([, def]) => !def?.readOnly)
					for (const [name, def] of entries) {
						const input = farmsModalFields.querySelector(`[data-field-name="${name}"]`)
						if (!input) {
							continue
						}
						let value = null
						if (def?.type === 'boolean') {
							value = Boolean(input.checked)
						} else if (def?.type === 'integer' || def?.type === 'number') {
							value = input.value === '' ? null : Number(input.value)
						} else {
							value = input.value !== undefined ? String(input.value).trim() : ''
						}

						if (mode === 'create') {
							if (def?.required && (value === null || value === '')) {
								showFarmsError(`Missing required field: ${name}`)
								return
							}
							if (value !== null && value !== '') {
								payload[name] = value
							}
						} else {
							const initial = modalInitial?.[name]
							const normalizedInitial = def?.type === 'boolean'
								? Boolean(initial)
								: (def?.type === 'integer' || def?.type === 'number')
									? (initial === undefined || initial === null || initial === '' ? null : Number(initial))
									: (initial === undefined || initial === null ? '' : String(initial))
							const normalizedValue = def?.type === 'boolean'
								? Boolean(value)
								: (def?.type === 'integer' || def?.type === 'number')
									? (value === null || value === '' ? null : Number(value))
									: (value === null ? '' : String(value))
							if (normalizedValue !== normalizedInitial) {
								payload[name] = value
							}
						}
					}

					if (mode === 'edit' && Object.keys(payload).length === 0) {
						showFarmsError('No changes to save.')
						return
					}

					const url = mode === 'edit'
						? farmPatchUrl.replace('__ID__', encodeURIComponent(farmId))
						: farmCreateUrl
					const method = mode === 'edit' ? 'PATCH' : 'POST'
					const result = await performJsonRequest(method, url, { body: payload })
					if (!result.parsed) {
						showFarmsError('Unable to parse farm save response.')
						return
					}
					const okSave = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
					if (!okSave) {
						const message = pickMessage(result.data, 'Unable to save farm.')
						showFarmsError(message)
						return
					}

					farmsModal.hidden = true
					await refreshFarms()
				}
			}

			const closeFarmModal = () => {
				if (farmsModal) {
					farmsModal.hidden = true
				}
			}

			const confirmDeleteAsync = () => new Promise((resolve) => {
				const dialogs = window.OC?.dialogs
				if (dialogs && typeof dialogs.confirm === 'function') {
					dialogs.confirm('Delete this farm?', 'Confirm deletion', (result) => resolve(result))
					return
				}
				resolve(false)
			})

			const confirmObservationDeleteAsync = () => new Promise((resolve) => {
				const dialogs = window.OC?.dialogs
				if (dialogs && typeof dialogs.confirm === 'function') {
					dialogs.confirm('Delete this observation?', 'Confirm deletion', (result) => resolve(result))
					return
				}
				resolve(false)
			})

			const deleteFarm = async (farmId) => {
				const schemaOk = await getSchemaReady('delete farm')
				logFarms('delete farm schema gate', { ok: schemaOk })
				if (!schemaOk) {
					return
				}
				clearFarmsNotes()
				const confirmed = await confirmObservationDeleteAsync()
				if (!confirmed) {
					return
				}
				const url = farmDeleteUrl.replace('__ID__', encodeURIComponent(farmId))
				const result = await performJsonRequest('DELETE', url)
				if (!result.parsed) {
					showFarmsError('Unable to parse delete response.')
					return
				}
				const okDelete = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
				if (!okDelete) {
					const message = pickMessage(result.data, 'Unable to delete farm.')
					showFarmsError(message)
					return
				}
				await refreshFarms()
			}

			const openSyncFarmModal = async (farmId, farm) => {
				const schemaOk = await getSchemaReady('open sync farm')
				logFarms('open sync farm schema gate', { ok: schemaOk })
				if (!schemaOk) {
					return
				}

				if (!farmSyncUrl) {
					showFarmsError('Farm sync endpoint is not available.')
					return
				}

				// If farm data is incomplete, fetch full farm data first
				let fullFarm = farm
				if (!farm || typeof farm !== 'object' || !farm.name) {
					// Fetch full farm data from backend
					const getFarmUrl = farmGetUrl.replace('__ID__', encodeURIComponent(farmId))
					const result = await performJsonRequest('GET', getFarmUrl)
					if (!result.parsed || !result.response.ok) {
						showFarmsError('Unable to load farm data for sync.')
						return
					}
					const okLoad = result.data?.status === 'ok' || result.data?.ok === true
					if (!okLoad) {
						const message = pickMessage(result.data, 'Unable to load farm data.')
						showFarmsError(message)
						return
					}
					fullFarm = result.data?.data || result.data || {}
				}

				logFarms('sync farm data', fullFarm)

				if (farmsSyncModal) {
					farmsSyncModal.hidden = false
				}

				if (syncExternalFarmIdInput) {
					// Generate a proper UUID if external_farm_id is missing
					const existingId = fullFarm.external_farm_id
					if (existingId && String(existingId).trim() !== '') {
						syncExternalFarmIdInput.value = String(existingId)
					} else {
						// Generate UUID v4
						const uuid = crypto.randomUUID()
						syncExternalFarmIdInput.value = uuid
					}
				}
				if (syncExternalUserIdInput) {
					// Use actual Nextcloud user ID if available
					// OC.getUser() returns {uid: 'username'} in most Nextcloud versions
					let userId = 'nextcloud-admin'
					try {
						const ocUser = window.OC?.getUser?.()
						if (ocUser && typeof ocUser === 'object') {
							userId = ocUser.uid || ocUser.id || 'nextcloud-admin'
						}
					} catch (e) {
						// Fallback if OC.getUser is not available
						console.warn('[farm_intelligence_platform] Could not get current user, using fallback')
					}
					syncExternalUserIdInput.value = fullFarm.external_user_id || userId
				}
				if (syncNameInput) {
					syncNameInput.value = fullFarm.name || ''
				}

				currentSyncFarmId = farmId
				currentSyncFarmData = fullFarm
			}

			const closeSyncFarmModal = () => {
				currentSyncFarmId = null
				currentSyncFarmData = null
				if (farmsSyncModal) {
					farmsSyncModal.hidden = true
				}
			}

			const syncFarm = async (farmId, farm) => {
				const schemaOk = await getSchemaReady('sync farm')
				logFarms('sync farm schema gate', { ok: schemaOk })
				if (!schemaOk) {
					return
				}
				clearFarmsNotes()

				if (!farmSyncUrl) {
					showFarmsError('Farm sync endpoint is not available.')
					return
				}

				// Use the full farm data we fetched earlier
				const fullFarm = currentSyncFarmData || farm
				logFarms('sync farm payload', fullFarm)

				// Build bbox - use existing values or defaults for missing data
				const hasBbox = fullFarm.bbox_south !== null && fullFarm.bbox_south !== undefined
					&& fullFarm.bbox_west !== null && fullFarm.bbox_west !== undefined
					&& fullFarm.bbox_north !== null && fullFarm.bbox_north !== undefined
					&& fullFarm.bbox_east !== null && fullFarm.bbox_east !== undefined
					&& String(fullFarm.bbox_south).trim() !== ''
					&& String(fullFarm.bbox_west).trim() !== ''
					&& String(fullFarm.bbox_north).trim() !== ''
					&& String(fullFarm.bbox_east).trim() !== ''

				const bboxPayload = hasBbox
					? {
						south: Number(fullFarm.bbox_south) || 0,
						west: Number(fullFarm.bbox_west) || 0,
						north: Number(fullFarm.bbox_north) || 0,
						east: Number(fullFarm.bbox_east) || 0,
					}
					: {
						south: -1.0,
						west: 36.0,
						north: 1.0,
						east: 38.0,
					}

				// Build centroid - only include if both lat and lon are valid numbers
				const latVal = fullFarm.centroid_lat
				const lonVal = fullFarm.centroid_lon
				const hasValidCentroid = latVal !== null && latVal !== undefined
					&& lonVal !== null && lonVal !== undefined
					&& String(latVal).trim() !== ''
					&& String(lonVal).trim() !== ''
					&& !isNaN(Number(latVal))
					&& !isNaN(Number(lonVal))

				const providedExternalFarmId = syncExternalFarmIdInput?.value?.trim()
				const externalFarmId = providedExternalFarmId && providedExternalFarmId !== ''
					? providedExternalFarmId
					: crypto.randomUUID()
				if (syncExternalFarmIdInput) {
					syncExternalFarmIdInput.value = externalFarmId
				}
				const payload = {
					external_farm_id: externalFarmId,
					external_user_id: syncExternalUserIdInput?.value || (() => {
						try {
							return window.OC?.getUser?.()?.uid ?? 'nextcloud-admin'
						} catch {
							return 'nextcloud-admin'
						}
					})(),
					name: syncNameInput?.value || fullFarm.name || 'Synced Farm',
					bbox: bboxPayload,
				}

				// Only add centroid if we have valid numeric data
				if (hasValidCentroid) {
					payload.centroid = {
						lat: Number(latVal),
						lon: Number(lonVal),
					}
				}

				// Debug: log the final payload (development only)
				// TODO: Remove before production deployment
				if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
					logFarms('sync farm final payload', payload)
				}

				const idempotencyKey = `farm-sync:${externalFarmId}`
				const result = await performJsonRequest('POST', farmSyncUrl, {
					body: payload,
					headers: {
						'Idempotency-Key': idempotencyKey,
					},
				})
				if (!result.parsed) {
					logFarms('sync farm response not json', { responseText: result.text })
					showFarmsError('Unable to parse sync response.')
					return
				}
				const okSync = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
				if (!okSync) {
					logFarms('sync farm failed', result.data ?? {})
					const message = pickMessage(result.data, 'Unable to sync farm.')
					showFarmsError(message)
					return
				}

				closeSyncFarmModal()
				toast('Farm synced successfully.')
				await refreshFarms()
			}

			const clearNdviOutput = () => {
				clearNdviError()
				destroyRasterMap()
				if (ndviOutput) ndviOutput.textContent = ''
				if (ndviCalendar) ndviCalendar.hidden = true
				if (ndviWeekdays) ndviWeekdays.replaceChildren()
				if (ndviCalendarGrid) ndviCalendarGrid.replaceChildren()
				if (ndviTable) ndviTable.textContent = ''
				if (ndviRasterPreview) ndviRasterPreview.hidden = true
				if (ndviRasterImg) ndviRasterImg.removeAttribute('src')
				if (ndviRasterObjectUrl) {
					URL.revokeObjectURL(ndviRasterObjectUrl)
					ndviRasterObjectUrl = null
				}
			}

			const clearNdviError = () => {
				if (!ndviError) {
					return
				}
				ndviError.textContent = ''
				ndviError.hidden = true
			}

			const showNdviError = (message) => {
				if (!ndviError) {
					return
				}
				ndviError.textContent = message
				ndviError.hidden = false
			}

			const ISO_DATE_PATTERN = /^\d{4}-\d{2}-\d{2}$/
			const SLASH_DATE_PATTERN = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/

			const parseIsoDate = (value) => {
				const raw = String(value ?? '').trim()
				if (raw === '') {
					return { raw: '', iso: null, date: null, invalid: false }
				}
				let year
				let month
				let day
				let iso = null
				if (ISO_DATE_PATTERN.test(raw)) {
					[year, month, day] = raw.split('-').map((part) => Number(part))
					iso = raw
				} else {
					const match = raw.match(SLASH_DATE_PATTERN)
					if (!match) {
						return { raw, iso: null, date: null, invalid: true }
					}
					month = Number(match[1])
					day = Number(match[2])
					year = Number(match[3])
					iso = `${String(year).padStart(4, '0')}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`
				}
				const date = new Date(Date.UTC(year, month - 1, day))
				if (Number.isNaN(date.getTime())) {
					return { raw, iso: null, date: null, invalid: true }
				}
				if (date.getUTCFullYear() !== year || date.getUTCMonth() !== month - 1 || date.getUTCDate() !== day) {
					return { raw, iso: null, date: null, invalid: true }
				}
				return { raw, iso, date, invalid: false }
			}

			const WEEKDAY_ABBREVIATIONS = ['Su', 'M', 'Tu', 'W', 'Th', 'F', 'Sa']
			const WEEKDAY_HEADER = ['M', 'Tu', 'W', 'Th', 'F', 'Sa', 'Su']
			const MONTH_ABBREVIATIONS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

			const formatWeekday = (dayIndex) => WEEKDAY_ABBREVIATIONS[dayIndex] ?? ''
			const formatMonth = (monthIndex) => MONTH_ABBREVIATIONS[monthIndex] ?? ''
			const formatDateParts = (year, month, day) => `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`
			const formatDayMonth = (date, useUtc = false, includeYear = false) => {
				const day = useUtc ? date.getUTCDate() : date.getDate()
				const month = formatMonth(useUtc ? date.getUTCMonth() : date.getMonth())
				const year = useUtc ? date.getUTCFullYear() : date.getFullYear()
				const base = `${day} ${month}`.trim()
				return includeYear ? `${base} ${year}` : base
			}
			const shouldIncludeYear = (date, useUtc = false) => {
				const year = useUtc ? date.getUTCFullYear() : date.getFullYear()
				const now = new Date()
				const currentYear = useUtc ? now.getUTCFullYear() : now.getFullYear()
				return year !== currentYear
			}
			const formatWeekdayDateLabel = (date, { useUtc = false, includeYear = null } = {}) => {
				const includeYearFinal = includeYear === null ? shouldIncludeYear(date, useUtc) : includeYear
				const weekday = formatWeekday(useUtc ? date.getUTCDay() : date.getDay())
				const datePart = formatDayMonth(date, useUtc, includeYearFinal)
				return weekday ? `${weekday} ${datePart}` : datePart
			}
			const formatTime = (date) => {
				const hours = String(date.getHours()).padStart(2, '0')
				const minutes = String(date.getMinutes()).padStart(2, '0')
				return `${hours}:${minutes}`
			}
			const formatWeekdayDateTimeLabel = (date) => `${formatWeekdayDateLabel(date)} \u2022 ${formatTime(date)}`

			const readNdviDateState = () => ({
				start: parseIsoDate(ndviStartInput?.value),
				end: parseIsoDate(ndviEndInput?.value),
				raster: parseIsoDate(ndviDateInput?.value),
			})

			const resolveIsoDateValue = (input) => {
				const parsed = parseIsoDate(input?.value)
				return parsed.iso ?? null
			}

			const validateTimeseriesInputs = (state) => {
				if (!state.start.raw || !state.end.raw) {
					return { ok: false, message: 'Start and end dates are required.' }
				}
				if (state.start.invalid || state.end.invalid) {
					return { ok: false, message: 'Use valid dates in YYYY-MM-DD format (e.g., 2026-01-20).' }
				}
				if (state.start.date && state.end.date && state.start.date > state.end.date) {
					return { ok: false, message: 'Start date must be on or before end date.' }
				}
				return { ok: true, start: state.start.iso, end: state.end.iso }
			}

			const validateRasterInput = (state) => {
				if (!state.raster.raw) {
					return { ok: false, message: 'Raster date is required.' }
				}
				if (state.raster.invalid) {
					return { ok: false, message: 'Use a valid raster date (YYYY-MM-DD, e.g., 2026-01-20).' }
				}
				return { ok: true, date: state.raster.iso }
			}

			const updateNdviActionState = () => {
				if (!schemaReady) {
					if (ndviLatestButton) ndviLatestButton.disabled = true
					if (ndviTimeseriesButton) ndviTimeseriesButton.disabled = true
					if (ndviQueueButton) ndviQueueButton.disabled = true
					if (ndviRasterButton) ndviRasterButton.disabled = true
					if (ndviRefreshButton) ndviRefreshButton.disabled = true
					if (ndwiLatestButton) ndwiLatestButton.disabled = true
					if (ndwiTimeseriesButton) ndwiTimeseriesButton.disabled = true
					if (ndwiQueueButton) ndwiQueueButton.disabled = true
					if (ndwiRasterButton) ndwiRasterButton.disabled = true
					if (ndwiRefreshButton) ndwiRefreshButton.disabled = true
					if (ndmiLatestButton) ndmiLatestButton.disabled = true
					if (ndmiTimeseriesButton) ndmiTimeseriesButton.disabled = true
					if (ndmiQueueButton) ndmiQueueButton.disabled = true
					if (ndmiRasterButton) ndmiRasterButton.disabled = true
					if (ndmiRefreshButton) ndmiRefreshButton.disabled = true
					if (rviLatestButton) rviLatestButton.disabled = true
					if (rviTimeseriesButton) rviTimeseriesButton.disabled = true
					if (rviQueueButton) rviQueueButton.disabled = true
					if (rviRasterButton) rviRasterButton.disabled = true
					if (rviRefreshButton) rviRefreshButton.disabled = true
					if (s1SmiLatestButton) s1SmiLatestButton.disabled = true
					if (s1SmiTimeseriesButton) s1SmiTimeseriesButton.disabled = true
					if (s1SmiQueueButton) s1SmiQueueButton.disabled = true
					if (s1SmiRasterButton) s1SmiRasterButton.disabled = true
					if (s1SmiRefreshButton) s1SmiRefreshButton.disabled = true
					clearNdviError()
					return
				}
				const state = readNdviDateState()
				const timeseriesValidation = validateTimeseriesInputs(state)
				const rasterValidation = validateRasterInput(state)

				if (ndviTimeseriesButton) {
					ndviTimeseriesButton.disabled = !timeseriesValidation.ok
				}
				if (ndviQueueButton) {
					ndviQueueButton.disabled = !rasterValidation.ok
				}
				if (ndviRasterButton) {
					ndviRasterButton.disabled = !rasterValidation.ok
				}
				if (ndwiTimeseriesButton) {
					ndwiTimeseriesButton.disabled = !timeseriesValidation.ok
				}
				if (ndwiQueueButton) {
					ndwiQueueButton.disabled = !rasterValidation.ok
				}
				if (ndwiRasterButton) {
					ndwiRasterButton.disabled = !rasterValidation.ok
				}
				if (ndmiTimeseriesButton) {
					ndmiTimeseriesButton.disabled = !timeseriesValidation.ok
				}
				if (ndmiQueueButton) {
					ndmiQueueButton.disabled = !rasterValidation.ok
				}
				if (ndmiRasterButton) {
					ndmiRasterButton.disabled = !rasterValidation.ok
				}
				if (rviTimeseriesButton) {
					rviTimeseriesButton.disabled = !timeseriesValidation.ok
				}
				if (rviQueueButton) {
					rviQueueButton.disabled = !rasterValidation.ok
				}
				if (rviRasterButton) {
					rviRasterButton.disabled = !rasterValidation.ok
				}
				if (s1SmiTimeseriesButton) {
					s1SmiTimeseriesButton.disabled = !timeseriesValidation.ok
				}
				if (s1SmiQueueButton) {
					s1SmiQueueButton.disabled = !rasterValidation.ok
				}
				if (s1SmiRasterButton) {
					s1SmiRasterButton.disabled = !rasterValidation.ok
				}
				if (s3LstTimeseriesButton) {
					s3LstTimeseriesButton.disabled = !timeseriesValidation.ok
				}
				if (s3LstQueueButton) {
					s3LstQueueButton.disabled = !rasterValidation.ok
				}
				if (s3LstRasterButton) {
					s3LstRasterButton.disabled = !rasterValidation.ok
				}
				if (landsatLstTimeseriesButton) {
					landsatLstTimeseriesButton.disabled = !timeseriesValidation.ok
				}
				if (landsatLstQueueButton) {
					landsatLstQueueButton.disabled = !rasterValidation.ok
				}
				if (landsatLstRasterButton) {
					landsatLstRasterButton.disabled = !rasterValidation.ok
				}
				if (ironOxideTimeseriesButton) {
					ironOxideTimeseriesButton.disabled = !timeseriesValidation.ok
				}
				if (ironOxideQueueButton) {
					ironOxideQueueButton.disabled = !rasterValidation.ok
				}
				if (ironOxideRasterButton) {
					ironOxideRasterButton.disabled = !rasterValidation.ok
				}
				if (eviTimeseriesButton) {
					eviTimeseriesButton.disabled = !timeseriesValidation.ok
				}
				if (eviQueueButton) {
					eviQueueButton.disabled = !rasterValidation.ok
				}
				if (eviRasterButton) {
					eviRasterButton.disabled = !rasterValidation.ok
				}
				if (lRviTimeseriesButton) {
					lRviTimeseriesButton.disabled = !timeseriesValidation.ok
				}
				if (lRviQueueButton) {
					lRviQueueButton.disabled = !rasterValidation.ok
				}
				if (lRviRasterButton) {
					lRviRasterButton.disabled = !rasterValidation.ok
				}
				if (nisarSmiTimeseriesButton) {
					nisarSmiTimeseriesButton.disabled = !timeseriesValidation.ok
				}
				if (nisarSmiQueueButton) {
					nisarSmiQueueButton.disabled = !rasterValidation.ok
				}
				if (nisarSmiRasterButton) {
					nisarSmiRasterButton.disabled = !rasterValidation.ok
				}
				if (ndreTimeseriesButton) {
					ndreTimeseriesButton.disabled = !timeseriesValidation.ok
				}
				if (ndreQueueButton) {
					ndreQueueButton.disabled = !rasterValidation.ok
				}
				if (ndreRasterButton) {
					ndreRasterButton.disabled = !rasterValidation.ok
				}
				if (biomassTimeseriesButton) {
					biomassTimeseriesButton.disabled = !timeseriesValidation.ok
				}
				if (biomassQueueButton) {
					biomassQueueButton.disabled = !rasterValidation.ok
				}
				if (biomassRasterButton) {
					biomassRasterButton.disabled = !rasterValidation.ok
				}

				const showTimeseriesError = (ndviTouched.start || ndviTouched.end || state.start.raw || state.end.raw)
				const showRasterError = (ndviTouched.raster || state.raster.raw)
				let message = ''
				if (!timeseriesValidation.ok && showTimeseriesError) {
					message = timeseriesValidation.message
				} else if (!rasterValidation.ok && showRasterError) {
					message = rasterValidation.message
				}

				if (message) {
					showNdviError(message)
				} else {
					clearNdviError()
				}
			}

			const resetNdviState = () => {
				ndviTouched = { start: false, end: false, raster: false }
				latestNdviState = reduceLatestState(null, { type: 'reset' })
				timeseriesNdviState = reduceTimeseriesState(null, { type: 'reset' }, null, null)
				latestNdwiState = reduceLatestState(null, { type: 'reset' })
				timeseriesNdwiState = reduceTimeseriesState(null, { type: 'reset' }, null, null)
				latestNdmiState = reduceLatestState(null, { type: 'reset' })
				timeseriesNdmiState = reduceTimeseriesState(null, { type: 'reset' }, null, null)
				latestRviState = reduceLatestState(null, { type: 'reset' })
				timeseriesRviState = reduceTimeseriesState(null, { type: 'reset' }, null, null)
				latestS1SmiState = reduceLatestState(null, { type: 'reset' })
				timeseriesS1SmiState = reduceTimeseriesState(null, { type: 'reset' }, null, null)
				latestLandsatLstState = reduceLatestState(null, { type: 'reset' })
				timeseriesLandsatLstState = reduceTimeseriesState(null, { type: 'reset' }, null, null)
				latestIronOxideState = reduceLatestState(null, { type: 'reset' })
				timeseriesIronOxideState = reduceTimeseriesState(null, { type: 'reset' }, null, null)
				latestEviState = reduceLatestState(null, { type: 'reset' })
				timeseriesEviState = reduceTimeseriesState(null, { type: 'reset' }, null, null)
				latestLRviState = reduceLatestState(null, { type: 'reset' })
				timeseriesLRviState = reduceTimeseriesState(null, { type: 'reset' }, null, null)
				latestNisarSmiState = reduceLatestState(null, { type: 'reset' })
				timeseriesNisarSmiState = reduceTimeseriesState(null, { type: 'reset' }, null, null)
				latestNdreState = reduceLatestState(null, { type: 'reset' })
				timeseriesNdreState = reduceTimeseriesState(null, { type: 'reset' }, null, null)
				latestBiomassState = reduceLatestState(null, { type: 'reset' })
				timeseriesBiomassState = reduceTimeseriesState(null, { type: 'reset' }, null, null)
				updateNdviActionState()
			}

			const resolveParamName = (params, desired) => {
				if (!Array.isArray(params)) {
					return null
				}
				const exact = params.find((param) => param?.name === desired)
				if (exact) {
					return exact.name
				}
				const fuzzy = params.find((param) => typeof param?.name === 'string' && param.name.includes(desired))
				return fuzzy?.name ?? null
			}

			const resolveBodyFieldName = (fields, desired) => {
				if (!fields || typeof fields !== 'object') {
					return null
				}
				if (fields[desired]) {
					return desired
				}
				const match = Object.keys(fields).find((name) => name.includes(desired))
				return match ?? null
			}

			const NDVI_DEFAULT_MAX_CLOUD = 60
			const NDVI_DEFAULT_RASTER_SIZE = 512

			const buildNdviQuery = (operationKey, overrides = {}) => {
				const operation = resolveOperation(operationKey)
				const params = operation?.queryParams ?? []
				const query = {}
				const startName = resolveParamName(params, 'start')
				const endName = resolveParamName(params, 'end')
				const dateName = resolveParamName(params, 'date')
				const sizeName = resolveParamName(params, 'size')
				const maxCloudName = resolveParamName(params, 'max_cloud')
				const startValue = overrides.start ?? resolveIsoDateValue(ndviStartInput)
				const endValue = overrides.end ?? resolveIsoDateValue(ndviEndInput)
				const dateValue = overrides.date ?? resolveIsoDateValue(ndviDateInput)
				const sizeValue = overrides.size ?? NDVI_DEFAULT_RASTER_SIZE
				const maxCloudValue = overrides.max_cloud ?? NDVI_DEFAULT_MAX_CLOUD
				if (startName && startValue) {
					query[startName] = startValue
				}
				if (endName && endValue) {
					query[endName] = endValue
				}
				if (dateName && dateValue) {
					query[dateName] = dateValue
				}
				if (sizeName && sizeValue !== null && sizeValue !== undefined && sizeValue !== '') {
					query[sizeName] = sizeValue
				}
				if (maxCloudName && maxCloudValue !== null && maxCloudValue !== undefined && maxCloudValue !== '') {
					query[maxCloudName] = maxCloudValue
				}
				return query
			}

			const buildNdviBody = (operationKey, overrides = {}) => {
				const operation = resolveOperation(operationKey)
				const fields = operation?.bodyFields ?? {}
				const body = {}
				const startName = resolveBodyFieldName(fields, 'start')
				const endName = resolveBodyFieldName(fields, 'end')
				const dateName = resolveBodyFieldName(fields, 'date')
				const sizeName = resolveBodyFieldName(fields, 'size')
				const maxCloudName = resolveBodyFieldName(fields, 'max_cloud')
				const startValue = overrides.start ?? resolveIsoDateValue(ndviStartInput)
				const endValue = overrides.end ?? resolveIsoDateValue(ndviEndInput)
				const dateValue = overrides.date ?? resolveIsoDateValue(ndviDateInput)
				const sizeValue = overrides.size ?? NDVI_DEFAULT_RASTER_SIZE
				const maxCloudValue = overrides.max_cloud ?? NDVI_DEFAULT_MAX_CLOUD
				if (startName && startValue) {
					body[startName] = startValue
				}
				if (endName && endValue) {
					body[endName] = endValue
				}
				if (dateName && dateValue) {
					body[dateName] = dateValue
				}
				if (sizeName && sizeValue !== null && sizeValue !== undefined && sizeValue !== '') {
					body[sizeName] = sizeValue
				}
				if (maxCloudName && maxCloudValue !== null && maxCloudValue !== undefined && maxCloudValue !== '') {
					body[maxCloudName] = maxCloudValue
				}
				return body
			}

			const formatFactLabel = (label) => {
				const raw = String(label ?? '').trim()
				if (!raw) {
					return ''
				}
				return raw
					.replace(/_/g, ' ')
					.replace(/([a-z])([A-Z])/g, '$1 $2')
					.replace(/\s+/g, ' ')
					.replace(/^./, (char) => char.toUpperCase())
			}

			const normalizeFactValue = (value) => {
				if (value === null || value === undefined) {
					return ''
				}
				if (typeof value === 'string') {
					const trimmed = value.trim()
					return trimmed || ''
				}
				if (typeof value === 'number' || typeof value === 'boolean') {
					return String(value)
				}
				if (Array.isArray(value)) {
					const parts = value
						.map((item) => normalizeFactValue(item))
						.filter((part) => part !== '')
					return parts.length ? parts.join(', ') : ''
				}
				return ''
			}

			const renderKeyValueFacts = (facts) => {
				const entries = []
				if (Array.isArray(facts)) {
					facts.forEach((fact) => {
						if (fact && typeof fact === 'object') {
							entries.push({ label: fact.label, value: fact.value })
						}
					})
				} else if (facts && typeof facts === 'object') {
					Object.entries(facts).forEach(([label, value]) => {
						entries.push({ label, value })
					})
				}
				const normalized = entries
					.map(({ label, value }) => {
						const formattedLabel = formatFactLabel(label)
						const formattedValue = normalizeFactValue(value)
						if (!formattedLabel || formattedValue === '') {
							return null
						}
						return { label: formattedLabel, value: formattedValue }
					})
					.filter(Boolean)
				if (normalized.length === 0) {
					return null
				}
				const list = document.createElement('dl')
				list.className = 'farm-intelligence-platform-result__facts'
				normalized.forEach(({ label, value }) => {
					const term = document.createElement('dt')
					term.textContent = label
					const detail = document.createElement('dd')
					detail.textContent = value
					list.appendChild(term)
					list.appendChild(detail)
				})
				return list
			}

			const renderDebugDetails = (debug) => {
				if (debug === undefined || debug === null) {
					return null
				}
				const details = document.createElement('details')
				details.className = 'farm-intelligence-platform-result__debug'
				const summary = document.createElement('summary')
				summary.textContent = 'Debug'
				const pre = document.createElement('pre')
				const debugText = typeof debug === 'string'
					? debug
					: JSON.stringify(debug, null, 2)
				pre.textContent = debugText || ''
				details.appendChild(summary)
				details.appendChild(pre)
				return details
			}

			const renderResultCard = ({
				title,
				level = 'info',
				summary,
				badges = [],
				callout,
				facts,
				debug,
			} = {}) => {
				const levels = new Set(['success', 'warning', 'error', 'info'])
				const resolvedLevel = levels.has(level) ? level : 'info'
				const card = document.createElement('div')
				card.className = `farm-intelligence-platform-result farm-intelligence-platform-result--${resolvedLevel}`

				const header = document.createElement('div')
				header.className = 'farm-intelligence-platform-result__header'
				const body = document.createElement('div')
				body.className = 'farm-intelligence-platform-result__body'
				const teaser = document.createElement('div')
				teaser.className = 'farm-intelligence-platform-result__teaser'
				teaser.hidden = true

				if (title) {
					const heading = document.createElement('strong')
					heading.className = 'farm-intelligence-platform-result__title'
					heading.textContent = toText(title, '')
					header.appendChild(heading)
				}
				if (Array.isArray(badges) && badges.length > 0) {
					const badgeWrap = document.createElement('div')
					badgeWrap.className = 'farm-intelligence-platform-result__badges'
					badges.forEach((badge) => {
						const badgeEl = document.createElement('span')
						badgeEl.className = 'farm-intelligence-platform-result__badge'
						badgeEl.textContent = toText(badge, '')
						badgeWrap.appendChild(badgeEl)
					})
					header.appendChild(badgeWrap)
				}

				const actions = document.createElement('div')
				actions.className = 'farm-intelligence-platform-result__actions'
				const hideBtn = document.createElement('button')
				hideBtn.type = 'button'
				hideBtn.className = 'farm-intelligence-platform-result__close farm-intelligence-platform-result__close--secondary'
				hideBtn.textContent = 'Hide card'
				const setCollapsed = (collapsed) => {
					body.hidden = collapsed
					if (collapsed) {
						if (!card.contains(teaser)) {
							card.appendChild(teaser)
						}
					} else if (card.contains(teaser)) {
						teaser.remove()
					}
					card.classList.toggle('is-collapsed', collapsed)
					hideBtn.textContent = collapsed ? 'View details' : 'Hide card'
					hideBtn.setAttribute('aria-label', collapsed ? 'View details' : 'Hide card')
					hideBtn.title = collapsed ? 'View card details' : 'Hide card'
				}
				hideBtn.addEventListener('click', () => {
					setCollapsed(!body.hidden)
				})
				const dismissBtn = document.createElement('button')
				dismissBtn.type = 'button'
				dismissBtn.className = 'farm-intelligence-platform-result__close'
				dismissBtn.textContent = 'Dismiss'
				dismissBtn.title = 'Dismiss card'
				dismissBtn.setAttribute('aria-label', 'Dismiss card')
				dismissBtn.addEventListener('click', () => {
					card.remove()
				})
				actions.appendChild(hideBtn)
				actions.appendChild(dismissBtn)
				header.appendChild(actions)
				card.appendChild(header)
				card.appendChild(body)
				if (summary) {
					const summaryEl = document.createElement('p')
					summaryEl.className = 'farm-intelligence-platform-result__summary'
					summaryEl.textContent = toText(summary, '')
					body.appendChild(summaryEl)
				}
				if (callout) {
					const calloutEl = document.createElement('div')
					calloutEl.className = 'farm-intelligence-platform-result__callout'
					const calloutLabel = document.createElement('span')
					calloutLabel.className = 'farm-intelligence-platform-result__callout-label'
					calloutLabel.textContent = 'Action'
					const calloutValue = document.createElement('span')
					calloutValue.className = 'farm-intelligence-platform-result__callout-value'
					calloutValue.textContent = toText(callout, '')
					calloutEl.appendChild(calloutLabel)
					calloutEl.appendChild(calloutValue)
					body.appendChild(calloutEl)
				}
				const factsEl = renderKeyValueFacts(facts)
				if (factsEl) {
					body.appendChild(factsEl)
				}
				const debugEl = renderDebugDetails(debug)
				if (debugEl) {
					body.appendChild(debugEl)
				}
				const teaserLabel = document.createElement('span')
				teaserLabel.className = 'farm-intelligence-platform-result__teaser-label'
				teaserLabel.textContent = 'Card details are hidden. Use the header button to view details.'
				teaser.appendChild(teaserLabel)
				setCollapsed(false)
				return card
			}

			const replaceNdviOutput = (...nodes) => {
				if (!ndviOutput) {
					return
				}
				const filtered = nodes.filter(Boolean)
				ndviOutput.replaceChildren(...filtered)
			}

			const pushFact = (facts, label, value) => {
				const normalized = normalizeFactValue(value)
				if (!label || normalized === '') {
					return
				}
				facts.push({ label, value: normalized })
			}

			const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
			const normalizeNdviTimeseries = ndviUi.normalizeNdviTimeseries ?? ((raw, rangeStart, rangeEnd) => ({
				rangeStart,
				rangeEnd,
				receivedCount: 0,
				shownCount: 0,
				points: [],
				filterWarning: false,
				raw,
			}))
			const NDVI_LATEST_STATE = ndviUi.NDVI_LATEST_STATE ?? {
				loading: 'loading',
				error: 'error',
				no_data: 'no_data',
				fresh: 'fresh',
				stale: 'stale',
			}
			const NDVI_SERIES_STATE = ndviUi.NDVI_SERIES_STATE ?? {
				loading: 'loading',
				error: 'error',
				no_data: 'no_data',
				has_data: 'has_data',
			}
			reduceLatestState = ndviUi.reduceLatestState ?? ((state, action) => ({
				status: NDVI_LATEST_STATE.no_data,
				vm: null,
				payload: action?.payload ?? null,
				message: action?.message ?? '',
			}))
			reduceTimeseriesState = ndviUi.reduceTimeseriesState ?? ((state, action, rangeStart, rangeEnd) => ({
				status: NDVI_SERIES_STATE.no_data,
				vm: normalizeNdviTimeseries(null, rangeStart, rangeEnd),
				payload: action?.payload ?? null,
				message: action?.message ?? '',
			}))
			const buildLatestCardModel = ndviUi.buildLatestCardModel ?? ((state, label) => ({
				title: label ?? 'Latest NDVI',
				level: 'info',
				summary: '',
				badges: [],
				facts: [],
				showRetry: false,
			}))
			const buildTimeseriesCardModel = ndviUi.buildTimeseriesCardModel ?? ((state, label) => ({
				title: label ?? 'NDVI timeseries',
				level: 'info',
				summary: '',
				badges: [],
				facts: [],
				showRetry: false,
				emptyMessage: '',
			}))

			const formatNdviNumber = typeof ndviUi.formatNumber === 'function'
				? ndviUi.formatNumber
				: (value, digits = 3) => {
					if (value === null || value === undefined || value === '') {
						return '-'
					}
					const num = Number(value)
					if (!Number.isFinite(num)) {
						return '-'
					}
					return num.toFixed(digits)
				}
			const formatNdviPercent = typeof ndviUi.formatPercent === 'function'
				? ndviUi.formatPercent
				: (value, digits = 1) => {
					if (value === null || value === undefined || value === '') {
						return '-'
					}
					const num = Number(value)
					if (!Number.isFinite(num)) {
						return '-'
					}
					const scaled = num * 100
					return `${formatNdviNumber(scaled, digits)}%`
				}
			const formatNdviCount = typeof ndviUi.formatCount === 'function'
				? ndviUi.formatCount
				: (value) => formatNdviNumber(value, 0)
			const formatNdviDate = typeof ndviUi.formatDateWithWeekday === 'function'
				? ndviUi.formatDateWithWeekday
				: (value) => {
					const raw = value ? String(value).trim() : ''
					if (!raw) {
						return ''
					}
					if (ISO_DATE_PATTERN.test(raw)) {
						const parsed = parseIsoDate(raw)
						if (parsed.date && !parsed.invalid) {
							return formatWeekdayDateLabel(parsed.date, { useUtc: true })
						}
						return raw
					}
					const parsedDate = new Date(raw)
					if (Number.isNaN(parsedDate.getTime())) {
						return raw
					}
					return formatWeekdayDateLabel(parsedDate)
				}

			const appendRetryButton = (card, label, handler) => {
				if (!card || typeof handler !== 'function') {
					return
				}
				const actions = document.createElement('div')
				actions.className = 'farm-intelligence-platform-result__actions'
				const button = document.createElement('button')
				button.type = 'button'
				button.className = 'button'
				button.textContent = label || 'Retry'
				button.addEventListener('click', handler)
				actions.appendChild(button)
				card.appendChild(actions)
			}

			const renderLatestCard = (state, retryHandler, label) => {
				const model = buildLatestCardModel(state, label)
				const card = renderResultCard({
					title: model.title,
					level: model.level,
					summary: model.summary,
					badges: model.badges,
					facts: model.facts,
					debug: state?.payload,
				})
				if (model.showRetry) {
					appendRetryButton(card, 'Retry', retryHandler)
				}
				replaceNdviOutput(card)
				hideNdviCalendar()
			}

			const renderTimeseriesCard = (state, retryHandler, label) => {
				const model = buildTimeseriesCardModel(state, label)
				const card = renderResultCard({
					title: model.title,
					level: model.level,
					summary: model.summary,
					badges: model.badges,
					facts: model.facts,
					debug: state?.payload,
				})
				if (model.emptyMessage) {
					const emptyLine = document.createElement('p')
					emptyLine.className = 'farm-intelligence-platform-result__summary'
					emptyLine.textContent = model.emptyMessage
					card.appendChild(emptyLine)
				}
				if (model.showRetry) {
					appendRetryButton(card, 'Retry', retryHandler)
				}
				replaceNdviOutput(card)
			}

			const hideNdviCalendar = () => {
				if (ndviCalendar) ndviCalendar.hidden = true
				if (ndviWeekdays) ndviWeekdays.replaceChildren()
				if (ndviCalendarGrid) ndviCalendarGrid.replaceChildren()
			}

			const renderNdviWeekdays = () => {
				if (!ndviWeekdays) {
					return
				}
				const items = WEEKDAY_HEADER.map((label) => {
					const span = document.createElement('span')
					span.textContent = label
					return span
				})
				ndviWeekdays.replaceChildren(...items)
			}

			const formatUtcDateKey = (date) => formatDateParts(
				date.getUTCFullYear(),
				date.getUTCMonth() + 1,
				date.getUTCDate(),
			)

			const renderNdviCalendar = (state) => {
				if (!ndviCalendar || !ndviCalendarGrid) {
					return
				}
				const rangeStart = state?.vm?.rangeStart ?? null
				const rangeEnd = state?.vm?.rangeEnd ?? null
				const startInfo = parseIsoDate(rangeStart)
				const endInfo = parseIsoDate(rangeEnd)
				if (!startInfo?.date || !endInfo?.date || startInfo.invalid || endInfo.invalid) {
					hideNdviCalendar()
					return
				}
				renderNdviWeekdays()
				ndviCalendar.hidden = false
				ndviCalendarGrid.replaceChildren()
				const points = Array.isArray(state?.vm?.points) ? state.vm.points : []
				const pointsByDate = new Set(points.map((point) => point?.date).filter(Boolean))
				const endTime = endInfo.date.getTime()
				let cursor = new Date(startInfo.date.getTime())
				let index = 0
				while (cursor.getTime() <= endTime) {
					const iso = formatUtcDateKey(cursor)
					const cell = document.createElement('div')
					cell.className = 'farm-intelligence-platform-farms__ndvi-day'
					cell.textContent = String(cursor.getUTCDate())
					cell.setAttribute('role', 'gridcell')
					cell.dataset.date = iso
					cell.title = formatNdviDate(iso) || iso
					if (pointsByDate.has(iso)) {
						cell.classList.add('has-data')
					}
					if (index === 0) {
						const startColumn = ((cursor.getUTCDay() + 6) % 7) + 1
						cell.style.gridColumnStart = String(startColumn)
					}
					ndviCalendarGrid.appendChild(cell)
					cursor = new Date(Date.UTC(
						cursor.getUTCFullYear(),
						cursor.getUTCMonth(),
						cursor.getUTCDate() + 1,
					))
					index += 1
				}
			}

			latestNdviState = reduceLatestState(null, { type: 'reset' })
			timeseriesNdviState = reduceTimeseriesState(null, { type: 'reset' }, null, null)

			const NDVI_PERCENT_KEYS = new Set(['cloudFraction', 'cloud_fraction', 'cloudPct', 'cloud_pct'])
			const NDVI_COUNT_KEYS = new Set(['sampleCount', 'sample_count', 'samples'])
			const NDVI_DECIMAL_KEYS = new Set(['mean', 'min', 'max', 'value', 'ndvi', 'mean_ndvi'])
			const NDVI_DATE_KEYS = new Set(['date', 'bucket_date', 'bucketDate'])

			const resolveNdviColumnClass = (key, value) => {
				const keyName = String(key)
				if (NDVI_DATE_KEYS.has(keyName) || /date|day|timestamp/i.test(keyName)) {
					return 'is-date'
				}
				if (
					NDVI_PERCENT_KEYS.has(keyName)
					|| NDVI_COUNT_KEYS.has(keyName)
					|| NDVI_DECIMAL_KEYS.has(keyName)
					|| typeof value === 'number'
				) {
					return 'is-number'
				}
				return ''
			}

			const formatNdviTableValue = (key, value) => {
				if (value === null || value === undefined) {
					return ''
				}
				const keyName = String(key)
				if (NDVI_DATE_KEYS.has(keyName) || /date|day|timestamp/i.test(keyName)) {
					return formatNdviDate(value)
				}
				if (NDVI_PERCENT_KEYS.has(keyName)) {
					return formatNdviPercent(value)
				}
				if (NDVI_COUNT_KEYS.has(keyName)) {
					return formatNdviCount(value)
				}
				if (NDVI_DECIMAL_KEYS.has(keyName)) {
					return formatNdviNumber(value, 3)
				}
				if (typeof value === 'number') {
					return formatNdviNumber(value, 3)
				}
				return String(value)
			}

			const renderNdviTable = (items) => {
				if (!ndviTable) {
					return
				}
				if (!Array.isArray(items) || items.length === 0) {
					ndviTable.textContent = 'No observations.'
					return
				}
				const columns = Object.keys(items[0] || {})
				const table = document.createElement('table')
				table.className = 'farm-intelligence-platform-farms__table farm-intelligence-platform-farms__table--sticky'
				const thead = document.createElement('thead')
				const headerRow = document.createElement('tr')
				const columnClasses = columns.map((name) => resolveNdviColumnClass(name, items[0]?.[name]))
				columns.forEach((name, index) => {
					const th = document.createElement('th')
					th.textContent = name
					if (columnClasses[index]) {
						th.classList.add(columnClasses[index])
					}
					headerRow.appendChild(th)
				})
				thead.appendChild(headerRow)
				table.appendChild(thead)
				const tbody = document.createElement('tbody')
				items.forEach((item) => {
					const row = document.createElement('tr')
					columns.forEach((name, index) => {
						const cell = document.createElement('td')
						const value = item?.[name]
						cell.textContent = formatNdviTableValue(name, value)
						if (columnClasses[index]) {
							cell.classList.add(columnClasses[index])
						}
						row.appendChild(cell)
					})
					tbody.appendChild(row)
				})
				table.appendChild(tbody)
				ndviTable.replaceChildren()
				ndviTable.appendChild(table)
			}

			const openNdviPanel = (farmId, farm) => {
				selectedFarm = { id: farmId, data: farm }
				if (farmsNdvi) {
					farmsNdvi.hidden = false
				}
				if (farmsWeather) {
					farmsWeather.hidden = true
				}
				if (farmsObservations) {
					farmsObservations.hidden = true
				}
				if (farmsActivities) {
					farmsActivities.hidden = true
				}
				if (farmsNdviTitle) {
					const label = farm?.name ? `${farm.name} (#${farmId})` : `Farm #${farmId}`
					farmsNdviTitle.textContent = label
				}
				clearNdviOutput()
				resetNdviState()
			}

			const runNdviRequest = async (operationKey, urlTemplate, options = {}) => {
				const { returnRaw = false, ...requestOptions } = options
				const schemaOk = await getSchemaReady(`ndvi ${operationKey}`)
				logFarms(`ndvi ${operationKey} schema gate`, { ok: schemaOk })
				if (!schemaOk) {
					return null
				}
				clearFarmsNotes()
				clearNdviError()
				if (!selectedFarm) {
					showNdviError('Select a farm first.')
					return
				}
				const url = urlTemplate.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
				const result = await performJsonRequest(requestOptions.method || 'GET', url, requestOptions)
				const responseOk = Boolean(result.response?.ok)
				const contentType = result.response?.headers?.get
					? result.response.headers.get('content-type') || ''
					: ''
				const expectsJson = contentType === '' || contentType.includes('application/json')
				const snippet = (result.text || '').trim().slice(0, 200)
				const fallbackMessage = `Unable to load ${operationKey}.`

				if (!responseOk || !expectsJson) {
					const message = buildNdviErrorMessage(
						result.response,
						result.data,
						fallbackMessage,
						result.text || snippet,
					)
					showNdviError(message)
					if (shouldToastNdviError(message)) {
						toast(message)
					}
					return null
				}
				if (!result.parsed) {
					const message = buildNdviErrorMessage(
						result.response,
						result.data,
						'Unable to parse NDVI response.',
						result.text || snippet,
					)
					showNdviError(message)
					if (shouldToastNdviError(message)) {
						toast(message)
					}
					return null
				}
				const statusValue = result.data?.status
				const okNdvi = result.data?.ok === true || statusValue === 'ok' || statusValue === 0
				if (!okNdvi) {
					const message = buildNdviErrorMessage(
						result.response,
						result.data,
						fallbackMessage,
						result.text || snippet,
					)
					showNdviError(message)
					if (shouldToastNdviError(message)) {
						toast(message)
					}
					return null
				}
				return returnRaw ? result.data : unwrapResponseData(result.data)
			}

			const clearWeatherError = () => {
				if (!weatherError) {
					return
				}
				weatherError.textContent = ''
				weatherError.hidden = true
			}

			const clearObservationsError = () => {
				if (!farmsObservationsError) {
					return
				}
				farmsObservationsError.textContent = ''
				farmsObservationsError.hidden = true
			}

			const showObservationsError = (message) => {
				if (!farmsObservationsError) {
					return
				}
				farmsObservationsError.textContent = message
				farmsObservationsError.hidden = false
			}

			const showWeatherError = (message) => {
				if (!weatherError) {
					return
				}
				weatherError.textContent = message
				weatherError.hidden = false
			}

			const setWeatherLoading = (loading) => {
				if (weatherLoading) {
					weatherLoading.hidden = !loading
				}
				if (weatherCurrentTab) weatherCurrentTab.disabled = loading || !schemaReady
				if (weatherHourlyTab) weatherHourlyTab.disabled = loading || !schemaReady
				if (weatherDailyTab) weatherDailyTab.disabled = loading || !schemaReady
			}

			const setWeatherActiveTab = (tab) => {
				const tabs = [
					{ key: 'current', button: weatherCurrentTab, panel: weatherCurrentPanel },
					{ key: 'hourly', button: weatherHourlyTab, panel: weatherHourlyPanel },
					{ key: 'daily', button: weatherDailyTab, panel: weatherDailyPanel },
				]
				tabs.forEach(({ key, button, panel }) => {
					if (panel) {
						panel.hidden = key !== tab
					}
					if (button) {
						button.classList.toggle('active', key === tab)
					}
				})
			}

			const renderObservationsTable = (items) => {
				if (!farmsObservationsTable) {
					return
				}
				if (!Array.isArray(items) || items.length === 0) {
					farmsObservationsTable.textContent = 'No observations.'
					return
				}
				const columns = Object.keys(items[0] || {})
				const hasActions = columns.includes('id')
				const table = document.createElement('table')
				table.className = 'farm-intelligence-platform-farms__table farm-intelligence-platform-farms__table--sticky'
				const metadataColumn = columns.indexOf('metadata')
				const thead = document.createElement('thead')
				const headerRow = document.createElement('tr')
				columns.forEach((name) => {
					const th = document.createElement('th')
					th.textContent = name
					if (name === 'metadata') {
						th.classList.add('farm-intelligence-platform-farms__cell--metadata')
					}
					headerRow.appendChild(th)
				})
				if (hasActions) {
					const th = document.createElement('th')
					th.textContent = 'Actions'
					headerRow.appendChild(th)
				}
				thead.appendChild(headerRow)
				table.appendChild(thead)
				const tbody = document.createElement('tbody')
				items.forEach((item) => {
					const row = document.createElement('tr')
					columns.forEach((name, index) => {
						const cell = document.createElement('td')
						const value = item?.[name]
						const text = formatObservationValue(value, name)
						cell.textContent = text
						if (index === metadataColumn) {
							cell.classList.add('farm-intelligence-platform-farms__cell--metadata')
							cell.title = text === '—' ? '' : text
						}
						row.appendChild(cell)
					})
					if (hasActions) {
						const actions = document.createElement('td')
						const editButton = document.createElement('button')
						editButton.type = 'button'
						editButton.className = 'button'
						editButton.textContent = 'Edit'
						const deleteButton = document.createElement('button')
						deleteButton.type = 'button'
						deleteButton.className = 'button'
						deleteButton.textContent = 'Delete'
						actions.appendChild(editButton)
						actions.appendChild(deleteButton)
						row.appendChild(actions)
						const observationId = item?.id ?? null
						if (observationId === null) {
							editButton.disabled = true
							deleteButton.disabled = true
						} else {
							editButton.addEventListener('click', () => openObservationModal('edit', item))
							deleteButton.addEventListener('click', () => deleteObservation(String(observationId)))
						}
					}
					tbody.appendChild(row)
				})
				table.appendChild(tbody)
				farmsObservationsTable.replaceChildren()
				farmsObservationsTable.appendChild(table)
			}

			const setObservationsPagination = (count) => {
				if (!farmsObservationsPagination || !farmsObservationsPrev || !farmsObservationsNext || !farmsObservationsPage) {
					return
				}
				farmsObservationsPagination.hidden = false
				farmsObservationsPrev.disabled = observationsOffset <= 0 || !schemaReady
				const hasNext = Number.isFinite(count) ? count >= observationsLimit : false
				farmsObservationsNext.disabled = !hasNext || !schemaReady
				const start = observationsOffset + 1
				const end = observationsOffset + count
				farmsObservationsPage.textContent = count === 0
					? 'No observations'
					: `${start}-${end}`
			}

			const loadObservations = async () => {
				const schemaOk = await getSchemaReady('farm observations')
				logFarms('observations schema gate', { ok: schemaOk })
				if (!schemaOk) {
					return
				}
				clearFarmsNotes()
				clearObservationsError()
				if (!selectedFarm) {
					showObservationsError('Select a farm first.')
					return
				}
				if (!farmObservationsUrl) {
					showObservationsError('Farm observations endpoint is not available.')
					return
				}
				const url = farmObservationsUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
				const limitValue = Number.parseInt(observationsLimitInput?.value || '', 10)
				if (Number.isFinite(limitValue) && limitValue > 0) {
					observationsLimit = Math.min(limitValue, 500)
				}
				const result = await performJsonRequest('GET', url, {
					query: {
						start: observationsStartInput?.value || undefined,
						end: observationsEndInput?.value || undefined,
						event_type: observationsTypeInput?.value || undefined,
						limit: observationsLimit,
						offset: observationsOffset,
					},
				})
				if (!result.parsed) {
					showObservationsError('Unable to parse observations response.')
					return
				}
				const ok = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
				if (!ok) {
					const message = pickMessage(result.data, 'Unable to load observations.')
					showObservationsError(message)
					return
				}
				const payload = unwrapResponseData(result.data)
				const normalized = payload && payload.data !== undefined
					? payload.data
					: payload
				const items = Array.isArray(normalized)
					? normalized
					: Array.isArray(normalized?.results)
						? normalized.results
						: []
				renderObservationsTable(items)
				setObservationsPagination(items.length)
			}

			const openObservationsPanel = (farmId, farm) => {
				selectedFarm = { id: farmId, data: farm }
				observationsOffset = 0
				currentObservationId = null
				if (observationsLimitInput && !observationsLimitInput.value) {
					observationsLimitInput.value = String(observationsLimit)
				}
				if (farmsObservations) {
					farmsObservations.hidden = false
				}
				if (farmsNdvi) {
					farmsNdvi.hidden = true
				}
				if (farmsWeather) {
					farmsWeather.hidden = true
				}
				if (farmsActivities) {
					farmsActivities.hidden = true
				}
				if (farmsObservationsTitle) {
					const label = farm?.name ? `${farm.name} (#${farmId})` : `Farm #${farmId}`
					farmsObservationsTitle.textContent = label
				}
				loadObservations()
			}

			const setInputValue = (input, value) => {
				if (!input) {
					return
				}
				input.value = value === null || value === undefined ? '' : String(value)
			}

			const formatObservationValue = (value, key = '') => {
				if (value === null || value === undefined) {
					return '—'
				}
				if (key === 'metadata') {
					let obj = value
					if (typeof value === 'string') {
						try {
							obj = JSON.parse(value)
						} catch {
							return value
						}
					}
					if (obj && typeof obj === 'object' && !Array.isArray(obj)) {
						const parts = Object.entries(obj)
							.map(([k, v]) => `${k}=${v}`)
							.filter((item) => item.length > 1)
						return parts.length === 0 ? '—' : parts.join(', ')
					}
				}
				if (typeof value === 'object') {
					return JSON.stringify(value)
				}
				return String(value)
			}

			const readString = (input) => {
				const value = input?.value ? input.value.trim() : ''
				return value === '' ? null : value
			}

			const readNumber = (input) => {
				if (!input?.value) {
					return null
				}
				const parsed = Number.parseFloat(input.value)
				return Number.isFinite(parsed) ? parsed : null
			}

			const resetObservationMetadataInputs = () => {
				setInputValue(observationSource, '')
				setInputValue(observationObserver, '')
				setInputValue(observationCrop, '')
				setInputValue(observationVariety, '')
				setInputValue(observationGrowthStage, '')
				setInputValue(observationAreaHa, '')
				setInputValue(observationLocationNote, '')
				setInputValue(observationSeedRate, '')
				setInputValue(observationPlantingMethod, '')
				setInputValue(observationIrrigationType, '')
				setInputValue(observationWaterMm, '')
				setInputValue(observationFertilizerType, '')
				setInputValue(observationNutrientN, '')
				setInputValue(observationNutrientP, '')
				setInputValue(observationNutrientK, '')
				setInputValue(observationPest, '')
				setInputValue(observationProduct, '')
				setInputValue(observationDose, '')
				setInputValue(observationYield, '')
				setInputValue(observationMoisture, '')
				setInputValue(observationPestPressure, '')
				setInputValue(observationSoilPh, '')
				setInputValue(observationOrganicMatter, '')
			}

			const applyObservationMetadata = (metadata) => {
				let data = metadata
				if (typeof metadata === 'string') {
					try {
						data = JSON.parse(metadata)
					} catch {
						return
					}
				}
				if (!data || typeof data !== 'object' || Array.isArray(data)) {
					return
				}
				setInputValue(observationSource, data.source)
				setInputValue(observationObserver, data.observer)
				setInputValue(observationCrop, data.crop)
				setInputValue(observationVariety, data.variety)
				setInputValue(observationGrowthStage, data.growth_stage)
				setInputValue(observationAreaHa, data.area_ha)
				setInputValue(observationLocationNote, data.location_note)
				setInputValue(observationSeedRate, data.seed_rate_kg_ha)
				setInputValue(observationPlantingMethod, data.planting_method)
				setInputValue(observationIrrigationType, data.irrigation_type)
				setInputValue(observationWaterMm, data.water_mm)
				setInputValue(observationFertilizerType, data.fertilizer_type)
				setInputValue(observationNutrientN, data.nutrient_n_kg_ha)
				setInputValue(observationNutrientP, data.nutrient_p_kg_ha)
				setInputValue(observationNutrientK, data.nutrient_k_kg_ha)
				setInputValue(observationPest, data.pest)
				setInputValue(observationProduct, data.product)
				setInputValue(observationDose, data.dose_ml_ha)
				setInputValue(observationYield, data.yield_kg)
				setInputValue(observationMoisture, data.moisture_percent)
				setInputValue(observationPestPressure, data.pest_pressure)
				setInputValue(observationSoilPh, data.soil_ph)
				setInputValue(observationOrganicMatter, data.organic_matter_percent)
			}

			const collectObservationMetadata = () => {
				const metadata = {}
				const assignIfPresent = (key, value) => {
					if (value === null || value === undefined || value === '') {
						return
					}
					metadata[key] = value
				}
				assignIfPresent('source', readString(observationSource))
				assignIfPresent('observer', readString(observationObserver))
				assignIfPresent('crop', readString(observationCrop))
				assignIfPresent('variety', readString(observationVariety))
				assignIfPresent('growth_stage', readString(observationGrowthStage))
				assignIfPresent('area_ha', readNumber(observationAreaHa))
				assignIfPresent('location_note', readString(observationLocationNote))
				assignIfPresent('seed_rate_kg_ha', readNumber(observationSeedRate))
				assignIfPresent('planting_method', readString(observationPlantingMethod))
				assignIfPresent('irrigation_type', readString(observationIrrigationType))
				assignIfPresent('water_mm', readNumber(observationWaterMm))
				assignIfPresent('fertilizer_type', readString(observationFertilizerType))
				assignIfPresent('nutrient_n_kg_ha', readNumber(observationNutrientN))
				assignIfPresent('nutrient_p_kg_ha', readNumber(observationNutrientP))
				assignIfPresent('nutrient_k_kg_ha', readNumber(observationNutrientK))
				assignIfPresent('pest', readString(observationPest))
				assignIfPresent('product', readString(observationProduct))
				assignIfPresent('dose_ml_ha', readNumber(observationDose))
				assignIfPresent('yield_kg', readNumber(observationYield))
				assignIfPresent('moisture_percent', readNumber(observationMoisture))
				assignIfPresent('pest_pressure', readString(observationPestPressure))
				assignIfPresent('soil_ph', readNumber(observationSoilPh))
				assignIfPresent('organic_matter_percent', readNumber(observationOrganicMatter))
				return Object.keys(metadata).length === 0 ? null : metadata
			}

			const updateObservationEventGroups = () => {
				if (!observationFieldGroups || observationFieldGroups.length === 0) {
					return
				}
				const eventType = observationEventType?.value ? observationEventType.value.trim() : ''
				observationFieldGroups.forEach((group) => {
					const types = group.dataset.eventTypes
						? group.dataset.eventTypes.split(',').map((item) => item.trim()).filter(Boolean)
						: []
					group.hidden = eventType === '' || !types.includes(eventType)
				})
			}

			const openObservationModal = (mode, observation = null) => {
				currentObservationId = observation?.id ?? null
				if (observationsModalTitle) {
					observationsModalTitle.textContent = mode === 'edit' ? 'Edit observation' : 'New observation'
				}
				if (observationObservedAt) {
					observationObservedAt.value = observation?.observed_at ?? ''
				}
				if (observationEventType) {
					observationEventType.value = observation?.event_type ?? ''
				}
				if (observationNote) {
					observationNote.value = observation?.note ?? ''
				}
				resetObservationMetadataInputs()
				applyObservationMetadata(observation?.metadata)
				updateObservationEventGroups()
				if (observationsModal) {
					observationsModal.hidden = false
				}
			}

			const closeObservationModal = () => {
				currentObservationId = null
				if (observationsModal) {
					observationsModal.hidden = true
				}
			}

			const buildObservationPayload = () => {
				const payload = {}
				if (!observationObservedAt?.value) {
					toast('Observed at is required.')
					return null
				}
				if (!observationEventType?.value) {
					toast('Event type is required.')
					return null
				}
				if (observationObservedAt?.value) {
					payload.observed_at = observationObservedAt.value
				}
				if (observationEventType?.value) {
					payload.event_type = observationEventType.value
				}
				if (observationNote?.value) {
					payload.note = observationNote.value
				}
				const metadata = collectObservationMetadata()
				if (metadata !== null) {
					payload.metadata = metadata
				}
				return payload
			}

			const saveObservation = async () => {
				clearObservationsError()
				if (!selectedFarm) {
					showObservationsError('Select a farm first.')
					return
				}
				if (!farmObservationsUrl || !farmObservationUrl) {
					showObservationsError('Farm observations endpoint is not available.')
					return
				}
				const payload = buildObservationPayload()
				if (!payload) {
					return
				}
				const isEdit = currentObservationId !== null
				const url = isEdit
					? farmObservationUrl
						.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
						.replace('__OBSERVATION_ID__', encodeURIComponent(currentObservationId))
					: farmObservationsUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
				const method = isEdit ? 'PATCH' : 'POST'
				const result = await performJsonRequest(method, url, { body: payload })
				if (!result.parsed) {
					showObservationsError('Unable to parse observation response.')
					return
				}
				const ok = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
				if (!ok) {
					const message = pickMessage(result.data, 'Unable to save observation.')
					showObservationsError(message)
					toast(message)
					return
				}
				toast(pickMessage(result.data, 'Observation saved.'))
				closeObservationModal()
				await loadObservations()
			}

			const deleteObservation = async (observationId) => {
				clearObservationsError()
				if (!selectedFarm) {
					showObservationsError('Select a farm first.')
					return
				}
				if (!farmObservationUrl) {
					showObservationsError('Farm observations endpoint is not available.')
					return
				}
				const confirmed = await confirmDeleteAsync()
				if (!confirmed) {
					return
				}
				const url = farmObservationUrl
					.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					.replace('__OBSERVATION_ID__', encodeURIComponent(observationId))
				const result = await performJsonRequest('DELETE', url)
				if (!result.parsed) {
					showObservationsError('Unable to parse delete response.')
					return
				}
				const ok = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
				if (!ok) {
					const message = pickMessage(result.data, 'Unable to delete observation.')
					showObservationsError(message)
					toast(message)
					return
				}
				toast(pickMessage(result.data, 'Observation deleted.'))
				await loadObservations()
			}

			let activitiesOffset = 0
			let currentActivityId = null
			let activityModalMode = 'create'
			let activityModalInitial = {}
			let activityCreateFields = {}
			let activityUpdateFields = {}
			let activityAllFields = {}
			let activitySchemaLoaded = false
			const activitiesLimit = 100

			const clearActivitiesError = () => {
				if (farmsActivitiesError) {
					farmsActivitiesError.hidden = true
					farmsActivitiesError.textContent = ''
				}
			}

			const showActivitiesError = (message) => {
				if (!farmsActivitiesError) {
					return
				}
				farmsActivitiesError.textContent = message
				farmsActivitiesError.hidden = false
			}

			const renderActivitiesTable = (items) => {
				if (!farmsActivitiesTable) {
					return
				}
				farmsActivitiesTable.innerHTML = ''
				if (!Array.isArray(items) || items.length === 0) {
					const p = document.createElement('p')
					p.textContent = 'No activities found.'
					farmsActivitiesTable.appendChild(p)
					return
				}
				const table = document.createElement('table')
				table.className = 'farm-intelligence-platform-farms__table farm-intelligence-platform-farms__table--sticky'
				const thead = document.createElement('thead')
				const headerRow = document.createElement('tr')
				const columns = Object.keys(items[0] || {})
				columns.forEach((name) => {
					const th = document.createElement('th')
					th.textContent = name
					headerRow.appendChild(th)
				})
				const actionsTh = document.createElement('th')
				actionsTh.textContent = 'Actions'
				headerRow.appendChild(actionsTh)
				thead.appendChild(headerRow)
				table.appendChild(thead)
				const tbody = document.createElement('tbody')
				items.forEach((item) => {
					const row = document.createElement('tr')
					columns.forEach((name) => {
						const td = document.createElement('td')
						td.textContent = formatActivityValue(item?.[name], name)
						row.appendChild(td)
					})
					const actionsTd = document.createElement('td')
					const editBtn = document.createElement('button')
					editBtn.type = 'button'
					editBtn.className = 'button'
					editBtn.textContent = 'Edit'
					editBtn.addEventListener('click', () => openActivityModal('edit', item))
					const deleteBtn = document.createElement('button')
					deleteBtn.type = 'button'
					deleteBtn.className = 'button'
					deleteBtn.textContent = 'Delete'
					deleteBtn.addEventListener('click', () => deleteActivity(item?.id))
					actionsTd.appendChild(editBtn)
					actionsTd.appendChild(deleteBtn)
					row.appendChild(actionsTd)
					tbody.appendChild(row)
				})
				table.appendChild(tbody)
				farmsActivitiesTable.appendChild(table)
			}

			const setActivitiesPagination = (count) => {
				if (!farmsActivitiesPagination || !farmsActivitiesPage) {
					return
				}
				if (count <= 0) {
					farmsActivitiesPagination.hidden = true
					return
				}
				farmsActivitiesPagination.hidden = false
				farmsActivitiesPage.textContent = `Offset ${activitiesOffset} · ${count} items`
				if (farmsActivitiesPrev) {
					farmsActivitiesPrev.disabled = activitiesOffset <= 0
					farmsActivitiesPrev.onclick = () => {
						activitiesOffset = Math.max(0, activitiesOffset - activitiesLimit)
						loadActivities()
					}
				}
				if (farmsActivitiesNext) {
					farmsActivitiesNext.disabled = count < activitiesLimit
					farmsActivitiesNext.onclick = () => {
						activitiesOffset += activitiesLimit
						loadActivities()
					}
				}
			}

			const loadActivities = async () => {
				clearFarmsNotes()
				clearActivitiesError()
				if (!selectedFarm) {
					showActivitiesError('Select a farm first.')
					return
				}
				if (!farmActivitiesUrl) {
					showActivitiesError('Farm activities endpoint is not available.')
					return
				}
				const url = farmActivitiesUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
				const limitValue = Number.parseInt(activitiesLimitInput?.value || '', 10)
				const limit = Number.isFinite(limitValue) && limitValue > 0 ? Math.min(limitValue, 500) : activitiesLimit
				const result = await performJsonRequest('GET', url, {
					query: {
						status: activitiesStatusInput?.value || undefined,
						type: activitiesTypeFilterInput?.value || undefined,
						limit,
						offset: activitiesOffset,
					},
				})
				if (!result.parsed) {
					showActivitiesError('Unable to parse activities response.')
					return
				}
				const ok = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
				if (!ok) {
					const message = pickMessage(result.data, 'Unable to load activities.')
					showActivitiesError(message)
					return
				}
				const payload = unwrapResponseData(result.data)
				const normalized = payload && payload.data !== undefined ? payload.data : payload
				const items = Array.isArray(normalized)
					? normalized
					: Array.isArray(normalized?.results) ? normalized.results : []
				renderActivitiesTable(items)
				setActivitiesPagination(items.length)
			}

			const openActivitiesPanel = (farmId, farm) => {
				selectedFarm = { id: farmId, data: farm }
				activitiesOffset = 0
				currentActivityId = null
				if (activitiesLimitInput && !activitiesLimitInput.value) {
					activitiesLimitInput.value = String(activitiesLimit)
				}
				if (farmsActivities) {
					farmsActivities.hidden = false
				}
				if (farmsNdvi) {
					farmsNdvi.hidden = true
				}
				if (farmsWeather) {
					farmsWeather.hidden = true
				}
				if (farmsObservations) {
					farmsObservations.hidden = true
				}
				if (farmsActivitiesTitle) {
					const label = farm?.name ? `${farm.name} (#${farmId})` : `Farm #${farmId}`
					farmsActivitiesTitle.textContent = label
				}
				loadActivities()
			}

			const formatActivityValue = (value, key = '') => {
				if (value === null || value === undefined) {
					return '—'
				}
				if (typeof value === 'object') {
					try {
						return JSON.stringify(value)
					} catch {
						return String(value)
					}
				}
				return String(value)
			}

			const closeActivityModal = () => {
				if (activitiesModal) {
					activitiesModal.hidden = true
				}
				currentActivityId = null
				activityModalInitial = {}
			}

			const loadActivitySchema = async () => {
				if (activitySchemaLoaded) {
					return true
				}
				const schemaOk = await getSchemaReady('farm activities')
				if (!schemaOk) {
					activitySchemaLoaded = true
					return true
				}
				try {
					const opCreate = await resolveOperation('activities_create')
					const opUpdate = await resolveOperation('activities_update')
					activityCreateFields = opCreate?.bodyFields || {}
					activityUpdateFields = opUpdate?.bodyFields || {}
					if (Object.keys(activityUpdateFields).length === 0) {
						activityUpdateFields = activityCreateFields
					}
					const allFields = { ...activityCreateFields, ...activityUpdateFields }
					activityAllFields = allFields
				} catch (e) {
					logFarms('activity schema load failed, using empty fields', { error: e.message })
					activityCreateFields = {}
					activityUpdateFields = {}
					activityAllFields = {}
				}
				activitySchemaLoaded = true
				return true
			}

			const resolveActivityModalFields = (mode) => {
				const preferred = mode === 'edit' ? activityUpdateFields : activityCreateFields
				if (preferred && typeof preferred === 'object' && Object.keys(preferred).length > 0) {
					return preferred
				}
				if (activityAllFields && typeof activityAllFields === 'object' && Object.keys(activityAllFields).length > 0) {
					return activityAllFields
				}
				return {
					type: { type: 'string', required: true, enum: ['vaccination', 'fertilizer', 'irrigation', 'ndvi_trigger'] },
					scheduled_at: { type: 'string', format: 'date-time', required: true },
					recurrence_type: { type: 'string', enum: ['none', 'interval', 'cron'] },
					interval_days: { type: 'integer' },
					metadata: { type: 'object' },
				}
			}

			const renderActivityModalField = (name, def, existing) => {
				if (!activitiesModalFields) return
				if (def?.readOnly) return

				const row = document.createElement('div')
				row.className = 'farm-intelligence-platform-farms__field'

				const label = document.createElement('label')
				label.textContent = `${name}${def?.required ? ' *' : ''}`
				row.appendChild(label)

				let input = null

				if (name === 'type' && def?.enum) {
					input = document.createElement('select')
					const defaultOpt = document.createElement('option')
					defaultOpt.value = ''
					defaultOpt.textContent = 'Select type'
					defaultOpt.selected = true
					input.appendChild(defaultOpt)
					const typeLabels = { vaccination: 'Vaccination', fertilizer: 'Fertilizer', irrigation: 'Irrigation', ndvi_trigger: 'NDVI Trigger' }
					def.enum.forEach((enumVal) => {
						const option = document.createElement('option')
						option.value = enumVal
						option.textContent = typeLabels[enumVal] || enumVal
						if (existing?.[name] === enumVal) option.selected = true
						input.appendChild(option)
					})
				} else if (name === 'recurrence_type' && def?.enum) {
					input = document.createElement('select')
					const defaultOpt = document.createElement('option')
					defaultOpt.value = ''
					defaultOpt.textContent = 'Select recurrence'
					defaultOpt.selected = true
					input.appendChild(defaultOpt)
					const recLabels = { none: 'One-time', interval: 'Interval', cron: 'Cron (future)' }
					def.enum.forEach((enumVal) => {
						const option = document.createElement('option')
						option.value = enumVal
						option.textContent = recLabels[enumVal] || enumVal
						if (existing?.[name] === enumVal) option.selected = true
						input.appendChild(option)
					})
				} else if (def?.enum) {
					input = document.createElement('select')
					const defaultOpt = document.createElement('option')
					defaultOpt.value = ''
					defaultOpt.textContent = `Select ${name}`
					defaultOpt.selected = true
					input.appendChild(defaultOpt)
					def.enum.forEach((enumVal) => {
						const option = document.createElement('option')
						option.value = enumVal
						option.textContent = enumVal
						if (existing?.[name] === enumVal) option.selected = true
						input.appendChild(option)
					})
				} else if (def?.type === 'boolean') {
					input = document.createElement('input')
					input.type = 'checkbox'
					input.checked = Boolean(existing?.[name])
				} else if (def?.type === 'integer' || def?.type === 'number') {
					input = document.createElement('input')
					input.type = 'number'
					input.step = def?.type === 'integer' ? '1' : 'any'
					if (existing?.[name] !== undefined && existing?.[name] !== null) {
						input.value = String(existing[name])
					}
				} else if (def?.format === 'date-time') {
					input = document.createElement('input')
					input.type = 'datetime-local'
					if (existing?.[name]) {
						const dt = new Date(existing[name])
						if (!Number.isNaN(dt.getTime())) {
							const offset = dt.getTimezoneOffset()
							const localDt = new Date(dt.getTime() - offset * 60000)
							input.value = localDt.toISOString().slice(0, 16)
						}
					}
				} else if (name === 'metadata' || def?.type === 'object') {
					input = document.createElement('textarea')
					input.rows = 4
					if (existing?.[name] !== undefined && existing?.[name] !== null) {
						try {
							input.value = JSON.stringify(existing[name], null, 2)
						} catch {
							input.value = String(existing[name])
						}
					}
				} else {
					input = document.createElement('input')
					input.type = 'text'
					if (existing?.[name] !== undefined && existing?.[name] !== null) {
						input.value = String(existing[name])
					}
				}

				if (input) {
					input.dataset.fieldName = name
					if (def?.required) input.required = true
					row.appendChild(input)
				}
				activitiesModalFields.appendChild(row)
			}

			const openActivityModal = async (mode, activity = null) => {
				clearActivitiesError()
				await loadActivitySchema()

				activityModalMode = mode
				activityModalInitial = {}
				let existing = {}

				if (mode === 'edit' && activity?.id) {
					currentActivityId = activity.id
					const url = farmActivityUrl
						.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
						.replace('__ACTIVITY_ID__', encodeURIComponent(activity.id))
					const result = await performJsonRequest('GET', url)
					if (!result.parsed) {
						showActivitiesError('Unable to parse activity response.')
						return
					}
					const respOk = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
					if (!respOk) {
						const message = pickMessage(result.data, 'Unable to load activity.')
						showActivitiesError(message)
						return
					}
					existing = unwrapResponseData(result.data) ?? {}
					activityModalInitial = existing
				} else {
					currentActivityId = null
				}

				if (activitiesModalTitle) {
					activitiesModalTitle.textContent = mode === 'edit' ? 'Edit activity' : 'New activity'
				}
				if (activitiesModalFields) {
					activitiesModalFields.innerHTML = ''
					const fieldSet = resolveActivityModalFields(mode)
					const entries = Object.entries(fieldSet).filter(([, def]) => !def?.readOnly)
					if (entries.length === 0) {
						showActivitiesError('Activity schema did not return any writable fields.')
						return
					}
					entries.forEach(([name, def]) => {
						renderActivityModalField(name, def, existing)
					})
				}
				if (activitiesModal) {
					activitiesModal.hidden = false
				}
			}

			const collectActivityPayload = () => {
				const payload = {}
				if (!activitiesModalFields) return payload
				const fieldSet = resolveActivityModalFields(activityModalMode)
				const entries = Object.entries(fieldSet).filter(([, def]) => !def?.readOnly)

				for (const [name, def] of entries) {
					const input = activitiesModalFields.querySelector(`[data-field-name="${name}"]`)
					if (!input) continue

					let value = null
					if (name === 'metadata' || def?.type === 'object') {
						const rawValue = input.value.trim()
						if (rawValue) {
							try {
								value = JSON.parse(rawValue)
							} catch {
								value = rawValue
							}
						}
					} else if (def?.type === 'boolean') {
						value = Boolean(input.checked)
					} else if (def?.type === 'integer' || def?.type === 'number') {
						value = input.value === '' ? null : Number(input.value)
					} else {
						value = input.value !== undefined ? String(input.value).trim() : ''
					}

					if (activityModalMode === 'create') {
						if (def?.required && (value === null || value === '')) {
							showActivitiesError(`Missing required field: ${name}`)
							return null
						}
						if (value !== null && value !== '') {
							payload[name] = value
						}
					} else {
						const initial = activityModalInitial?.[name]
						const normalizedInitial = def?.type === 'boolean'
							? Boolean(initial)
							: def?.type === 'integer' || def?.type === 'number'
								? (initial !== null && initial !== undefined ? Number(initial) : null)
								: initial !== null && initial !== undefined ? String(initial) : ''
						if (value !== normalizedInitial) {
							payload[name] = value
						}
					}
				}
				return payload
			}

			const saveActivity = async () => {
				clearActivitiesError()
				const payload = collectActivityPayload()
				if (payload === null) return
				if (!selectedFarm) {
					showActivitiesError('Select a farm first.')
					return
				}
				const isEdit = currentActivityId !== null
				if (!isEdit) {
					payload.farm = selectedFarm.id
				}
				const url = isEdit
					? farmActivityUrl
						.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
						.replace('__ACTIVITY_ID__', encodeURIComponent(currentActivityId))
					: farmActivitiesUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
				const method = isEdit ? 'PATCH' : 'POST'
				const result = await performJsonRequest(method, url, { body: payload })
				if (!result.parsed) {
					showActivitiesError('Unable to parse response.')
					return
				}
				const ok = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
				if (!ok) {
					const message = pickMessage(result.data, isEdit ? 'Unable to update activity.' : 'Unable to create activity.')
					showActivitiesError(message)
					toast(message)
					return
				}
				toast(pickMessage(result.data, 'Activity saved.'))
				closeActivityModal()
				await loadActivities()
			}

			const deleteActivity = async (activityId) => {
				clearActivitiesError()
				if (!selectedFarm) {
					showActivitiesError('Select a farm first.')
					return
				}
				if (!farmActivityUrl) {
					showActivitiesError('Farm activities endpoint is not available.')
					return
				}
				const confirmed = await confirmDeleteAsync()
				if (!confirmed) {
					return
				}
				const url = farmActivityUrl
					.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					.replace('__ACTIVITY_ID__', encodeURIComponent(activityId))
				const result = await performJsonRequest('DELETE', url)
				if (!result.parsed) {
					showActivitiesError('Unable to parse delete response.')
					return
				}
				const ok = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
				if (!ok) {
					const message = pickMessage(result.data, 'Unable to delete activity.')
					showActivitiesError(message)
					toast(message)
					return
				}
				toast(pickMessage(result.data, 'Activity deleted.'))
				await loadActivities()
			}

			const formatWeatherNumber = (value, digits = 1) => formatNdviNumber(value, digits)

			const parseWeatherDateInfo = (value) => {
				const raw = value ? String(value).trim() : ''
				if (!raw) {
					return null
				}
				if (ISO_DATE_PATTERN.test(raw)) {
					const parsed = parseIsoDate(raw)
					if (parsed.date && !parsed.invalid) {
						return { date: parsed.date, useUtc: true }
					}
					return null
				}
				const date = new Date(raw)
				if (Number.isNaN(date.getTime())) {
					return null
				}
				return { date, useUtc: false }
			}

			const formatWeatherTimeOnly = (value) => {
				const raw = value ? String(value).trim() : ''
				if (!raw) {
					return '-'
				}
				if (ISO_DATE_PATTERN.test(raw)) {
					return '00:00'
				}
				const info = parseWeatherDateInfo(raw)
				if (!info) {
					return raw
				}
				return formatTime(info.date)
			}

			const formatWeatherDateTime = (value) => {
				const raw = value ? String(value).trim() : ''
				if (!raw) {
					return '-'
				}
				const info = parseWeatherDateInfo(raw)
				if (!info) {
					return raw
				}
				if (info.useUtc) {
					return formatWeekdayDateLabel(info.date, { useUtc: true })
				}
				return formatWeekdayDateTimeLabel(info.date)
			}

			const formatWeatherDate = (value) => {
				const raw = value ? String(value).trim() : ''
				if (!raw) {
					return '-'
				}
				const info = parseWeatherDateInfo(raw)
				if (!info) {
					return raw
				}
				return formatWeekdayDateLabel(info.date, { useUtc: info.useUtc })
			}

			const renderWeatherCurrent = (payload) => {
				if (!weatherCurrentGrid) {
					return
				}
				const metrics = [
					{ label: 'Observed', value: formatWeatherDateTime(payload?.observed_at) },
					{ label: 'Temperature (C)', value: formatWeatherNumber(payload?.temperature_c) },
					{ label: 'Wind (m/s)', value: formatWeatherNumber(payload?.wind_speed_mps) },
					{ label: 'Source', value: payload?.source ? String(payload.source) : '-' },
				]
				weatherCurrentGrid.innerHTML = ''
				metrics.forEach((metric) => {
					const card = document.createElement('div')
					card.className = 'farm-intelligence-platform-farms__weather-card'
					const label = document.createElement('span')
					label.className = 'farm-intelligence-platform-farms__weather-card-label'
					label.textContent = metric.label
					const value = document.createElement('strong')
					value.className = 'farm-intelligence-platform-farms__weather-card-value'
					value.textContent = metric.value
					card.appendChild(label)
					card.appendChild(value)
					weatherCurrentGrid.appendChild(card)
				})
			}

			const renderWeatherTable = (target, rows, columns, emptyMessage) => {
				if (!target) {
					return
				}
				if (!Array.isArray(rows) || rows.length === 0) {
					target.textContent = emptyMessage
					return
				}
				const table = document.createElement('table')
				table.className = 'farm-intelligence-platform-farms__table farm-intelligence-platform-farms__table--sticky'
				const thead = document.createElement('thead')
				const headerRow = document.createElement('tr')
				const columnClasses = columns.map((column) => {
					const key = String(column.key ?? '')
					if (/timestamp|date|day/i.test(key)) {
						return 'is-date'
					}
					return 'is-number'
				})
				columns.forEach((column, index) => {
					const th = document.createElement('th')
					th.textContent = column.label
					if (columnClasses[index]) {
						th.classList.add(columnClasses[index])
					}
					headerRow.appendChild(th)
				})
				thead.appendChild(headerRow)
				table.appendChild(thead)
				const tbody = document.createElement('tbody')
				rows.forEach((row) => {
					const tr = document.createElement('tr')
					columns.forEach((column, index) => {
						const td = document.createElement('td')
						const raw = row?.[column.key]
						td.textContent = column.format ? column.format(raw) : String(raw ?? '-')
						if (columnClasses[index]) {
							td.classList.add(columnClasses[index])
						}
						tr.appendChild(td)
					})
					tbody.appendChild(tr)
				})
				table.appendChild(tbody)
				target.innerHTML = ''
				target.appendChild(table)
			}

			const renderWeatherHourly = (payload) => {
				if (!weatherHourlyTable) {
					return
				}
				const rows = Array.isArray(payload?.hours) ? payload.hours : []
				if (rows.length === 0) {
					weatherHourlyTable.textContent = 'No hourly data.'
					return
				}
				const columns = [
					{ key: 'timestamp', label: 'Time', format: formatWeatherTimeOnly },
					{ key: 'temperature_c', label: 'Temp (C)', format: formatWeatherNumber },
					{ key: 'precipitation_mm', label: 'Rain (mm)', format: formatWeatherNumber },
					{ key: 'wind_speed_mps', label: 'Wind (m/s)', format: formatWeatherNumber },
					{ key: 'cloud_cover_pct', label: 'Clouds (%)', format: formatWeatherNumber },
				]
				const columnClasses = columns.map((column) => {
					const key = String(column.key ?? '')
					if (/timestamp|date|day/i.test(key)) {
						return 'is-date'
					}
					return 'is-number'
				})
				const groups = []
				const groupIndex = new Map()
				rows.forEach((row) => {
					const info = parseWeatherDateInfo(row?.timestamp)
					if (!info) {
						const fallbackKey = 'unknown'
						let group = groupIndex.get(fallbackKey)
						if (!group) {
							group = { key: fallbackKey, label: 'Unknown date', rows: [] }
							groupIndex.set(fallbackKey, group)
							groups.push(group)
						}
						group.rows.push(row)
						return
					}
					const key = info.useUtc
						? formatDateParts(
							info.date.getUTCFullYear(),
							info.date.getUTCMonth() + 1,
							info.date.getUTCDate(),
						)
						: formatDateParts(
							info.date.getFullYear(),
							info.date.getMonth() + 1,
							info.date.getDate(),
						)
					let group = groupIndex.get(key)
					if (!group) {
						group = {
							key,
							label: formatWeekdayDateLabel(info.date, { useUtc: info.useUtc }),
							rows: [],
						}
						groupIndex.set(key, group)
						groups.push(group)
					}
					group.rows.push(row)
				})
				const table = document.createElement('table')
				table.className = 'farm-intelligence-platform-farms__table farm-intelligence-platform-farms__table--sticky'
				const thead = document.createElement('thead')
				const headerRow = document.createElement('tr')
				columns.forEach((column, index) => {
					const th = document.createElement('th')
					th.textContent = column.label
					if (columnClasses[index]) {
						th.classList.add(columnClasses[index])
					}
					headerRow.appendChild(th)
				})
				thead.appendChild(headerRow)
				table.appendChild(thead)
				const tbody = document.createElement('tbody')
				groups.forEach((group) => {
					const groupRow = document.createElement('tr')
					groupRow.className = 'farm-intelligence-platform-farms__group-row'
					const groupCell = document.createElement('td')
					groupCell.className = 'farm-intelligence-platform-farms__group-cell'
					groupCell.colSpan = columns.length
					groupCell.textContent = group.label
					groupRow.appendChild(groupCell)
					tbody.appendChild(groupRow)
					group.rows.forEach((row) => {
						const tr = document.createElement('tr')
						columns.forEach((column, index) => {
							const td = document.createElement('td')
							const raw = row?.[column.key]
							td.textContent = column.format ? column.format(raw) : String(raw ?? '-')
							if (columnClasses[index]) {
								td.classList.add(columnClasses[index])
							}
							tr.appendChild(td)
						})
						tbody.appendChild(tr)
					})
				})
				table.appendChild(tbody)
				weatherHourlyTable.innerHTML = ''
				weatherHourlyTable.appendChild(table)
			}

			const renderWeatherDaily = (payload) => {
				const rows = Array.isArray(payload?.forecasts) ? payload.forecasts : []
				renderWeatherTable(
					weatherDailyTable,
					rows,
					[
						{ key: 'day', label: 'Date', format: formatWeatherDate },
						{ key: 't_min_c', label: 'Min (C)', format: formatWeatherNumber },
						{ key: 't_max_c', label: 'Max (C)', format: formatWeatherNumber },
						{ key: 'precipitation_mm', label: 'Rain (mm)', format: formatWeatherNumber },
						{ key: 'wind_speed_max_mps', label: 'Wind max (m/s)', format: formatWeatherNumber },
					],
					'No daily data.',
				)
			}

			const runWeatherRequest = async (label, urlTemplate, options = {}) => {
				const schemaOk = await getSchemaReady(`weather ${label}`)
				logFarms(`weather ${label} schema gate`, { ok: schemaOk })
				if (!schemaOk) {
					showWeatherError('Weather schema is not available.')
					return null
				}
				clearFarmsNotes()
				clearWeatherError()
				if (!selectedFarm) {
					showWeatherError('Select a farm first.')
					return null
				}
				if (!urlTemplate) {
					showWeatherError('Weather endpoint is not available.')
					return null
				}
				const url = urlTemplate.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
				setWeatherLoading(true)
				const result = await performJsonRequest('GET', url, options)
				setWeatherLoading(false)

				const responseOk = Boolean(result.response?.ok)
				const snippet = (result.text || '').trim().slice(0, 200)
				const fallbackMessage = `Unable to load ${label}.`

				if (!responseOk) {
					const code = result.data?.code
					const message = code === 'upstream_error'
						? 'Upstream weather service error.'
						: snippet || pickMessage(result.data, fallbackMessage)
					showWeatherError(message)
					return null
				}
				if (!result.parsed) {
					showWeatherError(snippet || 'Unable to parse weather response.')
					return null
				}
				const weatherPayload = unwrapResponseData(result.data)
				if (weatherPayload?.status !== 0) {
					showWeatherError(pickMessage(weatherPayload, fallbackMessage))
					return null
				}
				return weatherPayload?.data ?? weatherPayload
			}

			const openWeatherPanel = (farmId, farm) => {
				selectedFarm = { id: farmId, data: farm }
				weatherCache = { current: null, hourly: null, daily: null }
				if (farmsWeather) {
					farmsWeather.hidden = false
				}
				if (farmsNdvi) {
					farmsNdvi.hidden = true
				}
				if (farmsObservations) {
					farmsObservations.hidden = true
				}
				if (farmsActivities) {
					farmsActivities.hidden = true
				}
				if (farmsWeatherTitle) {
					const label = farm?.name ? `${farm.name} (#${farmId})` : `Farm #${farmId}`
					farmsWeatherTitle.textContent = label
				}
				if (weatherHourlyTable) {
					weatherHourlyTable.textContent = ''
				}
				if (weatherDailyTable) {
					weatherDailyTable.textContent = ''
				}
				clearWeatherError()
				setWeatherActiveTab('current')
				loadWeatherTab('current')
			}

			const loadWeatherTab = async (tab) => {
				setWeatherActiveTab(tab)
				if (weatherCache[tab]) {
					if (tab === 'current') {
						renderWeatherCurrent(weatherCache.current)
					}
					if (tab === 'hourly') {
						renderWeatherHourly(weatherCache.hourly)
					}
					if (tab === 'daily') {
						renderWeatherDaily(weatherCache.daily)
					}
					return
				}
				if (tab === 'current') {
					const data = await runWeatherRequest('current weather', farmWeatherCurrentUrl)
					if (data) {
						weatherCache.current = data
						renderWeatherCurrent(data)
					}
					return
				}
				if (tab === 'hourly') {
					const data = await runWeatherRequest('hourly weather', farmWeatherHourlyUrl, {
						query: { hours: DEFAULT_WEATHER_HOURS },
					})
					if (data) {
						weatherCache.hourly = data
						renderWeatherHourly(data)
					}
					return
				}
				if (tab === 'daily') {
					const data = await runWeatherRequest('daily weather', farmWeatherDailyUrl, {
						query: { days: DEFAULT_WEATHER_DAYS },
					})
					if (data) {
						weatherCache.daily = data
						renderWeatherDaily(data)
					}
				}
			}

			if (farmsRefresh) {
				farmsRefresh.addEventListener('click', async () => {
					logFarms('refresh clicked', { schemaReady })
					await refreshFarms()
				})
			}
			if (farmsCreate) {
				farmsCreate.addEventListener('click', async () => {
					logFarms('new farm clicked', { schemaReady })
					await openFarmModal('create')
				})
			}
			if (farmsPrev) {
				farmsPrev.addEventListener('click', () => {
					if (prevParams) refreshFarms(prevParams)
				})
			}
			if (farmsNext) {
				farmsNext.addEventListener('click', () => {
					if (nextParams) refreshFarms(nextParams)
				})
			}
			if (observationsRefresh) {
				observationsRefresh.addEventListener('click', () => {
					observationsOffset = 0
					loadObservations()
				})
			}
			if (observationsCreate) {
				observationsCreate.addEventListener('click', () => {
					openObservationModal('create')
				})
			}
			if (observationEventType) {
				observationEventType.addEventListener('change', updateObservationEventGroups)
			}
			if (observationsModalClose) {
				observationsModalClose.addEventListener('click', closeObservationModal)
			}
			if (observationsModalSave) {
				observationsModalSave.addEventListener('click', saveObservation)
			}
			if (farmsObservationsPrev) {
				farmsObservationsPrev.addEventListener('click', () => {
					if (observationsOffset <= 0) {
						return
					}
					observationsOffset = Math.max(0, observationsOffset - observationsLimit)
					loadObservations()
				})
			}
			if (farmsObservationsNext) {
				farmsObservationsNext.addEventListener('click', () => {
					observationsOffset += observationsLimit
					loadObservations()
				})
			}
			if (activitiesRefresh) {
				activitiesRefresh.addEventListener('click', () => {
					activitiesOffset = 0
					loadActivities()
				})
			}
			if (activitiesCreate) {
				activitiesCreate.addEventListener('click', () => {
					openActivityModal('create')
				})
			}
			if (activitiesModalClose) {
				activitiesModalClose.addEventListener('click', closeActivityModal)
			}
			if (activitiesModalSave) {
				activitiesModalSave.addEventListener('click', saveActivity)
			}
			if (farmsActivitiesPrev) {
				farmsActivitiesPrev.addEventListener('click', () => {
					if (activitiesOffset <= 0) {
						return
					}
					activitiesOffset = Math.max(0, activitiesOffset - activitiesLimit)
					loadActivities()
				})
			}
			if (farmsActivitiesNext) {
				farmsActivitiesNext.addEventListener('click', () => {
					activitiesOffset += activitiesLimit
					loadActivities()
				})
			}
			if (farmsModalClose) {
				farmsModalClose.addEventListener('click', closeFarmModal)
			}
			if (farmsSyncModalClose) {
				farmsSyncModalClose.addEventListener('click', closeSyncFarmModal)
			}
			if (farmsSyncModalCancel) {
				farmsSyncModalCancel.addEventListener('click', closeSyncFarmModal)
			}
			if (farmsSyncModalConfirm) {
				farmsSyncModalConfirm.addEventListener('click', () => {
					if (currentSyncFarmId) {
						syncFarm(currentSyncFarmId, currentSyncFarmData || {})
					}
				})
			}
			const loadLatestNdvi = async () => {
				latestNdviState = reduceLatestState(latestNdviState, { type: 'request' })
				renderLatestCard(latestNdviState, loadLatestNdvi, 'Latest NDVI')
				const payload = await runNdviRequest('latest NDVI', farmNdviLatestUrl, {
					method: 'GET',
					query: buildNdviQuery('ndvi_latest'),
					returnRaw: true,
				})
				if (!payload) {
					latestNdviState = reduceLatestState(latestNdviState, {
						type: 'failure',
						message: 'Unable to load latest NDVI.',
					})
					renderLatestCard(latestNdviState, loadLatestNdvi, 'Latest NDVI')
					return
				}
				latestNdviState = reduceLatestState(latestNdviState, { type: 'success', payload }, new Date())
				if (ndviTable) {
					ndviTable.textContent = ''
				}
				renderLatestCard(latestNdviState, loadLatestNdvi, 'Latest NDVI')
			}
			if (ndviLatestButton) {
				ndviLatestButton.addEventListener('click', loadLatestNdvi)
			}
			const loadNdviTimeseries = async () => {
				const state = readNdviDateState()
				const validation = validateTimeseriesInputs(state)
				if (!validation.ok) {
					showNdviError(validation.message)
					return
				}
				timeseriesNdviState = reduceTimeseriesState(
					timeseriesNdviState,
					{ type: 'request' },
					validation.start,
					validation.end,
				)
				renderTimeseriesCard(timeseriesNdviState, loadNdviTimeseries, 'NDVI timeseries')
				renderNdviCalendar(timeseriesNdviState)
				const payload = await runNdviRequest('timeseries', farmNdviTimeseriesUrl, {
					method: 'GET',
					query: buildNdviQuery('ndvi_timeseries', {
						start: validation.start,
						end: validation.end,
					}),
					returnRaw: true,
				})
				if (!payload) {
					timeseriesNdviState = reduceTimeseriesState(
						timeseriesNdviState,
						{ type: 'failure', message: 'Unable to load NDVI timeseries.' },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesNdviState, loadNdviTimeseries, 'NDVI timeseries')
					renderNdviCalendar(timeseriesNdviState)
					if (ndviTable) {
						ndviTable.textContent = ''
					}
					return
				}
				timeseriesNdviState = reduceTimeseriesState(
					timeseriesNdviState,
					{ type: 'success', payload },
					validation.start,
					validation.end,
				)
				renderTimeseriesCard(timeseriesNdviState, loadNdviTimeseries, 'NDVI timeseries')
				renderNdviCalendar(timeseriesNdviState)
				if (timeseriesNdviState.status === NDVI_SERIES_STATE.has_data) {
					renderNdviTable(timeseriesNdviState.vm?.points ?? [])
				} else if (ndviTable) {
					ndviTable.textContent = ''
				}
			}
			if (ndviTimeseriesButton) {
				ndviTimeseriesButton.addEventListener('click', loadNdviTimeseries)
			}
			if (ndviRasterButton) {
				ndviRasterButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const url = farmNdviRasterUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const query = buildNdviQuery('geotiff_download', { date: validation.date })
					const queryString = buildQueryString(query)
					const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
					const resolvedUrl = ncGenerateUrl(finalUrl)
					// Build tile URL template for XYZ tiles
					const tileUrl = url.replace('/raster.png', '/tiles')
					const colormap = rasterColormapSelect ? rasterColormapSelect.value : 'rdylgn'
					const baseTileUrl = ncGenerateUrl(tileUrl)
					const tileUrlTemplate = `${baseTileUrl}?z={z}&x={x}&y={y}&date=${encodeURIComponent(validation.date)}&colormap=${colormap}`
					const token = resolveRequestToken()
					const headers = {
						Accept: 'image/png',
						'OCS-APIRequest': 'true',
						'X-Requested-With': 'XMLHttpRequest',
					}
					if (token) {
						headers.requesttoken = token
					}

					try {
						const response = await fetch(resolvedUrl, {
							method: 'GET',
							credentials: 'same-origin',
							headers,
						})

						const contentType = response.headers.get('content-type') || ''
						if (!response.ok) {
							const result = await readJsonResponse(response)
							const message = buildNdviErrorMessage(
								response,
								result.data,
								`Unable to load raster preview (HTTP ${response.status}).`,
								result.text,
							)
							showNdviError(message)
							if (shouldToastNdviError(message)) {
								toast(message)
							}
							return
						}
						if (contentType && !contentType.includes('image/png')) {
							const result = await readJsonResponse(response)
							const message = buildNdviErrorMessage(
								response,
								result.data,
								'Raster preview did not return an image.',
								result.text,
							)
							showNdviError(message)
							if (shouldToastNdviError(message)) {
								toast(message)
							}
							return
						}
						const blob = await response.blob()
						if (blob.size === 0) {
							showNdviError('Raster preview response was empty.')
							return
						}
						if (ndviRasterObjectUrl) {
							URL.revokeObjectURL(ndviRasterObjectUrl)
						}
						ndviRasterObjectUrl = URL.createObjectURL(blob)
						showRasterMap(ndviRasterObjectUrl, selectedFarm, tileUrlTemplate, 'NDVI')
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Unable to load raster preview.'
						showNdviError(message)
					}
				})
			}
			if (ndviQueueButton) {
				ndviQueueButton.addEventListener('click', async () => {
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const queueOperation = resolveOperation('ndvi_raster_queue')
					const bodyDateField = resolveBodyFieldName(queueOperation?.bodyFields ?? {}, 'date')
					const queryDateField = resolveParamName(queueOperation?.queryParams ?? [], 'date')
					const data = await runNdviRequest('queue raster', farmNdviRasterQueueUrl, {
						method: 'POST',
						body: bodyDateField ? buildNdviBody('ndvi_raster_queue', { date: validation.date }) : null,
						query: !bodyDateField && queryDateField
							? buildNdviQuery('ndvi_raster_queue', { date: validation.date })
							: undefined,
					})
					if (data) {
						const jobId = data?.job_id ?? data?.jobId ?? data?.id ?? null
						const toastMessage = jobId
							? `Queued raster job #${jobId}`
							: 'Queued raster job'
						toast(toastMessage)
						const facts = []
						pushFact(facts, 'Raster date', validation.date)
						pushFact(facts, 'Job ID', jobId)
						const card = renderResultCard({
							title: 'NDVI raster queue',
							level: 'success',
							summary: `${toastMessage}.`,
							facts,
							debug: data,
						})
						replaceNdviOutput(card)
					}
				})
			}
			if (ndviRefreshButton) {
				ndviRefreshButton.addEventListener('click', async () => {
					const data = await runNdviRequest('refresh NDVI', farmNdviRefreshUrl, {
						method: 'POST',
						body: buildNdviBody('ndvi_refresh'),
					})
					if (data) {
						const jobId = data?.job_id ?? data?.jobId ?? data?.id ?? null
						const toastMessage = jobId
							? `Queued NDVI refresh job #${jobId}`
							: 'Queued NDVI refresh job'
						toast(toastMessage)
						const facts = []
						pushFact(facts, 'Job ID', jobId)
						const card = renderResultCard({
							title: 'NDVI refresh',
							level: 'success',
							summary: `${toastMessage}.`,
							facts,
							debug: data,
						})
						replaceNdviOutput(card)
					}
				})
			}
			if (farmStateButton) {
				farmStateButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (farmStateOutput) {
						farmStateOutput.hidden = false
					}
					if (farmStateContent) {
						farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading farm state...</div>'
					}
					const url = farmStateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const payload = await runNdviRequest('farm state', url, {
							method: 'GET',
							returnRaw: true,
						})
						if (!payload) {
							if (farmStateContent) {
								farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Unable to load farm state.</div>'
							}
							return
						}
						const data = unwrapResponseData(payload?.data ?? payload)
						const state = data?.state ?? 'unknown'
						const meanNdvi = data?.mean_ndvi ?? null
						const maxNdvi = data?.max_ndvi ?? null
						const coveragePct = data?.coverage_pct ?? null
						const trend = data?.trend ?? null
						const interpretation = data?.interpretation ?? ''
						const action = data?.action ?? ''

						const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
						const formatNumber = typeof ndviUi.formatNumber === 'function' ? ndviUi.formatNumber : (v) => String(v)
						const formatPercent = typeof ndviUi.formatPercent === 'function' ? ndviUi.formatPercent : (v) => String(v * 100) + '%'

						const stateLabels = {
							establishment: 'Establishment',
							full_canopy: 'Full Canopy',
							decline: 'Decline',
							growth: 'Growth',
							unknown: 'Unknown',
						}
						const stateLevel = {
							establishment: 'warning',
							full_canopy: 'success',
							decline: 'warning',
							growth: 'success',
							unknown: 'info',
						}
						const facts = []
						pushFact(facts, 'State', stateLabels[state] ?? state)
						pushFact(facts, 'Mean NDVI', meanNdvi !== null ? formatNumber(meanNdvi, 3) : '-')
						pushFact(facts, 'Max NDVI', maxNdvi !== null ? formatNumber(maxNdvi, 3) : '-')
						pushFact(facts, 'Coverage', coveragePct !== null ? formatPercent(coveragePct / 100, 1) : '-')
						pushFact(facts, 'Trend', trend !== null ? (trend >= 0 ? `+${formatNumber(trend, 4)}` : formatNumber(trend, 4)) : '-')
						const card = renderResultCard({
							title: 'Farm State',
							level: stateLevel[state] ?? 'info',
							badges: [
								stateLabels[state] ?? state,
							],
							summary: interpretation || `Farm state: ${stateLabels[state] ?? state}`,
							callout: action || 'No action available',
							facts,
							debug: data,
						})
						if (farmStateContent) {
							farmStateContent.innerHTML = ''
							farmStateContent.appendChild(card)
						}
					} catch (error) {
						console.error('[farm_intelligence_platform] farm state error', error)
						if (farmStateContent) {
							farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Failed to load farm state.</div>'
						}
					}
				})
			}
			if (decisionButton) {
				decisionButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (decisionOutput) {
						decisionOutput.hidden = false
					}
					if (decisionContent) {
						decisionContent.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading decision...</div>'
					}
					const url = farmDecisionUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const payload = await runNdviRequest('farm decision', url, {
							method: 'GET',
							returnRaw: true,
						})
						if (!payload) {
							if (decisionContent) {
								decisionContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Unable to load decision.</div>'
							}
							return
						}
						const data = unwrapResponseData(payload?.data ?? payload)
						const action = data?.action ?? 'none'
						const urgency = data?.urgency ?? 'none'
						const message = data?.message ?? ''
						const daysUntilCritical = data?.days_until_critical ?? null
						const crop = data?.crop ?? '-'
						const growthStage = data?.growth_stage ?? null
						const waterDeficitMm = data?.water_deficit_mm ?? null
						const etoTodayMm = data?.eto_today_mm ?? null
						const etcTodayMm = data?.etc_today_mm ?? null
						const v2 = data?.v2 ?? null
						const economics = v2?.economics ?? null
						const risk = v2?.risk_assessment ?? null

						const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
						const formatNumber = typeof ndviUi.formatNumber === 'function' ? ndviUi.formatNumber : (v) => String(v)

						const levelMap = {
							high: 'warning',
							medium: 'warning',
							low: 'info',
							none: 'success',
						}
						const facts = []
						pushFact(facts, 'Action', action)
						pushFact(facts, 'Urgency', urgency)
						pushFact(facts, 'Crop', crop)
						pushFact(facts, 'Growth stage', growthStage ?? '-')
						pushFact(facts, 'Days until critical', daysUntilCritical !== null ? formatNumber(daysUntilCritical) : '-')
						pushFact(facts, 'Water deficit (mm)', waterDeficitMm !== null ? formatNumber(waterDeficitMm, 2) : '-')
						pushFact(facts, 'ETo today (mm)', etoTodayMm !== null ? formatNumber(etoTodayMm, 2) : '-')
						pushFact(facts, 'ETc today (mm)', etcTodayMm !== null ? formatNumber(etcTodayMm, 2) : '-')
						if (economics) {
							pushFact(facts, 'Best action (economics)', economics.best_action ?? '-')
							pushFact(facts, 'Marginal return ($/ha)', economics.marginal_return_usd_per_ha !== null && economics.marginal_return_usd_per_ha !== undefined ? formatNumber(economics.marginal_return_usd_per_ha, 2) : '-')
						}
						if (risk) {
							pushFact(facts, 'Risk index', risk.risk_index !== null && risk.risk_index !== undefined ? formatNumber(risk.risk_index) : '-')
						}
						const card = renderResultCard({
							title: 'Decision',
							level: levelMap[urgency] ?? 'info',
							badges: [
								action,
								urgency,
							],
							summary: message || `Decision: ${action} (${urgency})`,
							callout: daysUntilCritical !== null ? `${action} — ${daysUntilCritical} day(s) until critical` : action,
							facts,
							debug: data,
						})
						if (decisionContent) {
							decisionContent.innerHTML = ''
							decisionContent.appendChild(card)
						}
					} catch (error) {
						console.error('[farm_intelligence_platform] farm decision error', error)
						if (decisionContent) {
							decisionContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Failed to load decision.</div>'
						}
					}
				})
			}
			if (ndwiLatestButton) {
				ndwiLatestButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (ndviOutput) {
						ndviOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading latest NDWI...</div>'
					}
					latestNdwiState = reduceLatestState(latestNdwiState, { type: 'request' })
					renderLatestCard(latestNdwiState, () => {}, 'Latest NDWI')
					const payload = await runNdviRequest('latest NDWI', farmNdwiLatestUrl, {
						method: 'GET',
						returnRaw: true,
					})
					if (!payload) {
						latestNdwiState = reduceLatestState(latestNdwiState, {
							type: 'failure',
							message: 'Unable to load latest NDWI.',
						})
						renderLatestCard(latestNdwiState, () => {}, 'Latest NDWI')
						return
					}
					latestNdwiState = reduceLatestState(latestNdwiState, { type: 'success', payload }, new Date())
					if (ndviTable) {
						ndviTable.textContent = ''
					}
					renderLatestCard(latestNdwiState, () => {}, 'Latest NDWI')
				})
			}
			if (ndwiTimeseriesButton) {
				ndwiTimeseriesButton.addEventListener('click', async () => {
					const state = readNdviDateState()
					const validation = validateTimeseriesInputs(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					timeseriesNdwiState = reduceTimeseriesState(
						timeseriesNdwiState,
						{ type: 'request' },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesNdwiState, () => {}, 'NDWI timeseries')
					renderNdviCalendar(timeseriesNdwiState)
					const payload = await runNdviRequest('ndwi_timeseries', farmNdwiTimeseriesUrl, {
						method: 'GET',
						query: buildNdviQuery('ndwi_timeseries', {
							start: validation.start,
							end: validation.end,
						}),
						returnRaw: true,
					})
					if (!payload) {
						timeseriesNdwiState = reduceTimeseriesState(
							timeseriesNdwiState,
							{ type: 'failure', message: 'Unable to load NDWI timeseries.' },
							validation.start,
							validation.end,
						)
						renderTimeseriesCard(timeseriesNdwiState, () => {}, 'NDWI timeseries')
						renderNdviCalendar(timeseriesNdwiState)
						if (ndviTable) {
							ndviTable.textContent = ''
						}
						return
					}
					timeseriesNdwiState = reduceTimeseriesState(
						timeseriesNdwiState,
						{ type: 'success', payload },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesNdwiState, () => {}, 'NDWI timeseries')
					renderNdviCalendar(timeseriesNdwiState)
					if (timeseriesNdwiState.status === NDVI_SERIES_STATE.has_data) {
						renderNdviTable(timeseriesNdwiState.vm?.points ?? [])
					} else if (ndviTable) {
						ndviTable.textContent = ''
					}
				})
			}
			if (ndwiRasterButton) {
				ndwiRasterButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const url = farmNdwiRasterUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const query = buildNdviQuery('ndwi_raster', { date: validation.date })
					const queryString = buildQueryString(query)
					const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
					const resolvedUrl = ncGenerateUrl(finalUrl)
					// Build tile URL template for XYZ tiles
					const tileUrl = url.replace('/raster.png', '/tiles')
					const colormap = rasterColormapSelect ? rasterColormapSelect.value : 'brbg'
					const baseTileUrl = ncGenerateUrl(tileUrl)
					const tileUrlTemplate = `${baseTileUrl}?z={z}&x={x}&y={y}&date=${encodeURIComponent(validation.date)}&colormap=${colormap}`
					const token = resolveRequestToken()
					const headers = {
						Accept: 'image/png',
						'OCS-APIRequest': 'true',
						'X-Requested-With': 'XMLHttpRequest',
					}
					if (token) {
						headers.requesttoken = token
					}

					try {
						const response = await fetch(resolvedUrl, {
							method: 'GET',
							credentials: 'same-origin',
							headers,
						})
						const contentType = response.headers.get('content-type') || ''
						if (!response.ok) {
							const result = await readJsonResponse(response)
							const message = buildNdviErrorMessage(
								response,
								result.data,
								`Unable to load NDWI raster preview (HTTP ${response.status}).`,
								result.text,
							)
							showNdviError(message)
							if (shouldToastNdviError(message)) {
								toast(message)
							}
							return
						}
						if (contentType && !contentType.includes('image/png')) {
							const result = await readJsonResponse(response)
							const message = buildNdviErrorMessage(
								response,
								result.data,
								'NDWI raster preview did not return an image.',
								result.text,
							)
							showNdviError(message)
							if (shouldToastNdviError(message)) {
								toast(message)
							}
							return
						}
						const blob = await response.blob()
						if (blob.size === 0) {
							showNdviError('NDWI raster preview response was empty.')
							return
						}
						if (ndviRasterObjectUrl) {
							URL.revokeObjectURL(ndviRasterObjectUrl)
						}
						ndviRasterObjectUrl = URL.createObjectURL(blob)
						showRasterMap(ndviRasterObjectUrl, selectedFarm, tileUrlTemplate, 'NDWI')
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Unable to load NDWI raster preview.'
						showNdviError(message)
					}
				})
			}
			if (ndwiQueueButton) {
				ndwiQueueButton.addEventListener('click', async () => {
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const queueOperation = resolveOperation('ndwi_raster_queue')
					const bodyDateField = resolveBodyFieldName(queueOperation?.bodyFields ?? {}, 'date')
					const queryDateField = resolveParamName(queueOperation?.queryParams ?? [], 'date')
					const data = await runNdviRequest('queue NDWI raster', farmNdwiRasterQueueUrl, {
						method: 'POST',
						body: bodyDateField ? buildNdviBody('ndwi_raster_queue', { date: validation.date }) : null,
						query: !bodyDateField && queryDateField
							? buildNdviQuery('ndwi_raster_queue', { date: validation.date })
							: undefined,
					})
					if (data) {
						const jobId = data?.job_id ?? data?.jobId ?? data?.id ?? null
						const toastMessage = jobId
							? `Queued NDWI raster job #${jobId}`
							: 'Queued NDWI raster job'
						toast(toastMessage)
						const facts = []
						pushFact(facts, 'Raster date', validation.date)
						pushFact(facts, 'Job ID', jobId)
						const card = renderResultCard({
							title: 'NDWI raster queue',
							level: 'success',
							summary: `${toastMessage}.`,
							facts,
							debug: data,
						})
						replaceNdviOutput(card)
					}
				})
			}
			if (ndwiRefreshButton) {
				ndwiRefreshButton.addEventListener('click', async () => {
					const data = await runNdviRequest('refresh NDWI', farmNdwiRefreshUrl, {
						method: 'POST',
						body: buildNdviBody('ndwi_refresh'),
					})
					if (data) {
						const jobId = data?.job_id ?? data?.jobId ?? data?.id ?? null
						const toastMessage = jobId
							? `Queued NDWI refresh job #${jobId}`
							: 'Queued NDWI refresh job'
						toast(toastMessage)
						const facts = []
						pushFact(facts, 'Job ID', jobId)
						const card = renderResultCard({
							title: 'NDWI refresh',
							level: 'success',
							summary: `${toastMessage}.`,
							facts,
							debug: data,
						})
						replaceNdviOutput(card)
					}
				})
			}
			if (ndwiStateButton) {
				ndwiStateButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (farmStateOutput) {
						farmStateOutput.hidden = false
					}
					if (farmStateContent) {
						farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading NDWI farm state...</div>'
					}
					const url = farmNdwiFarmStateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const payload = await runNdviRequest('ndwi farm state', url, {
							method: 'GET',
							returnRaw: true,
						})
						if (!payload) {
							if (farmStateContent) {
								farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Unable to load NDWI farm state.</div>'
							}
							return
						}
						const data = unwrapResponseData(payload?.data ?? payload)
						const state = data?.state ?? 'unknown'
						const meanNdwi = data?.mean_ndwi ?? null
						const maxNdwi = data?.max_ndwi ?? null
						const coveragePct = data?.coverage_pct ?? null
						const trend = data?.trend ?? null
						const interpretation = data?.interpretation ?? ''
						const action = data?.action ?? ''

						const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
						const formatNumber = typeof ndviUi.formatNumber === 'function' ? ndviUi.formatNumber : (v) => String(v)
						const formatPercent = typeof ndviUi.formatPercent === 'function' ? ndviUi.formatPercent : (v) => String(v * 100) + '%'

						const stateLabels = {
							water: 'Water',
							moist: 'Moist',
							dry: 'Dry',
							unknown: 'Unknown',
						}
						const stateLevel = {
							water: 'success',
							moist: 'info',
							dry: 'warning',
							unknown: 'info',
						}
						const facts = []
						pushFact(facts, 'State', stateLabels[state] ?? state)
						pushFact(facts, 'Mean NDWI', meanNdwi !== null ? formatNumber(meanNdwi, 3) : '-')
						pushFact(facts, 'Max NDWI', maxNdwi !== null ? formatNumber(maxNdwi, 3) : '-')
						pushFact(facts, 'Coverage', coveragePct !== null ? formatPercent(coveragePct / 100, 1) : '-')
						pushFact(facts, 'Trend', trend !== null ? (trend >= 0 ? `+${formatNumber(trend, 4)}` : formatNumber(trend, 4)) : '-')
						const card = renderResultCard({
							title: 'NDWI Farm State',
							level: stateLevel[state] ?? 'info',
							badges: [
								stateLabels[state] ?? state,
							],
							summary: interpretation || `NDWI farm state: ${stateLabels[state] ?? state}`,
							callout: action || 'No action available',
							facts,
							debug: data,
						})
						if (farmStateContent) {
							farmStateContent.innerHTML = ''
							farmStateContent.appendChild(card)
						}
					} catch (error) {
						console.error('[farm_intelligence_platform] NDWI farm state error', error)
						if (farmStateContent) {
							farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Failed to load NDWI farm state.</div>'
						}
					}
				})
			}
			if (ndmiLatestButton) {
				ndmiLatestButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (ndviOutput) {
						ndviOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading latest NDMI...</div>'
					}
					latestNdmiState = reduceLatestState(latestNdmiState, { type: 'request' })
					renderLatestCard(latestNdmiState, () => {}, 'Latest NDMI')
					const payload = await runNdviRequest('latest NDMI', farmNdmiLatestUrl, {
						method: 'GET',
						returnRaw: true,
					})
					if (!payload) {
						latestNdmiState = reduceLatestState(latestNdmiState, {
							type: 'failure',
							message: 'Unable to load latest NDMI.',
						})
						renderLatestCard(latestNdmiState, () => {}, 'Latest NDMI')
						return
					}
					latestNdmiState = reduceLatestState(latestNdmiState, { type: 'success', payload }, new Date())
					if (ndviTable) {
						ndviTable.textContent = ''
					}
					renderLatestCard(latestNdmiState, () => {}, 'Latest NDMI')
				})
			}
			if (ndmiTimeseriesButton) {
				ndmiTimeseriesButton.addEventListener('click', async () => {
					const state = readNdviDateState()
					const validation = validateTimeseriesInputs(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					timeseriesNdmiState = reduceTimeseriesState(
						timeseriesNdmiState,
						{ type: 'request' },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesNdmiState, () => {}, 'NDMI timeseries')
					renderNdviCalendar(timeseriesNdmiState)
					const payload = await runNdviRequest('ndmi_timeseries', farmNdmiTimeseriesUrl, {
						method: 'GET',
						query: buildNdviQuery('ndmi_timeseries', {
							start: validation.start,
							end: validation.end,
						}),
						returnRaw: true,
					})
					if (!payload) {
						timeseriesNdmiState = reduceTimeseriesState(
							timeseriesNdmiState,
							{ type: 'failure', message: 'Unable to load NDMI timeseries.' },
							validation.start,
							validation.end,
						)
						renderTimeseriesCard(timeseriesNdmiState, () => {}, 'NDMI timeseries')
						renderNdviCalendar(timeseriesNdmiState)
						if (ndviTable) {
							ndviTable.textContent = ''
						}
						return
					}
					timeseriesNdmiState = reduceTimeseriesState(
						timeseriesNdmiState,
						{ type: 'success', payload },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesNdmiState, () => {}, 'NDMI timeseries')
					renderNdviCalendar(timeseriesNdmiState)
					if (timeseriesNdmiState.status === NDVI_SERIES_STATE.has_data) {
						renderNdviTable(timeseriesNdmiState.vm?.points ?? [])
					} else if (ndviTable) {
						ndviTable.textContent = ''
					}
				})
			}
			let currentTileUrlTemplate = ''
			let rasterTileLayer = null

			const destroyRasterMap = () => {
				rasterTileLayer = null
				currentTileUrlTemplate = ''
				rasterAvailableDates = []
				rasterCurrentIndexType = ''
				rasterCurrentFarmId = ''
				if (rasterDateSlider) {
					rasterDateSlider.disabled = true
					rasterDateSlider.min = '0'
					rasterDateSlider.max = '0'
					rasterDateSlider.value = '0'
				}
				if (rasterDateLabel) {
					rasterDateLabel.textContent = ''
				}
				if (rasterMap) {
					rasterMap.remove()
					rasterMap = null
				}
				if (rasterMapContainer) {
					rasterMapContainer.hidden = true
				}
				if (rasterControls) {
					rasterControls.hidden = true
				}
				if (ndviRasterPreview) {
					ndviRasterPreview.hidden = true
				}
			}

			const showRasterMap = (blobUrl, farm, tileUrlTemplate, indexType) => {
				const f = farm?.data ?? farm
				const rawSouth = f?.bbox_south
				const rawWest = f?.bbox_west
				const rawNorth = f?.bbox_north
				const rawEast = f?.bbox_east
				const hasValidBbox = rawSouth !== null && rawSouth !== undefined && rawWest !== null && rawWest !== undefined
					&& rawNorth !== null && rawNorth !== undefined && rawEast !== null && rawEast !== undefined
					&& String(rawSouth).trim() !== '' && String(rawWest).trim() !== ''
					&& String(rawNorth).trim() !== '' && String(rawEast).trim() !== ''
				if (!hasValidBbox) {
					if (ndviRasterImg && ndviRasterObjectUrl) {
						ndviRasterImg.src = ndviRasterObjectUrl
					}
					if (ndviRasterPreview) {
						ndviRasterPreview.hidden = false
					}
					return
				}
				destroyRasterMap()
				if (!rasterMapContainer || typeof L === 'undefined') {
					if (ndviRasterImg && ndviRasterObjectUrl) {
						ndviRasterImg.src = ndviRasterObjectUrl
					}
					if (ndviRasterPreview) {
						ndviRasterPreview.hidden = false
					}
					return
				}
				const south = Number(rawSouth)
				const west = Number(rawWest)
				const north = Number(rawNorth)
				const east = Number(rawEast)
				const centerLat = (south + north) / 2
				const centerLng = (west + east) / 2
				const bounds = L.latLngBounds(
					[south, west],
					[north, east],
				)
				rasterCurrentIndexType = indexType || ''
				rasterCurrentFarmId = farm?.id || (farm?.data?.id ?? '')
				const tileTemplate = tileUrlTemplate || null
				rasterMapContainer.hidden = false
				if (rasterControls) {
					rasterControls.hidden = false
				}
				const initMap = () => {
					const rect = rasterMapContainer.getBoundingClientRect()
					if (rect.width === 0 || rect.height === 0) {
						// Container not laid out yet, retry
						setTimeout(initMap, 100)
						return
					}
					rasterMap = L.map(rasterMapContainer, {
						center: [centerLat, centerLng],
						zoom: 12,
						zoomControl: true,
						attributionControl: true,
					})
					L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
						maxZoom: 19,
						attribution: '&copy; OpenStreetMap contributors',
					}).addTo(rasterMap)
					if (tileTemplate) {
						currentTileUrlTemplate = tileTemplate
						rasterTileLayer = L.tileLayer(tileTemplate, {
							opacity: 0.8,
							maxZoom: 18,
							maxNativeZoom: 16,
							tms: false,
							bounds,
						}).addTo(rasterMap)
						loadRasterDates(rasterCurrentFarmId, rasterCurrentIndexType)
					} else {
						L.imageOverlay(blobUrl, bounds, { opacity: 0.8 }).addTo(rasterMap)
					}
					rasterMap.invalidateSize()
					rasterMap.fitBounds(bounds, {
						padding: [20, 20],
						maxZoom: 14,
						animate: false,
					})
					setTimeout(() => {
						if (rasterMap) rasterMap.invalidateSize()
					}, 250)
				}
				setTimeout(initMap, 200)
			}

			const rasterColormapSelect = document.getElementById('farm-intelligence-platform-raster-colormap')
			const rasterControls = document.getElementById('farm-intelligence-platform-raster-controls')
			const rasterDateSlider = document.getElementById('farm-intelligence-platform-raster-date-slider')
			const rasterDateLabel = document.getElementById('farm-intelligence-platform-raster-date-label')
			let rasterAvailableDates = []
			let rasterCurrentIndexType = ''
			let rasterCurrentFarmId = ''

			const updateRasterColormap = () => {
				if (!rasterMap || !currentTileUrlTemplate || !rasterTileLayer || !rasterColormapSelect) {
					return
				}
				const cmap = rasterColormapSelect.value
				// Build new URL with updated colormap param
				const base = currentTileUrlTemplate.replace(/&colormap=[^&]*/g, '').replace(/\?colormap=[^&]*&/, '?')
				const separator = base.includes('?') ? '&' : '?'
				const newUrl = `${base}${separator}colormap=${cmap}`
				const currentBounds = rasterTileLayer.options.bounds || null
				rasterMap.removeLayer(rasterTileLayer)
				rasterTileLayer = L.tileLayer(newUrl, {
					opacity: 0.8,
					maxZoom: 18,
					maxNativeZoom: 16,
					tms: false,
					bounds: currentBounds,
				}).addTo(rasterMap)
				currentTileUrlTemplate = newUrl
			}
			if (rasterColormapSelect) {
				rasterColormapSelect.addEventListener('change', updateRasterColormap)
			}

			const loadRasterDates = async (farmId, indexType) => {
				if (!farmId || !indexType || !rasterDateSlider || !rasterDateLabel) {
					return
				}
				const indexLower = indexType.toLowerCase()
				// Pick the correct base URL for this index type
				let baseUrl = ''
				if (indexLower === 'ndvi') baseUrl = farmNdviRasterUrl
				else if (indexLower === 'ndwi') baseUrl = farmNdwiRasterUrl
				else if (indexLower === 'ndmi') baseUrl = farmNdmiRasterUrl
				else if (indexLower === 'rvi') baseUrl = farmRviRasterUrl
				else if (indexLower === 's1_smi') baseUrl = farmS1SmiRasterUrl
				else if (indexLower === 's3_lst') baseUrl = farmS3LstRasterUrl
				else if (indexLower === 'landsat_lst') baseUrl = farmLandsatLstRasterUrl
				else if (indexLower === 'iron_oxide') baseUrl = farmIronOxideRasterUrl
				else if (indexLower === 'evi') baseUrl = farmEviRasterUrl
				else if (indexLower === 'l_rvi') baseUrl = farmLRviRasterUrl
				else if (indexLower === 'nisar_smi') baseUrl = farmNisarSmiRasterUrl
				else return
				const datesUrl = baseUrl
					.replace('__FARM_ID__', encodeURIComponent(farmId))
					.replace('/raster.png', '/raster-dates')
				try {
					const token = resolveRequestToken()
					const headers = { 'OCS-APIRequest': 'true', 'X-Requested-With': 'XMLHttpRequest' }
					if (token) headers.requesttoken = token
					const response = await fetch(ncGenerateUrl(datesUrl), { credentials: 'same-origin', headers })
					if (!response.ok) {
						rasterDateSlider.disabled = true
						rasterDateLabel.textContent = ''
						return
					}
					const result = await response.json()
					const dates = result?.data ?? []
					rasterAvailableDates = dates
					if (dates.length <= 1) {
						rasterDateSlider.disabled = true
						rasterDateLabel.textContent = dates.length === 1 ? dates[0] : ''
						return
					}
					rasterDateSlider.min = '0'
					rasterDateSlider.max = String(dates.length - 1)
					rasterDateSlider.value = String(dates.length - 1)
					rasterDateSlider.disabled = false
					rasterDateLabel.textContent = dates[dates.length - 1]
				} catch {
					rasterDateSlider.disabled = true
					rasterDateLabel.textContent = ''
				}
			}

			const updateRasterDate = () => {
				if (!rasterMap || !rasterDateSlider || !currentTileUrlTemplate || !rasterTileLayer || rasterAvailableDates.length === 0) {
					return
				}
				const idx = parseInt(rasterDateSlider.value, 10)
				const selectedDate = rasterAvailableDates[idx]
				if (!selectedDate) return
				if (rasterDateLabel) rasterDateLabel.textContent = selectedDate
				// Replace date in tile URL
				const newUrl = currentTileUrlTemplate.replace(/date=[^&]+/, `date=${selectedDate}`)
				const currentBounds = rasterTileLayer.options.bounds || null
				rasterMap.removeLayer(rasterTileLayer)
				rasterTileLayer = L.tileLayer(newUrl, {
					opacity: 0.8,
					maxZoom: 18,
					maxNativeZoom: 16,
					tms: false,
					bounds: currentBounds,
				}).addTo(rasterMap)
				currentTileUrlTemplate = newUrl
			}

			if (rasterDateSlider) {
				rasterDateSlider.addEventListener('input', updateRasterDate)
				rasterDateSlider.disabled = true
			}
			if (ndmiRasterButton) {
				ndmiRasterButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const url = farmNdmiRasterUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const query = buildNdviQuery('ndmi_raster', { date: validation.date })
					const queryString = buildQueryString(query)
					const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
					const resolvedUrl = ncGenerateUrl(finalUrl)
					// Build tile URL template for XYZ tiles
					const tileUrl = url.replace('/raster.png', '/tiles')
					const colormap = rasterColormapSelect ? rasterColormapSelect.value : 'brbg'
					const baseTileUrl = ncGenerateUrl(tileUrl)
					const tileUrlTemplate = `${baseTileUrl}?z={z}&x={x}&y={y}&date=${encodeURIComponent(validation.date)}&colormap=${colormap}`
					const token = resolveRequestToken()
					const headers = {
						Accept: 'image/png',
						'OCS-APIRequest': 'true',
						'X-Requested-With': 'XMLHttpRequest',
					}
					if (token) {
						headers.requesttoken = token
					}

					try {
						const response = await fetch(resolvedUrl, {
							method: 'GET',
							credentials: 'same-origin',
							headers,
						})
						const contentType = response.headers.get('content-type') || ''
						if (!response.ok) {
							const result = await readJsonResponse(response)
							const message = buildNdviErrorMessage(
								response,
								result.data,
								`Unable to load NDMI raster preview (HTTP ${response.status}).`,
								result.text,
							)
							showNdviError(message)
							if (shouldToastNdviError(message)) {
								toast(message)
							}
							return
						}
						if (contentType && !contentType.includes('image/png')) {
							const result = await readJsonResponse(response)
							const message = buildNdviErrorMessage(
								response,
								result.data,
								'NDMI raster preview did not return an image.',
								result.text,
							)
							showNdviError(message)
							if (shouldToastNdviError(message)) {
								toast(message)
							}
							return
						}
						const blob = await response.blob()
						if (blob.size === 0) {
							showNdviError('NDMI raster preview response was empty.')
							return
						}
						if (ndviRasterObjectUrl) {
							URL.revokeObjectURL(ndviRasterObjectUrl)
						}
						ndviRasterObjectUrl = URL.createObjectURL(blob)
						showRasterMap(ndviRasterObjectUrl, selectedFarm, tileUrlTemplate, 'NDMI')
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Unable to load NDMI raster preview.'
						showNdviError(message)
					}
				})
			}
			if (ndmiQueueButton) {
				ndmiQueueButton.addEventListener('click', async () => {
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const queueOperation = resolveOperation('ndmi_raster_queue')
					const bodyDateField = resolveBodyFieldName(queueOperation?.bodyFields ?? {}, 'date')
					const queryDateField = resolveParamName(queueOperation?.queryParams ?? [], 'date')
					const data = await runNdviRequest('queue NDMI raster', farmNdmiRasterQueueUrl, {
						method: 'POST',
						body: bodyDateField ? buildNdviBody('ndmi_raster_queue', { date: validation.date }) : null,
						query: !bodyDateField && queryDateField
							? buildNdviQuery('ndmi_raster_queue', { date: validation.date })
							: undefined,
					})
					if (data) {
						const jobId = data?.job_id ?? data?.jobId ?? data?.id ?? null
						const toastMessage = jobId
							? `Queued NDMI raster job #${jobId}`
							: 'Queued NDMI raster job'
						toast(toastMessage)
						const facts = []
						pushFact(facts, 'Raster date', validation.date)
						pushFact(facts, 'Job ID', jobId)
						const card = renderResultCard({
							title: 'NDMI raster queue',
							level: 'success',
							summary: `${toastMessage}.`,
							facts,
							debug: data,
						})
						replaceNdviOutput(card)
					}
				})
			}
			if (ndmiRefreshButton) {
				ndmiRefreshButton.addEventListener('click', async () => {
					const data = await runNdviRequest('refresh NDMI', farmNdmiRefreshUrl, {
						method: 'POST',
						body: buildNdviBody('ndmi_refresh'),
					})
					if (data) {
						const jobId = data?.job_id ?? data?.jobId ?? data?.id ?? null
						const toastMessage = jobId
							? `Queued NDMI refresh job #${jobId}`
							: 'Queued NDMI refresh job'
						toast(toastMessage)
						const facts = []
						pushFact(facts, 'Job ID', jobId)
						const card = renderResultCard({
							title: 'NDMI refresh',
							level: 'success',
							summary: `${toastMessage}.`,
							facts,
							debug: data,
						})
						replaceNdviOutput(card)
					}
				})
			}
			if (ndmiStateButton) {
				ndmiStateButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (farmStateOutput) {
						farmStateOutput.hidden = false
					}
					if (farmStateContent) {
						farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading NDMI farm state...</div>'
					}
					const url = farmNdmiFarmStateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const payload = await runNdviRequest('ndmi farm state', url, {
							method: 'GET',
							returnRaw: true,
						})
						if (!payload) {
							if (farmStateContent) {
								farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Unable to load NDMI farm state.</div>'
							}
							return
						}
						const data = unwrapResponseData(payload?.data ?? payload)
						const state = data?.state ?? 'unknown'
						const meanNdmi = data?.mean_ndmi ?? null
						const maxNdmi = data?.max_ndmi ?? null
						const minNdmi = data?.min_ndmi ?? null
						const trend = data?.trend ?? null
						const interpretation = data?.interpretation ?? ''
						const action = data?.action ?? ''

						const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
						const formatNumber = typeof ndviUi.formatNumber === 'function' ? ndviUi.formatNumber : (v) => String(v)

						const stateLabels = {
							dry: 'Dry',
							moist: 'Moist',
							wet: 'Wet',
							unknown: 'Unknown',
						}
						const stateLevel = {
							dry: 'warning',
							moist: 'info',
							wet: 'success',
							unknown: 'info',
						}
						const facts = []
						pushFact(facts, 'State', stateLabels[state] ?? state)
						pushFact(facts, 'Mean NDMI', meanNdmi !== null ? formatNumber(meanNdmi, 3) : '-')
						pushFact(facts, 'Max NDMI', maxNdmi !== null ? formatNumber(maxNdmi, 3) : '-')
						pushFact(facts, 'Min NDMI', minNdmi !== null ? formatNumber(minNdmi, 3) : '-')
						pushFact(facts, 'Trend', trend !== null ? (trend >= 0 ? `+${formatNumber(trend, 4)}` : formatNumber(trend, 4)) : '-')
						const card = renderResultCard({
							title: 'NDMI Farm State',
							level: stateLevel[state] ?? 'info',
							badges: [
								stateLabels[state] ?? state,
							],
							summary: interpretation || `NDMI farm state: ${stateLabels[state] ?? state}`,
							callout: action || 'No action available',
							facts,
							debug: data,
						})
						if (farmStateContent) {
							farmStateContent.innerHTML = ''
							farmStateContent.appendChild(card)
						}
					} catch (error) {
						console.error('[farm_intelligence_platform] NDMI farm state error', error)
						if (farmStateContent) {
							farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Failed to load NDMI farm state.</div>'
						}
					}
				})
			}
			if (rviLatestButton) {
				rviLatestButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (ndviOutput) {
						ndviOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading latest RVI...</div>'
					}
					latestRviState = reduceLatestState(latestRviState, { type: 'request' })
					renderLatestCard(latestRviState, () => {}, 'Latest RVI')
					const payload = await runNdviRequest('latest RVI', farmRviLatestUrl, {
						method: 'GET',
						returnRaw: true,
					})
					if (!payload) {
						latestRviState = reduceLatestState(latestRviState, {
							type: 'failure',
							message: 'Unable to load latest RVI.',
						})
						renderLatestCard(latestRviState, () => {}, 'Latest RVI')
						return
					}
					latestRviState = reduceLatestState(latestRviState, { type: 'success', payload }, new Date())
					if (ndviTable) {
						ndviTable.textContent = ''
					}
					renderLatestCard(latestRviState, () => {}, 'Latest RVI')
				})
			}
			if (rviTimeseriesButton) {
				rviTimeseriesButton.addEventListener('click', async () => {
					const state = readNdviDateState()
					const validation = validateTimeseriesInputs(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					timeseriesRviState = reduceTimeseriesState(
						timeseriesRviState,
						{ type: 'request' },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesRviState, () => {}, 'RVI timeseries')
					renderNdviCalendar(timeseriesRviState)
					const payload = await runNdviRequest('rvi_timeseries', farmRviTimeseriesUrl, {
						method: 'GET',
						query: buildNdviQuery('rvi_timeseries', {
							start: validation.start,
							end: validation.end,
						}),
						returnRaw: true,
					})
					if (!payload) {
						timeseriesRviState = reduceTimeseriesState(
							timeseriesRviState,
							{ type: 'failure', message: 'Unable to load RVI timeseries.' },
							validation.start,
							validation.end,
						)
						renderTimeseriesCard(timeseriesRviState, () => {}, 'RVI timeseries')
						renderNdviCalendar(timeseriesRviState)
						if (ndviTable) {
							ndviTable.textContent = ''
						}
						return
					}
					timeseriesRviState = reduceTimeseriesState(
						timeseriesRviState,
						{ type: 'success', payload },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesRviState, () => {}, 'RVI timeseries')
					renderNdviCalendar(timeseriesRviState)
					if (timeseriesRviState.status === NDVI_SERIES_STATE.has_data) {
						renderNdviTable(timeseriesRviState.vm?.points ?? [])
					} else if (ndviTable) {
						ndviTable.textContent = ''
					}
				})
			}
			if (rviRasterButton) {
				rviRasterButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const url = farmRviRasterUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const query = buildNdviQuery('rvi_raster', { date: validation.date })
					const queryString = buildQueryString(query)
					const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
					const resolvedUrl = ncGenerateUrl(finalUrl)
					const tileUrl = url.replace('/raster.png', '/tiles')
					const colormap = rasterColormapSelect ? rasterColormapSelect.value : 'brbg'
					const baseTileUrl = ncGenerateUrl(tileUrl)
					const tileUrlTemplate = `${baseTileUrl}?z={z}&x={x}&y={y}&date=${encodeURIComponent(validation.date)}&colormap=${colormap}`
					const token = resolveRequestToken()
					const headers = {
						Accept: 'image/png',
						'OCS-APIRequest': 'true',
						'X-Requested-With': 'XMLHttpRequest',
					}
					if (token) {
						headers.requesttoken = token
					}

					try {
						const response = await fetch(resolvedUrl, {
							method: 'GET',
							credentials: 'same-origin',
							headers,
						})
						const contentType = response.headers.get('content-type') || ''
						if (!response.ok) {
							const result = await readJsonResponse(response)
							const message = buildNdviErrorMessage(
								response,
								result.data,
								`Unable to load RVI raster preview (HTTP ${response.status}).`,
								result.text,
							)
							showNdviError(message)
							if (shouldToastNdviError(message)) {
								toast(message)
							}
							return
						}
						if (contentType && !contentType.includes('image/png')) {
							const result = await readJsonResponse(response)
							const message = buildNdviErrorMessage(
								response,
								result.data,
								'RVI raster preview did not return an image.',
								result.text,
							)
							showNdviError(message)
							if (shouldToastNdviError(message)) {
								toast(message)
							}
							return
						}
						const blob = await response.blob()
						if (blob.size === 0) {
							showNdviError('RVI raster preview response was empty.')
							return
						}
						if (ndviRasterObjectUrl) {
							URL.revokeObjectURL(ndviRasterObjectUrl)
						}
						ndviRasterObjectUrl = URL.createObjectURL(blob)
						showRasterMap(ndviRasterObjectUrl, selectedFarm, tileUrlTemplate, 'RVI')
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Unable to load RVI raster preview.'
						showNdviError(message)
					}
				})
			}
			if (rviQueueButton) {
				rviQueueButton.addEventListener('click', async () => {
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const queueOperation = resolveOperation('rvi_raster_queue')
					const bodyDateField = resolveBodyFieldName(queueOperation?.bodyFields ?? {}, 'date')
					const queryDateField = resolveParamName(queueOperation?.queryParams ?? [], 'date')
					const data = await runNdviRequest('queue RVI raster', farmRviRasterQueueUrl, {
						method: 'POST',
						body: bodyDateField ? buildNdviBody('rvi_raster_queue', { date: validation.date }) : null,
						query: !bodyDateField && queryDateField
							? buildNdviQuery('rvi_raster_queue', { date: validation.date })
							: undefined,
					})
					if (data) {
						const jobId = data?.job_id ?? data?.jobId ?? data?.id ?? null
						const toastMessage = jobId
							? `Queued RVI raster job #${jobId}`
							: 'Queued RVI raster job'
						toast(toastMessage)
						const facts = []
						pushFact(facts, 'Raster date', validation.date)
						pushFact(facts, 'Job ID', jobId)
						const card = renderResultCard({
							title: 'RVI raster queue',
							level: 'success',
							summary: `${toastMessage}.`,
							facts,
							debug: data,
						})
						replaceNdviOutput(card)
					}
				})
			}
			if (rviRefreshButton) {
				rviRefreshButton.addEventListener('click', async () => {
					const data = await runNdviRequest('refresh RVI', farmRviRefreshUrl, {
						method: 'POST',
						body: buildNdviBody('rvi_refresh'),
					})
					if (data) {
						const jobId = data?.job_id ?? data?.jobId ?? data?.id ?? null
						const toastMessage = jobId
							? `Queued RVI refresh job #${jobId}`
							: 'Queued RVI refresh job'
						toast(toastMessage)
						const facts = []
						pushFact(facts, 'Job ID', jobId)
						const card = renderResultCard({
							title: 'RVI refresh',
							level: 'success',
							summary: `${toastMessage}.`,
							facts,
							debug: data,
						})
						replaceNdviOutput(card)
					}
				})
			}
			if (rviStateButton) {
				rviStateButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (farmStateOutput) {
						farmStateOutput.hidden = false
					}
					if (farmStateContent) {
						farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading RVI farm state...</div>'
					}
					const url = farmRviFarmStateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const payload = await runNdviRequest('rvi farm state', url, {
							method: 'GET',
							returnRaw: true,
						})
						if (!payload) {
							if (farmStateContent) {
								farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Unable to load RVI farm state.</div>'
							}
							return
						}
						const data = unwrapResponseData(payload?.data ?? payload)
						const state = data?.state ?? 'unknown'
						const meanRvi = data?.mean_rvi ?? null
						const maxRvi = data?.max_rvi ?? null
						const minRvi = data?.min_rvi ?? null
						const trend = data?.trend ?? null
						const interpretation = data?.interpretation ?? ''
						const action = data?.action ?? ''

						const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
						const formatNumber = typeof ndviUi.formatNumber === 'function' ? ndviUi.formatNumber : (v) => String(v)

						const stateLabels = {
							dry: 'Dry',
							moist: 'Moist',
							wet: 'Wet',
							unknown: 'Unknown',
						}
						const stateLevel = {
							dry: 'warning',
							moist: 'info',
							wet: 'success',
							unknown: 'info',
						}
						const facts = []
						pushFact(facts, 'State', stateLabels[state] ?? state)
						pushFact(facts, 'Mean RVI', meanRvi !== null ? formatNumber(meanRvi, 3) : '-')
						pushFact(facts, 'Max RVI', maxRvi !== null ? formatNumber(maxRvi, 3) : '-')
						pushFact(facts, 'Min RVI', minRvi !== null ? formatNumber(minRvi, 3) : '-')
						pushFact(facts, 'Trend', trend !== null ? (trend >= 0 ? `+${formatNumber(trend, 4)}` : formatNumber(trend, 4)) : '-')
						const card = renderResultCard({
							title: 'RVI Farm State',
							level: stateLevel[state] ?? 'info',
							badges: [
								stateLabels[state] ?? state,
							],
							summary: interpretation || `RVI farm state: ${stateLabels[state] ?? state}`,
							callout: action || 'No action available',
							facts,
							debug: data,
						})
						if (farmStateContent) {
							farmStateContent.innerHTML = ''
							farmStateContent.appendChild(card)
						}
					} catch (error) {
						console.error('[farm_intelligence_platform] RVI farm state error', error)
						if (farmStateContent) {
							farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Failed to load RVI farm state.</div>'
						}
					}
				})
			}
			if (s1SmiLatestButton) {
				s1SmiLatestButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (ndviOutput) {
						ndviOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading latest S1_SMI...</div>'
					}
					latestS1SmiState = reduceLatestState(latestS1SmiState, { type: 'request' })
					renderLatestCard(latestS1SmiState, () => {}, 'Latest S1_SMI')
					const payload = await runNdviRequest('latest S1_SMI', farmS1SmiLatestUrl, {
						method: 'GET',
						returnRaw: true,
					})
					if (!payload) {
						latestS1SmiState = reduceLatestState(latestS1SmiState, {
							type: 'failure',
							message: 'Unable to load latest S1_SMI.',
						})
						renderLatestCard(latestS1SmiState, () => {}, 'Latest S1_SMI')
						return
					}
					latestS1SmiState = reduceLatestState(latestS1SmiState, { type: 'success', payload }, new Date())
					if (ndviTable) {
						ndviTable.textContent = ''
					}
					renderLatestCard(latestS1SmiState, () => {}, 'Latest S1_SMI')
				})
			}
			if (s1SmiTimeseriesButton) {
				s1SmiTimeseriesButton.addEventListener('click', async () => {
					const state = readNdviDateState()
					const validation = validateTimeseriesInputs(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					timeseriesS1SmiState = reduceTimeseriesState(
						timeseriesS1SmiState,
						{ type: 'request' },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesS1SmiState, () => {}, 'S1_SMI timeseries')
					renderNdviCalendar(timeseriesS1SmiState)
					const payload = await runNdviRequest('s1_smi_timeseries', farmS1SmiTimeseriesUrl, {
						method: 'GET',
						query: buildNdviQuery('s1_smi_timeseries', {
							start: validation.start,
							end: validation.end,
						}),
						returnRaw: true,
					})
					if (!payload) {
						timeseriesS1SmiState = reduceTimeseriesState(
							timeseriesS1SmiState,
							{ type: 'failure', message: 'Unable to load S1_SMI timeseries.' },
							validation.start,
							validation.end,
						)
						renderTimeseriesCard(timeseriesS1SmiState, () => {}, 'S1_SMI timeseries')
						renderNdviCalendar(timeseriesS1SmiState)
						if (ndviTable) {
							ndviTable.textContent = ''
						}
						return
					}
					timeseriesS1SmiState = reduceTimeseriesState(
						timeseriesS1SmiState,
						{ type: 'success', payload },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesS1SmiState, () => {}, 'S1_SMI timeseries')
					renderNdviCalendar(timeseriesS1SmiState)
					if (timeseriesS1SmiState.status === NDVI_SERIES_STATE.has_data) {
						renderNdviTable(timeseriesS1SmiState.vm?.points ?? [])
					} else if (ndviTable) {
						ndviTable.textContent = ''
					}
				})
			}
			if (s1SmiRasterButton) {
				s1SmiRasterButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const url = farmS1SmiRasterUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const query = buildNdviQuery('s1_smi_raster', { date: validation.date })
					const queryString = buildQueryString(query)
					const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
					const resolvedUrl = ncGenerateUrl(finalUrl)
					const tileUrl = url.replace('/raster.png', '/tiles')
					const colormap = rasterColormapSelect ? rasterColormapSelect.value : 'brbg'
					const baseTileUrl = ncGenerateUrl(tileUrl)
					const tileUrlTemplate = `${baseTileUrl}?z={z}&x={x}&y={y}&date=${encodeURIComponent(validation.date)}&colormap=${colormap}`
					const token = resolveRequestToken()
					const headers = {
						Accept: 'image/png',
						'OCS-APIRequest': 'true',
						'X-Requested-With': 'XMLHttpRequest',
					}
					if (token) {
						headers.requesttoken = token
					}

					try {
						const response = await fetch(resolvedUrl, {
							method: 'GET',
							credentials: 'same-origin',
							headers,
						})
						const contentType = response.headers.get('content-type') || ''
						if (!response.ok) {
							const result = await readJsonResponse(response)
							const message = buildNdviErrorMessage(
								response,
								result.data,
								`Unable to load S1_SMI raster preview (HTTP ${response.status}).`,
								result.text,
							)
							showNdviError(message)
							if (shouldToastNdviError(message)) {
								toast(message)
							}
							return
						}
						if (contentType && !contentType.includes('image/png')) {
							const result = await readJsonResponse(response)
							const message = buildNdviErrorMessage(
								response,
								result.data,
								'S1_SMI raster preview did not return an image.',
								result.text,
							)
							showNdviError(message)
							if (shouldToastNdviError(message)) {
								toast(message)
							}
							return
						}
						const blob = await response.blob()
						if (blob.size === 0) {
							showNdviError('S1_SMI raster preview response was empty.')
							return
						}
						if (ndviRasterObjectUrl) {
							URL.revokeObjectURL(ndviRasterObjectUrl)
						}
						ndviRasterObjectUrl = URL.createObjectURL(blob)
						showRasterMap(ndviRasterObjectUrl, selectedFarm, tileUrlTemplate, 'S1_SMI')
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Unable to load S1_SMI raster preview.'
						showNdviError(message)
					}
				})
			}
			if (s1SmiQueueButton) {
				s1SmiQueueButton.addEventListener('click', async () => {
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const queueOperation = resolveOperation('s1_smi_raster_queue')
					const bodyDateField = resolveBodyFieldName(queueOperation?.bodyFields ?? {}, 'date')
					const queryDateField = resolveParamName(queueOperation?.queryParams ?? [], 'date')
					const data = await runNdviRequest('queue S1_SMI raster', farmS1SmiRasterQueueUrl, {
						method: 'POST',
						body: bodyDateField ? buildNdviBody('s1_smi_raster_queue', { date: validation.date }) : null,
						query: !bodyDateField && queryDateField
							? buildNdviQuery('s1_smi_raster_queue', { date: validation.date })
							: undefined,
					})
					if (data) {
						const jobId = data?.job_id ?? data?.jobId ?? data?.id ?? null
						const toastMessage = jobId
							? `Queued S1_SMI raster job #${jobId}`
							: 'Queued S1_SMI raster job'
						toast(toastMessage)
						const facts = []
						pushFact(facts, 'Raster date', validation.date)
						pushFact(facts, 'Job ID', jobId)
						const card = renderResultCard({
							title: 'S1_SMI raster queue',
							level: 'success',
							summary: `${toastMessage}.`,
							facts,
							debug: data,
						})
						replaceNdviOutput(card)
					}
				})
			}
			if (s1SmiRefreshButton) {
				s1SmiRefreshButton.addEventListener('click', async () => {
					const data = await runNdviRequest('refresh S1_SMI', farmS1SmiRefreshUrl, {
						method: 'POST',
						body: buildNdviBody('s1_smi_refresh'),
					})
					if (data) {
						const jobId = data?.job_id ?? data?.jobId ?? data?.id ?? null
						const toastMessage = jobId
							? `Queued S1_SMI refresh job #${jobId}`
							: 'Queued S1_SMI refresh job'
						toast(toastMessage)
						const facts = []
						pushFact(facts, 'Job ID', jobId)
						const card = renderResultCard({
							title: 'S1_SMI refresh',
							level: 'success',
							summary: `${toastMessage}.`,
							facts,
							debug: data,
						})
						replaceNdviOutput(card)
					}
				})
			}
			if (s1SmiStateButton) {
				s1SmiStateButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (farmStateOutput) {
						farmStateOutput.hidden = false
					}
					if (farmStateContent) {
						farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading S1_SMI farm state...</div>'
					}
					const url = farmS1SmiFarmStateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const payload = await runNdviRequest('s1_smi farm state', url, {
							method: 'GET',
							returnRaw: true,
						})
						if (!payload) {
							if (farmStateContent) {
								farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Unable to load S1_SMI farm state.</div>'
							}
							return
						}
						const data = unwrapResponseData(payload?.data ?? payload)
						const state = data?.state ?? 'unknown'
						const meanS1Smi = data?.mean_s1_smi ?? null
						const maxS1Smi = data?.max_s1_smi ?? null
						const minS1Smi = data?.min_s1_smi ?? null
						const trend = data?.trend ?? null
						const interpretation = data?.interpretation ?? ''
						const action = data?.action ?? ''

						const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
						const formatNumber = typeof ndviUi.formatNumber === 'function' ? ndviUi.formatNumber : (v) => String(v)

						const stateLabels = {
							dry: 'Dry',
							moist: 'Moist',
							wet: 'Wet',
							unknown: 'Unknown',
						}
						const stateLevel = {
							dry: 'warning',
							moist: 'info',
							wet: 'success',
							unknown: 'info',
						}
						const facts = []
						pushFact(facts, 'State', stateLabels[state] ?? state)
						pushFact(facts, 'Mean S1_SMI', meanS1Smi !== null ? formatNumber(meanS1Smi, 3) : '-')
						pushFact(facts, 'Max S1_SMI', maxS1Smi !== null ? formatNumber(maxS1Smi, 3) : '-')
						pushFact(facts, 'Min S1_SMI', minS1Smi !== null ? formatNumber(minS1Smi, 3) : '-')
						pushFact(facts, 'Trend', trend !== null ? (trend >= 0 ? `+${formatNumber(trend, 4)}` : formatNumber(trend, 4)) : '-')
						const card = renderResultCard({
							title: 'S1_SMI Farm State',
							level: stateLevel[state] ?? 'info',
							badges: [
								stateLabels[state] ?? state,
							],
							summary: interpretation || `S1_SMI farm state: ${stateLabels[state] ?? state}`,
							callout: action || 'No action available',
							facts,
							debug: data,
						})
						if (farmStateContent) {
							farmStateContent.innerHTML = ''
							farmStateContent.appendChild(card)
						}
					} catch (error) {
						console.error('[farm_intelligence_platform] S1_SMI farm state error', error)
						if (farmStateContent) {
							farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Failed to load S1_SMI farm state.</div>'
						}
					}
				})
			}

			if (s3LstLatestButton) {
				s3LstLatestButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (ndviOutput) {
						ndviOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading latest S3_LST...</div>'
					}
					latestS3LstState = reduceLatestState(latestS3LstState, { type: 'request' })
					renderLatestCard(latestS3LstState, () => {}, 'Latest S3_LST')
					const payload = await runNdviRequest('latest S3_LST', farmS3LstLatestUrl, {
						method: 'GET',
						returnRaw: true,
					})
					if (!payload) {
						latestS3LstState = reduceLatestState(latestS3LstState, {
							type: 'failure',
							message: 'Unable to load latest S3_LST.',
						})
						renderLatestCard(latestS3LstState, () => {}, 'Latest S3_LST')
						return
					}
					latestS3LstState = reduceLatestState(latestS3LstState, { type: 'success', payload }, new Date())
					renderLatestCard(latestS3LstState, () => {}, 'Latest S3_LST')
				})
			}
			if (s3LstTimeseriesButton) {
				s3LstTimeseriesButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateTimeseriesInputs(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					timeseriesS3LstState = reduceTimeseriesState(
						timeseriesS3LstState,
						{ type: 'request' },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesS3LstState, () => {}, 'S3_LST timeseries')
					renderNdviCalendar(timeseriesS3LstState)
					const payload = await runNdviRequest('s3_lst_timeseries', farmS3LstTimeseriesUrl, {
						method: 'GET',
						query: buildNdviQuery('s3_lst_timeseries', {
							start: validation.start,
							end: validation.end,
						}),
						returnRaw: true,
					})
					if (!payload) {
						timeseriesS3LstState = reduceTimeseriesState(
							timeseriesS3LstState,
							{ type: 'failure', message: 'Unable to load S3_LST timeseries.' },
							validation.start,
							validation.end,
						)
						renderTimeseriesCard(timeseriesS3LstState, () => {}, 'S3_LST timeseries')
						renderNdviCalendar(timeseriesS3LstState)
						if (ndviTable) {
							ndviTable.textContent = ''
						}
						return
					}
					timeseriesS3LstState = reduceTimeseriesState(
						timeseriesS3LstState,
						{ type: 'success', payload },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesS3LstState, () => {}, 'S3_LST timeseries')
					renderNdviCalendar(timeseriesS3LstState)
					if (timeseriesS3LstState.status === NDVI_SERIES_STATE.has_data) {
						renderNdviTable(timeseriesS3LstState.vm?.points ?? [])
					} else if (ndviTable) {
						ndviTable.textContent = ''
					}
				})
			}
			if (s3LstRasterButton) {
				s3LstRasterButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const url = farmS3LstRasterUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const query = buildNdviQuery('s3_lst_raster', { date: validation.date })
					const queryString = buildQueryString(query)
					const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
					try {
						const tileUrlTemplate = farmS3LstRasterUrl
							.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
							.replace('/raster.png', '/tiles/{z}/{x}/{y}.png')
						const response = await fetch(ncGenerateUrl(finalUrl), {
							credentials: 'same-origin',
							headers: {
								'OCS-APIRequest': 'true',
								'X-Requested-With': 'XMLHttpRequest',
								requesttoken: resolveRequestToken() ?? '',
							},
						})
						if (!response.ok) {
							showNdviError(
								`Unable to load S3_LST raster preview (HTTP ${response.status}).`,
							)
							return
						}
						const contentType = response.headers.get('content-type') || ''
						if (!contentType.startsWith('image/')) {
							showNdviError(
								'S3_LST raster preview did not return an image.',
							)
							return
						}
						const blob = await response.blob()
						if (!blob || blob.size === 0) {
							showNdviError('S3_LST raster preview response was empty.')
							return
						}
						if (ndviRasterObjectUrl) {
							URL.revokeObjectURL(ndviRasterObjectUrl)
						}
						ndviRasterObjectUrl = URL.createObjectURL(blob)
						showRasterMap(ndviRasterObjectUrl, selectedFarm, tileUrlTemplate, 'S3_LST')
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Unable to load S3_LST raster preview.'
						showNdviError(message)
					}
				})
			}
			if (s3LstQueueButton) {
				s3LstQueueButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const queueOperation = resolveOperation('s3_lst_raster_queue')
					const bodyDateField = queueOperation?.bodyFields ? Object.keys(queueOperation.bodyFields).find(k => k.toLowerCase().includes('date')) : null
					try {
						const data = await runNdviRequest('queue S3_LST raster', farmS3LstRasterQueueUrl, {
							method: 'POST',
							body: bodyDateField ? buildNdviBody('s3_lst_raster_queue', { date: validation.date }) : null,
							query: !bodyDateField
								? buildNdviQuery('s3_lst_raster_queue', { date: validation.date })
								: undefined,
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued S3_LST raster job #${jobId}`
							: 'Queued S3_LST raster job'
						const card = renderResultCard({
							title: 'S3_LST raster queue',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to queue S3_LST raster.'
						showNdviError(message)
					}
				})
			}
			if (s3LstRefreshButton) {
				s3LstRefreshButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					try {
						const data = await runNdviRequest('refresh S3_LST', farmS3LstRefreshUrl, {
							method: 'POST',
							body: buildNdviBody('s3_lst_refresh'),
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued S3_LST refresh job #${jobId}`
							: 'Queued S3_LST refresh job'
						const card = renderResultCard({
							title: 'S3_LST refresh',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to refresh S3_LST.'
						showNdviError(message)
					}
				})
			}
			if (s3LstStateButton) {
				s3LstStateButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (farmStateOutput) {
						farmStateOutput.hidden = false
					}
					if (farmStateContent) {
						farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading S3_LST farm state...</div>'
					}
					const url = farmS3LstFarmStateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const payload = await runNdviRequest('s3_lst farm state', url, {
							method: 'GET',
							returnRaw: true,
						})
						if (!payload) {
							if (farmStateContent) {
								farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Unable to load S3_LST farm state.</div>'
							}
							return
						}
						const data = unwrapResponseData(payload?.data ?? payload)
						const state = data?.state ?? 'unknown'
						const meanS3Lst = data?.mean_lst ?? null
						const maxS3Lst = data?.max_lst ?? null
						const minS3Lst = data?.min_lst ?? null
						const trend = data?.trend ?? null
						const interpretation = data?.interpretation ?? ''
						const action = data?.action ?? ''

						const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
						const formatNumber = typeof ndviUi.formatNumber === 'function' ? ndviUi.formatNumber : (v) => String(v)

						const stateLabels = {
							cold: 'Cold',
							moderate: 'Moderate',
							hot: 'Hot',
							unknown: 'Unknown',
						}
						const stateLevel = {
							cold: 'info',
							moderate: 'success',
							hot: 'warning',
							unknown: 'info',
						}
						const facts = []
						pushFact(facts, 'State', stateLabels[state] ?? state)
						pushFact(facts, 'Mean S3_LST', meanS3Lst !== null ? formatNumber(meanS3Lst, 3) : '-')
						pushFact(facts, 'Max S3_LST', maxS3Lst !== null ? formatNumber(maxS3Lst, 3) : '-')
						pushFact(facts, 'Min S3_LST', minS3Lst !== null ? formatNumber(minS3Lst, 3) : '-')
						pushFact(facts, 'Trend', trend !== null ? (trend >= 0 ? `+${formatNumber(trend, 4)}` : formatNumber(trend, 4)) : '-')
						const card = renderResultCard({
							title: 'S3_LST Farm State',
							level: stateLevel[state] ?? 'info',
							badges: [
								stateLabels[state] ?? state,
							],
							summary: interpretation || `S3_LST farm state: ${stateLabels[state] ?? state}`,
							callout: action || 'No action available',
							facts,
							debug: data,
						})
						if (farmStateContent) {
							farmStateContent.innerHTML = ''
							farmStateContent.appendChild(card)
						}
					} catch (error) {
						console.error('[farm_intelligence_platform] S3_LST farm state error', error)
						if (farmStateContent) {
							farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Failed to load S3_LST farm state.</div>'
						}
					}
				})
			}
			if (landsatLstLatestButton) {
				landsatLstLatestButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (ndviOutput) {
						ndviOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading latest LANDSAT_LST...</div>'
					}
					latestLandsatLstState = reduceLatestState(latestLandsatLstState, { type: 'request' })
					renderLatestCard(latestLandsatLstState, () => {}, 'Latest LANDSAT_LST')
					const payload = await runNdviRequest('latest LANDSAT_LST', farmLandsatLstLatestUrl, {
						method: 'GET',
						returnRaw: true,
					})
					if (!payload) {
						latestLandsatLstState = reduceLatestState(latestLandsatLstState, {
							type: 'failure',
							message: 'Unable to load latest LANDSAT_LST.',
						})
						renderLatestCard(latestLandsatLstState, () => {}, 'Latest LANDSAT_LST')
						return
					}
					latestLandsatLstState = reduceLatestState(latestLandsatLstState, { type: 'success', payload }, new Date())
					renderLatestCard(latestLandsatLstState, () => {}, 'Latest LANDSAT_LST')
				})
			}
			if (landsatLstTimeseriesButton) {
				landsatLstTimeseriesButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateTimeseriesInputs(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					timeseriesLandsatLstState = reduceTimeseriesState(
						timeseriesLandsatLstState,
						{ type: 'request' },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesLandsatLstState, () => {}, 'LANDSAT_LST timeseries')
					renderNdviCalendar(timeseriesLandsatLstState)
					const payload = await runNdviRequest('landsat_lst_timeseries', farmLandsatLstTimeseriesUrl, {
						method: 'GET',
						query: buildNdviQuery('landsat_lst_timeseries', {
							start: validation.start,
							end: validation.end,
						}),
						returnRaw: true,
					})
					if (!payload) {
						timeseriesLandsatLstState = reduceTimeseriesState(
							timeseriesLandsatLstState,
							{ type: 'failure', message: 'Unable to load LANDSAT_LST timeseries.' },
							validation.start,
							validation.end,
						)
						renderTimeseriesCard(timeseriesLandsatLstState, () => {}, 'LANDSAT_LST timeseries')
						renderNdviCalendar(timeseriesLandsatLstState)
						if (ndviTable) {
							ndviTable.textContent = ''
						}
						return
					}
					timeseriesLandsatLstState = reduceTimeseriesState(
						timeseriesLandsatLstState,
						{ type: 'success', payload },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesLandsatLstState, () => {}, 'LANDSAT_LST timeseries')
					renderNdviCalendar(timeseriesLandsatLstState)
					if (timeseriesLandsatLstState.status === NDVI_SERIES_STATE.has_data) {
						renderNdviTable(timeseriesLandsatLstState.vm?.points ?? [])
					} else if (ndviTable) {
						ndviTable.textContent = ''
					}
				})
			}
			if (landsatLstRasterButton) {
				landsatLstRasterButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const url = farmLandsatLstRasterUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const query = buildNdviQuery('landsat_lst_raster', { date: validation.date })
					const queryString = buildQueryString(query)
					const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
					try {
						const tileUrlTemplate = farmLandsatLstRasterUrl
							.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
							.replace('/raster.png', '/tiles/{z}/{x}/{y}.png')
						const response = await fetch(ncGenerateUrl(finalUrl), {
							credentials: 'same-origin',
							headers: {
								'OCS-APIRequest': 'true',
								'X-Requested-With': 'XMLHttpRequest',
								requesttoken: resolveRequestToken() ?? '',
							},
						})
						if (!response.ok) {
							showNdviError(
								`Unable to load LANDSAT_LST raster preview (HTTP ${response.status}).`,
							)
							return
						}
						const contentType = response.headers.get('content-type') || ''
						if (!contentType.startsWith('image/')) {
							showNdviError(
								'LANDSAT_LST raster preview did not return an image.',
							)
							return
						}
						const blob = await response.blob()
						if (!blob || blob.size === 0) {
							showNdviError('LANDSAT_LST raster preview response was empty.')
							return
						}
						if (ndviRasterObjectUrl) {
							URL.revokeObjectURL(ndviRasterObjectUrl)
						}
						ndviRasterObjectUrl = URL.createObjectURL(blob)
						showRasterMap(ndviRasterObjectUrl, selectedFarm, tileUrlTemplate, 'LANDSAT_LST')
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Unable to load LANDSAT_LST raster preview.'
						showNdviError(message)
					}
				})
			}
			if (landsatLstQueueButton) {
				landsatLstQueueButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const queueOperation = resolveOperation('landsat_lst_raster_queue')
					const bodyDateField = queueOperation?.bodyFields ? Object.keys(queueOperation.bodyFields).find(k => k.toLowerCase().includes('date')) : null
					try {
						const data = await runNdviRequest('queue LANDSAT_LST raster', farmLandsatLstRasterQueueUrl, {
							method: 'POST',
							body: bodyDateField ? buildNdviBody('landsat_lst_raster_queue', { date: validation.date }) : null,
							query: !bodyDateField
								? buildNdviQuery('landsat_lst_raster_queue', { date: validation.date })
								: undefined,
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued LANDSAT_LST raster job #${jobId}`
							: 'Queued LANDSAT_LST raster job'
						const card = renderResultCard({
							title: 'LANDSAT_LST raster queue',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to queue LANDSAT_LST raster.'
						showNdviError(message)
					}
				})
			}
			if (landsatLstRefreshButton) {
				landsatLstRefreshButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					try {
						const data = await runNdviRequest('refresh LANDSAT_LST', farmLandsatLstRefreshUrl, {
							method: 'POST',
							body: buildNdviBody('landsat_lst_refresh'),
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued LANDSAT_LST refresh job #${jobId}`
							: 'Queued LANDSAT_LST refresh job'
						const card = renderResultCard({
							title: 'LANDSAT_LST refresh',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to refresh LANDSAT_LST.'
						showNdviError(message)
					}
				})
			}
			if (landsatLstStateButton) {
				landsatLstStateButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (farmStateOutput) {
						farmStateOutput.hidden = false
					}
					if (farmStateContent) {
						farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading LANDSAT_LST farm state...</div>'
					}
					const url = farmLandsatLstFarmStateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const payload = await runNdviRequest('landsat_lst farm state', url, {
							method: 'GET',
							returnRaw: true,
						})
						if (!payload) {
							if (farmStateContent) {
								farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Unable to load LANDSAT_LST farm state.</div>'
							}
							return
						}
						const data = unwrapResponseData(payload?.data ?? payload)
						const state = data?.state ?? 'unknown'
						const meanLandsatLst = data?.mean_lst ?? null
						const maxLandsatLst = data?.max_lst ?? null
						const minLandsatLst = data?.min_lst ?? null
						const trend = data?.trend ?? null
						const interpretation = data?.interpretation ?? ''
						const action = data?.action ?? ''

						const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
						const formatNumber = typeof ndviUi.formatNumber === 'function' ? ndviUi.formatNumber : (v) => String(v)

						const stateLabels = {
							cold: 'Cold',
							moderate: 'Moderate',
							hot: 'Hot',
							unknown: 'Unknown',
						}
						const stateLevel = {
							cold: 'info',
							moderate: 'success',
							hot: 'warning',
							unknown: 'info',
						}
						const facts = []
						pushFact(facts, 'State', stateLabels[state] ?? state)
						pushFact(facts, 'Mean LANDSAT_LST', meanLandsatLst !== null ? formatNumber(meanLandsatLst, 3) : '-')
						pushFact(facts, 'Max LANDSAT_LST', maxLandsatLst !== null ? formatNumber(maxLandsatLst, 3) : '-')
						pushFact(facts, 'Min LANDSAT_LST', minLandsatLst !== null ? formatNumber(minLandsatLst, 3) : '-')
						pushFact(facts, 'Trend', trend !== null ? (trend >= 0 ? `+${formatNumber(trend, 4)}` : formatNumber(trend, 4)) : '-')
						const card = renderResultCard({
							title: 'LANDSAT_LST Farm State',
							level: stateLevel[state] ?? 'info',
							badges: [
								stateLabels[state] ?? state,
							],
							summary: interpretation || `LANDSAT_LST farm state: ${stateLabels[state] ?? state}`,
							callout: action || 'No action available',
							facts,
							debug: data,
						})
						if (farmStateContent) {
							farmStateContent.innerHTML = ''
							farmStateContent.appendChild(card)
						}
					} catch (error) {
						console.error('[farm_intelligence_platform] LANDSAT_LST farm state error', error)
						if (farmStateContent) {
							farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Failed to load LANDSAT_LST farm state.</div>'
						}
					}
				})
			}
			if (ironOxideLatestButton) {
				ironOxideLatestButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (ndviOutput) {
						ndviOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading latest IRON_OXIDE...</div>'
					}
					latestIronOxideState = reduceLatestState(latestIronOxideState, { type: 'request' })
					renderLatestCard(latestIronOxideState, () => {}, 'Latest IRON_OXIDE')
					const payload = await runNdviRequest('latest IRON_OXIDE', farmIronOxideLatestUrl, {
						method: 'GET',
						returnRaw: true,
					})
					if (!payload) {
						latestIronOxideState = reduceLatestState(latestIronOxideState, {
							type: 'failure',
							message: 'Unable to load latest IRON_OXIDE.',
						})
						renderLatestCard(latestIronOxideState, () => {}, 'Latest IRON_OXIDE')
						return
					}
					latestIronOxideState = reduceLatestState(latestIronOxideState, { type: 'success', payload }, new Date())
					renderLatestCard(latestIronOxideState, () => {}, 'Latest IRON_OXIDE')
				})
			}
			if (ironOxideTimeseriesButton) {
				ironOxideTimeseriesButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateTimeseriesInputs(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					timeseriesIronOxideState = reduceTimeseriesState(
						timeseriesIronOxideState,
						{ type: 'request' },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesIronOxideState, () => {}, 'IRON_OXIDE timeseries')
					renderNdviCalendar(timeseriesIronOxideState)
					const payload = await runNdviRequest('iron_oxide_timeseries', farmIronOxideTimeseriesUrl, {
						method: 'GET',
						query: buildNdviQuery('iron_oxide_timeseries', {
							start: validation.start,
							end: validation.end,
						}),
						returnRaw: true,
					})
					if (!payload) {
						timeseriesIronOxideState = reduceTimeseriesState(
							timeseriesIronOxideState,
							{ type: 'failure', message: 'Unable to load IRON_OXIDE timeseries.' },
							validation.start,
							validation.end,
						)
						renderTimeseriesCard(timeseriesIronOxideState, () => {}, 'IRON_OXIDE timeseries')
						renderNdviCalendar(timeseriesIronOxideState)
						if (ndviTable) {
							ndviTable.textContent = ''
						}
						return
					}
					timeseriesIronOxideState = reduceTimeseriesState(
						timeseriesIronOxideState,
						{ type: 'success', payload },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesIronOxideState, () => {}, 'IRON_OXIDE timeseries')
					renderNdviCalendar(timeseriesIronOxideState)
					if (timeseriesIronOxideState.status === NDVI_SERIES_STATE.has_data) {
						renderNdviTable(timeseriesIronOxideState.vm?.points ?? [])
					} else if (ndviTable) {
						ndviTable.textContent = ''
					}
				})
			}
			if (ironOxideRasterButton) {
				ironOxideRasterButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const url = farmIronOxideRasterUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const query = buildNdviQuery('iron_oxide_raster', { date: validation.date })
					const queryString = buildQueryString(query)
					const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
					try {
						const tileUrlTemplate = farmIronOxideRasterUrl
							.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
							.replace('/raster.png', '/tiles/{z}/{x}/{y}.png')
						const response = await fetch(ncGenerateUrl(finalUrl), {
							credentials: 'same-origin',
							headers: {
								'OCS-APIRequest': 'true',
								'X-Requested-With': 'XMLHttpRequest',
								requesttoken: resolveRequestToken() ?? '',
							},
						})
						if (!response.ok) {
							showNdviError(
								`Unable to load IRON_OXIDE raster preview (HTTP ${response.status}).`,
							)
							return
						}
						const contentType = response.headers.get('content-type') || ''
						if (!contentType.startsWith('image/')) {
							showNdviError(
								'IRON_OXIDE raster preview did not return an image.',
							)
							return
						}
						const blob = await response.blob()
						if (!blob || blob.size === 0) {
							showNdviError('IRON_OXIDE raster preview response was empty.')
							return
						}
						if (ndviRasterObjectUrl) {
							URL.revokeObjectURL(ndviRasterObjectUrl)
						}
						ndviRasterObjectUrl = URL.createObjectURL(blob)
						showRasterMap(ndviRasterObjectUrl, selectedFarm, tileUrlTemplate, 'IRON_OXIDE')
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Unable to load IRON_OXIDE raster preview.'
						showNdviError(message)
					}
				})
			}
			if (ironOxideQueueButton) {
				ironOxideQueueButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const queueOperation = resolveOperation('iron_oxide_raster_queue')
					const bodyDateField = queueOperation?.bodyFields ? Object.keys(queueOperation.bodyFields).find(k => k.toLowerCase().includes('date')) : null
					try {
						const data = await runNdviRequest('queue IRON_OXIDE raster', farmIronOxideRasterQueueUrl, {
							method: 'POST',
							body: bodyDateField ? buildNdviBody('iron_oxide_raster_queue', { date: validation.date }) : null,
							query: !bodyDateField
								? buildNdviQuery('iron_oxide_raster_queue', { date: validation.date })
								: undefined,
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued IRON_OXIDE raster job #${jobId}`
							: 'Queued IRON_OXIDE raster job'
						const card = renderResultCard({
							title: 'IRON_OXIDE raster queue',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to queue IRON_OXIDE raster.'
						showNdviError(message)
					}
				})
			}
			if (ironOxideRefreshButton) {
				ironOxideRefreshButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					try {
						const data = await runNdviRequest('refresh IRON_OXIDE', farmIronOxideRefreshUrl, {
							method: 'POST',
							body: buildNdviBody('iron_oxide_refresh'),
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued IRON_OXIDE refresh job #${jobId}`
							: 'Queued IRON_OXIDE refresh job'
						const card = renderResultCard({
							title: 'IRON_OXIDE refresh',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to refresh IRON_OXIDE.'
						showNdviError(message)
					}
				})
			}
			if (ironOxideStateButton) {
				ironOxideStateButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (farmStateOutput) {
						farmStateOutput.hidden = false
					}
					if (farmStateContent) {
						farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading IRON_OXIDE farm state...</div>'
					}
					const url = farmIronOxideFarmStateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const payload = await runNdviRequest('iron_oxide farm state', url, {
							method: 'GET',
							returnRaw: true,
						})
						if (!payload) {
							if (farmStateContent) {
								farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Unable to load IRON_OXIDE farm state.</div>'
							}
							return
						}
						const data = unwrapResponseData(payload?.data ?? payload)
						const state = data?.state ?? 'unknown'
						const meanIronOxide = data?.mean_iron_oxide ?? null
						const maxIronOxide = data?.max_iron_oxide ?? null
						const minIronOxide = data?.min_iron_oxide ?? null
						const trend = data?.trend ?? null
						const interpretation = data?.interpretation ?? ''
						const action = data?.action ?? ''

						const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
						const formatNumber = typeof ndviUi.formatNumber === 'function' ? ndviUi.formatNumber : (v) => String(v)

						const stateLabels = {
							high: 'High',
							moderate: 'Moderate',
							low: 'Low',
							declining: 'Declining',
							unknown: 'Unknown',
						}
						const stateLevel = {
							high: 'warning',
							moderate: 'success',
							low: 'info',
							declining: 'warning',
							unknown: 'info',
						}
						const facts = []
						pushFact(facts, 'State', stateLabels[state] ?? state)
						pushFact(facts, 'Mean IRON_OXIDE', meanIronOxide !== null ? formatNumber(meanIronOxide, 3) : '-')
						pushFact(facts, 'Max IRON_OXIDE', maxIronOxide !== null ? formatNumber(maxIronOxide, 3) : '-')
						pushFact(facts, 'Min IRON_OXIDE', minIronOxide !== null ? formatNumber(minIronOxide, 3) : '-')
						pushFact(facts, 'Trend', trend !== null ? (trend >= 0 ? `+${formatNumber(trend, 4)}` : formatNumber(trend, 4)) : '-')
						const card = renderResultCard({
							title: 'IRON_OXIDE Farm State',
							level: stateLevel[state] ?? 'info',
							badges: [
								stateLabels[state] ?? state,
							],
							summary: interpretation || `IRON_OXIDE farm state: ${stateLabels[state] ?? state}`,
							callout: action || 'No action available',
							facts,
							debug: data,
						})
						if (farmStateContent) {
							farmStateContent.innerHTML = ''
							farmStateContent.appendChild(card)
						}
					} catch (error) {
						console.error('[farm_intelligence_platform] IRON_OXIDE farm state error', error)
						if (farmStateContent) {
							farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Failed to load IRON_OXIDE farm state.</div>'
						}
					}
				})
			}
			if (eviLatestButton) {
				eviLatestButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (ndviOutput) {
						ndviOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading latest EVI...</div>'
					}
					latestEviState = reduceLatestState(latestEviState, { type: 'request' })
					renderLatestCard(latestEviState, () => {}, 'Latest EVI')
					const payload = await runNdviRequest('latest EVI', farmEviLatestUrl, {
						method: 'GET',
						returnRaw: true,
					})
					if (!payload) {
						latestEviState = reduceLatestState(latestEviState, {
							type: 'failure',
							message: 'Unable to load latest EVI.',
						})
						renderLatestCard(latestEviState, () => {}, 'Latest EVI')
						return
					}
					latestEviState = reduceLatestState(latestEviState, { type: 'success', payload }, new Date())
					renderLatestCard(latestEviState, () => {}, 'Latest EVI')
				})
			}
			if (eviTimeseriesButton) {
				eviTimeseriesButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateTimeseriesInputs(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					timeseriesEviState = reduceTimeseriesState(
						timeseriesEviState,
						{ type: 'request' },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesEviState, () => {}, 'EVI timeseries')
					renderNdviCalendar(timeseriesEviState)
					const payload = await runNdviRequest('evi_timeseries', farmEviTimeseriesUrl, {
						method: 'GET',
						query: buildNdviQuery('evi_timeseries', {
							start: validation.start,
							end: validation.end,
						}),
						returnRaw: true,
					})
					if (!payload) {
						timeseriesEviState = reduceTimeseriesState(
							timeseriesEviState,
							{ type: 'failure', message: 'Unable to load EVI timeseries.' },
							validation.start,
							validation.end,
						)
						renderTimeseriesCard(timeseriesEviState, () => {}, 'EVI timeseries')
						renderNdviCalendar(timeseriesEviState)
						if (ndviTable) {
							ndviTable.textContent = ''
						}
						return
					}
					timeseriesEviState = reduceTimeseriesState(
						timeseriesEviState,
						{ type: 'success', payload },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesEviState, () => {}, 'EVI timeseries')
					renderNdviCalendar(timeseriesEviState)
					if (timeseriesEviState.status === NDVI_SERIES_STATE.has_data) {
						renderNdviTable(timeseriesEviState.vm?.points ?? [])
					} else if (ndviTable) {
						ndviTable.textContent = ''
					}
				})
			}
			if (eviRasterButton) {
				eviRasterButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const url = farmEviRasterUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const query = buildNdviQuery('evi_raster', { date: validation.date })
					const queryString = buildQueryString(query)
					const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
					try {
						const tileUrlTemplate = farmEviRasterUrl
							.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
							.replace('/raster.png', '/tiles/{z}/{x}/{y}.png')
						const response = await fetch(ncGenerateUrl(finalUrl), {
							credentials: 'same-origin',
							headers: {
								'OCS-APIRequest': 'true',
								'X-Requested-With': 'XMLHttpRequest',
								requesttoken: resolveRequestToken() ?? '',
							},
						})
						if (!response.ok) {
							showNdviError(
								`Unable to load EVI raster preview (HTTP ${response.status}).`,
							)
							return
						}
						const contentType = response.headers.get('content-type') || ''
						if (!contentType.startsWith('image/')) {
							showNdviError(
								'EVI raster preview did not return an image.',
							)
							return
						}
						const blob = await response.blob()
						if (!blob || blob.size === 0) {
							showNdviError('EVI raster preview response was empty.')
							return
						}
						if (ndviRasterObjectUrl) {
							URL.revokeObjectURL(ndviRasterObjectUrl)
						}
						ndviRasterObjectUrl = URL.createObjectURL(blob)
						showRasterMap(ndviRasterObjectUrl, selectedFarm, tileUrlTemplate, 'EVI')
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Unable to load EVI raster preview.'
						showNdviError(message)
					}
				})
			}
			if (eviQueueButton) {
				eviQueueButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const queueOperation = resolveOperation('evi_raster_queue')
					const bodyDateField = queueOperation?.bodyFields ? Object.keys(queueOperation.bodyFields).find(k => k.toLowerCase().includes('date')) : null
					try {
						const data = await runNdviRequest('queue EVI raster', farmEviRasterQueueUrl, {
							method: 'POST',
							body: bodyDateField ? buildNdviBody('evi_raster_queue', { date: validation.date }) : null,
							query: !bodyDateField
								? buildNdviQuery('evi_raster_queue', { date: validation.date })
								: undefined,
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued EVI raster job #${jobId}`
							: 'Queued EVI raster job'
						const card = renderResultCard({
							title: 'EVI raster queue',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to queue EVI raster.'
						showNdviError(message)
					}
				})
			}
			if (eviRefreshButton) {
				eviRefreshButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					try {
						const data = await runNdviRequest('refresh EVI', farmEviRefreshUrl, {
							method: 'POST',
							body: buildNdviBody('evi_refresh'),
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued EVI refresh job #${jobId}`
							: 'Queued EVI refresh job'
						const card = renderResultCard({
							title: 'EVI refresh',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to refresh EVI.'
						showNdviError(message)
					}
				})
			}
			if (eviStateButton) {
				eviStateButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (farmStateOutput) {
						farmStateOutput.hidden = false
					}
					if (farmStateContent) {
						farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading EVI farm state...</div>'
					}
					const url = farmEviFarmStateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const payload = await runNdviRequest('evi farm state', url, {
							method: 'GET',
							returnRaw: true,
						})
						if (!payload) {
							if (farmStateContent) {
								farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Unable to load EVI farm state.</div>'
							}
							return
						}
						const data = unwrapResponseData(payload?.data ?? payload)
						const state = data?.state ?? 'unknown'
						const meanEvi = data?.mean_evi ?? null
						const maxEvi = data?.max_evi ?? null
						const minEvi = data?.min_evi ?? null
						const trend = data?.trend ?? null
						const interpretation = data?.interpretation ?? ''
						const action = data?.action ?? ''

						const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
						const formatNumber = typeof ndviUi.formatNumber === 'function' ? ndviUi.formatNumber : (v) => String(v)

						const stateLabels = {
							high: 'High',
							moderate: 'Moderate',
							low: 'Low',
							declining: 'Declining',
							unknown: 'Unknown',
						}
						const stateLevel = {
							high: 'success',
							moderate: 'success',
							low: 'warning',
							declining: 'warning',
							unknown: 'info',
						}
						const facts = []
						pushFact(facts, 'State', stateLabels[state] ?? state)
						pushFact(facts, 'Mean EVI', meanEvi !== null ? formatNumber(meanEvi, 3) : '-')
						pushFact(facts, 'Max EVI', maxEvi !== null ? formatNumber(maxEvi, 3) : '-')
						pushFact(facts, 'Min EVI', minEvi !== null ? formatNumber(minEvi, 3) : '-')
						pushFact(facts, 'Trend', trend !== null ? (trend >= 0 ? `+${formatNumber(trend, 4)}` : formatNumber(trend, 4)) : '-')
						const card = renderResultCard({
							title: 'EVI Farm State',
							level: stateLevel[state] ?? 'info',
							badges: [
								stateLabels[state] ?? state,
							],
							summary: interpretation || `EVI farm state: ${stateLabels[state] ?? state}`,
							callout: action || 'No action available',
							facts,
							debug: data,
						})
						if (farmStateContent) {
							farmStateContent.innerHTML = ''
							farmStateContent.appendChild(card)
						}
					} catch (error) {
						console.error('[farm_intelligence_platform] EVI farm state error', error)
						if (farmStateContent) {
							farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Failed to load EVI farm state.</div>'
						}
					}
				})
			}

			if (lRviLatestButton) {
				lRviLatestButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (ndviOutput) {
						ndviOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading latest L_RVI...</div>'
					}
					latestLRviState = reduceLatestState(latestLRviState, { type: 'request' })
					renderLatestCard(latestLRviState, () => {}, 'Latest L_RVI')
					const payload = await runNdviRequest('latest L_RVI', farmLRviLatestUrl, {
						method: 'GET',
						returnRaw: true,
					})
					if (!payload) {
						latestLRviState = reduceLatestState(latestLRviState, {
							type: 'failure',
							message: 'Unable to load latest L_RVI.',
						})
						renderLatestCard(latestLRviState, () => {}, 'Latest L_RVI')
						return
					}
					latestLRviState = reduceLatestState(latestLRviState, { type: 'success', payload }, new Date())
					renderLatestCard(latestLRviState, () => {}, 'Latest L_RVI')
				})
			}
			if (lRviTimeseriesButton) {
				lRviTimeseriesButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateTimeseriesInputs(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					timeseriesLRviState = reduceTimeseriesState(
						timeseriesLRviState,
						{ type: 'request' },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesLRviState, () => {}, 'L_RVI timeseries')
					renderNdviCalendar(timeseriesLRviState)
					const payload = await runNdviRequest('l_rvi_timeseries', farmLRviTimeseriesUrl, {
						method: 'GET',
						query: buildNdviQuery('l_rvi_timeseries', {
							start: validation.start,
							end: validation.end,
						}),
						returnRaw: true,
					})
					if (!payload) {
						timeseriesLRviState = reduceTimeseriesState(
							timeseriesLRviState,
							{ type: 'failure', message: 'Unable to load L_RVI timeseries.' },
							validation.start,
							validation.end,
						)
						renderTimeseriesCard(timeseriesLRviState, () => {}, 'L_RVI timeseries')
						renderNdviCalendar(timeseriesLRviState)
						if (ndviTable) {
							ndviTable.textContent = ''
						}
						return
					}
					timeseriesLRviState = reduceTimeseriesState(
						timeseriesLRviState,
						{ type: 'success', payload },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesLRviState, () => {}, 'L_RVI timeseries')
					renderNdviCalendar(timeseriesLRviState)
					if (timeseriesLRviState.status === NDVI_SERIES_STATE.has_data) {
						renderNdviTable(timeseriesLRviState.vm?.points ?? [])
					} else if (ndviTable) {
						ndviTable.textContent = ''
					}
				})
			}
			if (lRviRasterButton) {
				lRviRasterButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const url = farmLRviRasterUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const query = buildNdviQuery('l_rvi_raster', { date: validation.date })
					const queryString = buildQueryString(query)
					const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
					try {
						const tileUrlTemplate = farmLRviRasterUrl
							.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
							.replace('/raster.png', '/tiles/{z}/{x}/{y}.png')
						const response = await fetch(ncGenerateUrl(finalUrl), {
							credentials: 'same-origin',
							headers: {
								'OCS-APIRequest': 'true',
								'X-Requested-With': 'XMLHttpRequest',
								requesttoken: resolveRequestToken() ?? '',
							},
						})
						if (!response.ok) {
							showNdviError(
								`Unable to load L_RVI raster preview (HTTP ${response.status}).`,
							)
							return
						}
						const contentType = response.headers.get('content-type') || ''
						if (!contentType.startsWith('image/')) {
							showNdviError(
								'L_RVI raster preview did not return an image.',
							)
							return
						}
						const blob = await response.blob()
						if (!blob || blob.size === 0) {
							showNdviError('L_RVI raster preview response was empty.')
							return
						}
						if (ndviRasterObjectUrl) {
							URL.revokeObjectURL(ndviRasterObjectUrl)
						}
						ndviRasterObjectUrl = URL.createObjectURL(blob)
						showRasterMap(ndviRasterObjectUrl, selectedFarm, tileUrlTemplate, 'L_RVI')
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Unable to load L_RVI raster preview.'
						showNdviError(message)
					}
				})
			}
			if (lRviQueueButton) {
				lRviQueueButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const queueOperation = resolveOperation('l_rvi_raster_queue')
					const bodyDateField = queueOperation?.bodyFields ? Object.keys(queueOperation.bodyFields).find(k => k.toLowerCase().includes('date')) : null
					try {
						const data = await runNdviRequest('queue L_RVI raster', farmLRviRasterQueueUrl, {
							method: 'POST',
							body: bodyDateField ? buildNdviBody('l_rvi_raster_queue', { date: validation.date }) : null,
							query: !bodyDateField
								? buildNdviQuery('l_rvi_raster_queue', { date: validation.date })
								: undefined,
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued L_RVI raster job #${jobId}`
							: 'Queued L_RVI raster job'
						const card = renderResultCard({
							title: 'L_RVI raster queue',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to queue L_RVI raster.'
						showNdviError(message)
					}
				})
			}
			if (lRviRefreshButton) {
				lRviRefreshButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					try {
						const data = await runNdviRequest('refresh L_RVI', farmLRviRefreshUrl, {
							method: 'POST',
							body: buildNdviBody('l_rvi_refresh'),
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued L_RVI refresh job #${jobId}`
							: 'Queued L_RVI refresh job'
						const card = renderResultCard({
							title: 'L_RVI refresh',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to refresh L_RVI.'
						showNdviError(message)
					}
				})
			}
			if (lRviStateButton) {
				lRviStateButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (farmStateOutput) {
						farmStateOutput.hidden = false
					}
					if (farmStateContent) {
						farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading L_RVI farm state...</div>'
					}
					const url = farmLRviFarmStateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const payload = await runNdviRequest('l_rvi farm state', url, {
							method: 'GET',
							returnRaw: true,
						})
						if (!payload) {
							if (farmStateContent) {
								farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Unable to load L_RVI farm state.</div>'
							}
							return
						}
						const data = unwrapResponseData(payload?.data ?? payload)
						const state = data?.state ?? 'unknown'
						const meanLRvi = data?.mean_l_rvi ?? null
						const maxLRvi = data?.max_l_rvi ?? null
						const minLRvi = data?.min_l_rvi ?? null
						const trend = data?.trend ?? null
						const interpretation = data?.interpretation ?? ''
						const action = data?.action ?? ''

						const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
						const formatNumber = typeof ndviUi.formatNumber === 'function' ? ndviUi.formatNumber : (v) => String(v)

						const stateLabels = {
							high: 'High',
							moderate: 'Moderate',
							low: 'Low',
							declining: 'Declining',
							unknown: 'Unknown',
						}
						const stateLevel = {
							high: 'success',
							moderate: 'success',
							low: 'warning',
							declining: 'warning',
							unknown: 'info',
						}
						const facts = []
						pushFact(facts, 'State', stateLabels[state] ?? state)
						pushFact(facts, 'Mean L_RVI', meanLRvi !== null ? formatNumber(meanLRvi, 3) : '-')
						pushFact(facts, 'Max L_RVI', maxLRvi !== null ? formatNumber(maxLRvi, 3) : '-')
						pushFact(facts, 'Min L_RVI', minLRvi !== null ? formatNumber(minLRvi, 3) : '-')
						pushFact(facts, 'Trend', trend !== null ? (trend >= 0 ? `+${formatNumber(trend, 4)}` : formatNumber(trend, 4)) : '-')
						const card = renderResultCard({
							title: 'L_RVI Farm State',
							level: stateLevel[state] ?? 'info',
							badges: [
								stateLabels[state] ?? state,
							],
							summary: interpretation || `L_RVI farm state: ${stateLabels[state] ?? state}`,
							callout: action || 'No action available',
							facts,
							debug: data,
						})
						if (farmStateContent) {
							farmStateContent.innerHTML = ''
							farmStateContent.appendChild(card)
						}
					} catch (error) {
						console.error('[farm_intelligence_platform] L_RVI farm state error', error)
						if (farmStateContent) {
							farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Failed to load L_RVI farm state.</div>'
						}
					}
				})
			}

			if (nisarSmiLatestButton) {
				nisarSmiLatestButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (ndviOutput) {
						ndviOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading latest NISAR_SMI...</div>'
					}
					latestNisarSmiState = reduceLatestState(latestNisarSmiState, { type: 'request' })
					renderLatestCard(latestNisarSmiState, () => {}, 'Latest NISAR_SMI')
					const payload = await runNdviRequest('latest NISAR_SMI', farmNisarSmiLatestUrl, {
						method: 'GET',
						returnRaw: true,
					})
					if (!payload) {
						latestNisarSmiState = reduceLatestState(latestNisarSmiState, {
							type: 'failure',
							message: 'Unable to load latest NISAR_SMI.',
						})
						renderLatestCard(latestNisarSmiState, () => {}, 'Latest NISAR_SMI')
						return
					}
					latestNisarSmiState = reduceLatestState(latestNisarSmiState, { type: 'success', payload }, new Date())
					renderLatestCard(latestNisarSmiState, () => {}, 'Latest NISAR_SMI')
				})
			}
			if (nisarSmiTimeseriesButton) {
				nisarSmiTimeseriesButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateTimeseriesInputs(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					timeseriesNisarSmiState = reduceTimeseriesState(
						timeseriesNisarSmiState,
						{ type: 'request' },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesNisarSmiState, () => {}, 'NISAR_SMI timeseries')
					renderNdviCalendar(timeseriesNisarSmiState)
					const payload = await runNdviRequest('nisar_smi_timeseries', farmNisarSmiTimeseriesUrl, {
						method: 'GET',
						query: buildNdviQuery('nisar_smi_timeseries', {
							start: validation.start,
							end: validation.end,
						}),
						returnRaw: true,
					})
					if (!payload) {
						timeseriesNisarSmiState = reduceTimeseriesState(
							timeseriesNisarSmiState,
							{ type: 'failure', message: 'Unable to load NISAR_SMI timeseries.' },
							validation.start,
							validation.end,
						)
						renderTimeseriesCard(timeseriesNisarSmiState, () => {}, 'NISAR_SMI timeseries')
						renderNdviCalendar(timeseriesNisarSmiState)
						if (ndviTable) {
							ndviTable.textContent = ''
						}
						return
					}
					timeseriesNisarSmiState = reduceTimeseriesState(
						timeseriesNisarSmiState,
						{ type: 'success', payload },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesNisarSmiState, () => {}, 'NISAR_SMI timeseries')
					renderNdviCalendar(timeseriesNisarSmiState)
					if (timeseriesNisarSmiState.status === NDVI_SERIES_STATE.has_data) {
						renderNdviTable(timeseriesNisarSmiState.vm?.points ?? [])
					} else if (ndviTable) {
						ndviTable.textContent = ''
					}
				})
			}
			if (nisarSmiRasterButton) {
				nisarSmiRasterButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const url = farmNisarSmiRasterUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const query = buildNdviQuery('nisar_smi_raster', { date: validation.date })
					const queryString = buildQueryString(query)
					const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
					try {
						const tileUrlTemplate = farmNisarSmiRasterUrl
							.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
							.replace('/raster.png', '/tiles/{z}/{x}/{y}.png')
						const response = await fetch(ncGenerateUrl(finalUrl), {
							credentials: 'same-origin',
							headers: {
								'OCS-APIRequest': 'true',
								'X-Requested-With': 'XMLHttpRequest',
								requesttoken: resolveRequestToken() ?? '',
							},
						})
						if (!response.ok) {
							showNdviError(
								`Unable to load NISAR_SMI raster preview (HTTP ${response.status}).`,
							)
							return
						}
						const contentType = response.headers.get('content-type') || ''
						if (!contentType.startsWith('image/')) {
							showNdviError(
								'NISAR_SMI raster preview did not return an image.',
							)
							return
						}
						const blob = await response.blob()
						if (!blob || blob.size === 0) {
							showNdviError('NISAR_SMI raster preview response was empty.')
							return
						}
						if (ndviRasterObjectUrl) {
							URL.revokeObjectURL(ndviRasterObjectUrl)
						}
						ndviRasterObjectUrl = URL.createObjectURL(blob)
						showRasterMap(ndviRasterObjectUrl, selectedFarm, tileUrlTemplate, 'NISAR_SMI')
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Unable to load NISAR_SMI raster preview.'
						showNdviError(message)
					}
				})
			}
			if (nisarSmiQueueButton) {
				nisarSmiQueueButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const queueOperation = resolveOperation('nisar_smi_raster_queue')
					const bodyDateField = queueOperation?.bodyFields ? Object.keys(queueOperation.bodyFields).find(k => k.toLowerCase().includes('date')) : null
					try {
						const data = await runNdviRequest('queue NISAR_SMI raster', farmNisarSmiRasterQueueUrl, {
							method: 'POST',
							body: bodyDateField ? buildNdviBody('nisar_smi_raster_queue', { date: validation.date }) : null,
							query: !bodyDateField
								? buildNdviQuery('nisar_smi_raster_queue', { date: validation.date })
								: undefined,
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued NISAR_SMI raster job #${jobId}`
							: 'Queued NISAR_SMI raster job'
						const card = renderResultCard({
							title: 'NISAR_SMI raster queue',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to queue NISAR_SMI raster.'
						showNdviError(message)
					}
				})
			}
			if (nisarSmiRefreshButton) {
				nisarSmiRefreshButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					try {
						const data = await runNdviRequest('refresh NISAR_SMI', farmNisarSmiRefreshUrl, {
							method: 'POST',
							body: buildNdviBody('nisar_smi_refresh'),
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued NISAR_SMI refresh job #${jobId}`
							: 'Queued NISAR_SMI refresh job'
						const card = renderResultCard({
							title: 'NISAR_SMI refresh',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to refresh NISAR_SMI.'
						showNdviError(message)
					}
				})
			}
			if (nisarSmiStateButton) {
				nisarSmiStateButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (farmStateOutput) {
						farmStateOutput.hidden = false
					}
					if (farmStateContent) {
						farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading NISAR_SMI farm state...</div>'
					}
					const url = farmNisarSmiFarmStateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const payload = await runNdviRequest('nisar_smi farm state', url, {
							method: 'GET',
							returnRaw: true,
						})
						if (!payload) {
							if (farmStateContent) {
								farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Unable to load NISAR_SMI farm state.</div>'
							}
							return
						}
						const data = unwrapResponseData(payload?.data ?? payload)
						const state = data?.state ?? 'unknown'
						const meanNisarSmi = data?.mean_nisar_smi ?? null
						const maxNisarSmi = data?.max_nisar_smi ?? null
						const minNisarSmi = data?.min_nisar_smi ?? null
						const trend = data?.trend ?? null
						const interpretation = data?.interpretation ?? ''
						const action = data?.action ?? ''

						const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
						const formatNumber = typeof ndviUi.formatNumber === 'function' ? ndviUi.formatNumber : (v) => String(v)

						const stateLabels = {
							high: 'High',
							moderate: 'Moderate',
							low: 'Low',
							declining: 'Declining',
							unknown: 'Unknown',
						}
						const stateLevel = {
							high: 'success',
							moderate: 'success',
							low: 'warning',
							declining: 'warning',
							unknown: 'info',
						}
						const facts = []
						pushFact(facts, 'State', stateLabels[state] ?? state)
						pushFact(facts, 'Mean NISAR_SMI', meanNisarSmi !== null ? formatNumber(meanNisarSmi, 3) : '-')
						pushFact(facts, 'Max NISAR_SMI', maxNisarSmi !== null ? formatNumber(maxNisarSmi, 3) : '-')
						pushFact(facts, 'Min NISAR_SMI', minNisarSmi !== null ? formatNumber(minNisarSmi, 3) : '-')
						pushFact(facts, 'Trend', trend !== null ? (trend >= 0 ? `+${formatNumber(trend, 4)}` : formatNumber(trend, 4)) : '-')
						const card = renderResultCard({
							title: 'NISAR_SMI Farm State',
							level: stateLevel[state] ?? 'info',
							badges: [
								stateLabels[state] ?? state,
							],
							summary: interpretation || `NISAR_SMI farm state: ${stateLabels[state] ?? state}`,
							callout: action || 'No action available',
							facts,
							debug: data,
						})
						if (farmStateContent) {
							farmStateContent.innerHTML = ''
							farmStateContent.appendChild(card)
						}
					} catch (error) {
						console.error('[farm_intelligence_platform] NISAR_SMI farm state error', error)
						if (farmStateContent) {
							farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Failed to load NISAR_SMI farm state.</div>'
						}
					}
				})
			}

			const downloadGeotiff = async (urlTemplate, label) => {
				clearFarmsNotes()
				clearNdviError()
				if (!selectedFarm) {
					showNdviError('Select a farm first.')
					return
				}
				const state = readNdviDateState()
				const validation = validateRasterInput(state)
				if (!validation.ok) {
					showNdviError(validation.message)
					return
				}
				const url = urlTemplate.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
				const query = buildNdviQuery('geotiff_download', { date: validation.date })
				const queryString = buildQueryString(query)
				const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
				const resolvedUrl = ncGenerateUrl(finalUrl)
				try {
					const response = await fetch(resolvedUrl, {
						method: 'GET',
						credentials: 'same-origin',
						headers: {
							Accept: 'application/zip',
							'OCS-APIRequest': 'true',
							'X-Requested-With': 'XMLHttpRequest',
						},
					})
					if (!response.ok) {
						const result = await readJsonResponse(response)
						const message = buildNdviErrorMessage(response, result.data, `Unable to download ${label} GeoTIFF.`, result.text)
						showNdviError(message)
						if (shouldToastNdviError(message)) toast(message)
						return
					}
					const blob = await response.blob()
					if (blob.size === 0) {
						showNdviError(`${label} GeoTIFF response was empty.`)
						return
					}
					const link = document.createElement('a')
					link.href = URL.createObjectURL(blob)
					const filename = `${label.toLowerCase()}_${selectedFarm.id}_${validation.date}.zip`
					link.download = filename
					document.body.appendChild(link)
					link.click()
					document.body.removeChild(link)
					URL.revokeObjectURL(link.href)
				} catch (error) {
					const message = error instanceof Error ? error.message : `Unable to download ${label} GeoTIFF.`
					showNdviError(message)
				}
			}
			if (ndviGeotiffButton) {
				ndviGeotiffButton.addEventListener('click', () => downloadGeotiff(farmNdviGeotiffUrl, 'NDVI'))
			}
			if (ndwiGeotiffButton) {
				ndwiGeotiffButton.addEventListener('click', () => downloadGeotiff(farmNdwiGeotiffUrl, 'NDWI'))
			}
			if (ndmiGeotiffButton) {
				ndmiGeotiffButton.addEventListener('click', () => downloadGeotiff(farmNdmiGeotiffUrl, 'NDMI'))
			}
			if (rviGeotiffButton) {
				rviGeotiffButton.addEventListener('click', () => downloadGeotiff(farmRviGeotiffUrl, 'RVI'))
			}
			if (s1SmiGeotiffButton) {
				s1SmiGeotiffButton.addEventListener('click', () => downloadGeotiff(farmS1SmiGeotiffUrl, 'S1_SMI'))
			}
			if (s3LstGeotiffButton) {
				s3LstGeotiffButton.addEventListener('click', () => downloadGeotiff(farmS3LstGeotiffUrl, 'S3_LST'))
			}
			if (landsatLstGeotiffButton) {
				landsatLstGeotiffButton.addEventListener('click', () => downloadGeotiff(farmLandsatLstGeotiffUrl, 'LANDSAT_LST'))
			}
			if (ironOxideGeotiffButton) {
				ironOxideGeotiffButton.addEventListener('click', () => downloadGeotiff(farmIronOxideGeotiffUrl, 'IRON_OXIDE'))
			}
			if (eviGeotiffButton) {
				eviGeotiffButton.addEventListener('click', () => downloadGeotiff(farmEviGeotiffUrl, 'EVI'))
			}
			if (lRviGeotiffButton) {
				lRviGeotiffButton.addEventListener('click', () => downloadGeotiff(farmLRviGeotiffUrl, 'L_RVI'))
			}
			if (nisarSmiGeotiffButton) {
				nisarSmiGeotiffButton.addEventListener('click', () => downloadGeotiff(farmNisarSmiGeotiffUrl, 'NISAR_SMI'))
			}

			if (ndreLatestButton) {
				ndreLatestButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (ndviOutput) {
						ndviOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading latest NDRE...</div>'
					}
					latestNdreState = reduceLatestState(latestNdreState, { type: 'request' })
					renderLatestCard(latestNdreState, () => {}, 'Latest NDRE')
					const payload = await runNdviRequest('latest NDRE', farmNdreLatestUrl, {
						method: 'GET',
						returnRaw: true,
					})
					if (!payload) {
						latestNdreState = reduceLatestState(latestNdreState, {
							type: 'failure',
							message: 'Unable to load latest NDRE.',
						})
						renderLatestCard(latestNdreState, () => {}, 'Latest NDRE')
						return
					}
					latestNdreState = reduceLatestState(latestNdreState, { type: 'success', payload }, new Date())
					renderLatestCard(latestNdreState, () => {}, 'Latest NDRE')
				})
			}
			if (ndreTimeseriesButton) {
				ndreTimeseriesButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateTimeseriesInputs(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					timeseriesNdreState = reduceTimeseriesState(
						timeseriesNdreState,
						{ type: 'request' },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesNdreState, () => {}, 'NDRE timeseries')
					renderNdviCalendar(timeseriesNdreState)
					const payload = await runNdviRequest('ndre_timeseries', farmNdreTimeseriesUrl, {
						method: 'GET',
						query: buildNdviQuery('ndre_timeseries', {
							start: validation.start,
							end: validation.end,
						}),
						returnRaw: true,
					})
					if (!payload) {
						timeseriesNdreState = reduceTimeseriesState(
							timeseriesNdreState,
							{ type: 'failure', message: 'Unable to load NDRE timeseries.' },
							validation.start,
							validation.end,
						)
						renderTimeseriesCard(timeseriesNdreState, () => {}, 'NDRE timeseries')
						renderNdviCalendar(timeseriesNdreState)
						if (ndviTable) {
							ndviTable.textContent = ''
						}
						return
					}
					timeseriesNdreState = reduceTimeseriesState(
						timeseriesNdreState,
						{ type: 'success', payload },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesNdreState, () => {}, 'NDRE timeseries')
					renderNdviCalendar(timeseriesNdreState)
					if (timeseriesNdreState.status === NDVI_SERIES_STATE.has_data) {
						renderNdviTable(timeseriesNdreState.vm?.points ?? [])
					} else if (ndviTable) {
						ndviTable.textContent = ''
					}
				})
			}
			if (ndreRasterButton) {
				ndreRasterButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const url = farmNdreRasterUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const query = buildNdviQuery('ndre_raster', { date: validation.date })
					const queryString = buildQueryString(query)
					const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
					try {
						const tileUrlTemplate = farmNdreRasterUrl
							.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
							.replace('/raster.png', '/tiles/{z}/{x}/{y}.png')
						const response = await fetch(ncGenerateUrl(finalUrl), {
							credentials: 'same-origin',
							headers: {
								'OCS-APIRequest': 'true',
								'X-Requested-With': 'XMLHttpRequest',
								requesttoken: resolveRequestToken() ?? '',
							},
						})
						if (!response.ok) {
							showNdviError(
								`Unable to load NDRE raster preview (HTTP ${response.status}).`,
							)
							return
						}
						const contentType = response.headers.get('content-type') || ''
						if (!contentType.startsWith('image/')) {
							showNdviError(
								'NDRE raster preview did not return an image.',
							)
							return
						}
						const blob = await response.blob()
						if (!blob || blob.size === 0) {
							showNdviError('NDRE raster preview response was empty.')
							return
						}
						if (ndviRasterObjectUrl) {
							URL.revokeObjectURL(ndviRasterObjectUrl)
						}
						ndviRasterObjectUrl = URL.createObjectURL(blob)
						showRasterMap(ndviRasterObjectUrl, selectedFarm, tileUrlTemplate, 'NDRE')
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Unable to load NDRE raster preview.'
						showNdviError(message)
					}
				})
			}
			if (ndreQueueButton) {
				ndreQueueButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const queueOperation = resolveOperation('ndre_raster_queue')
					const bodyDateField = queueOperation?.bodyFields ? Object.keys(queueOperation.bodyFields).find(k => k.toLowerCase().includes('date')) : null
					try {
						const data = await runNdviRequest('queue NDRE raster', farmNdreRasterQueueUrl, {
							method: 'POST',
							body: bodyDateField ? buildNdviBody('ndre_raster_queue', { date: validation.date }) : null,
							query: !bodyDateField
								? buildNdviQuery('ndre_raster_queue', { date: validation.date })
								: undefined,
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued NDRE raster job #${jobId}`
							: 'Queued NDRE raster job'
						const card = renderResultCard({
							title: 'NDRE raster queue',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to queue NDRE raster.'
						showNdviError(message)
					}
				})
			}
			if (ndreRefreshButton) {
				ndreRefreshButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					try {
						const data = await runNdviRequest('refresh NDRE', farmNdreRefreshUrl, {
							method: 'POST',
							body: buildNdviBody('ndre_refresh'),
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued NDRE refresh job #${jobId}`
							: 'Queued NDRE refresh job'
						const card = renderResultCard({
							title: 'NDRE refresh',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to refresh NDRE.'
						showNdviError(message)
					}
				})
			}
			if (ndreStateButton) {
				ndreStateButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (farmStateOutput) {
						farmStateOutput.hidden = false
					}
					if (farmStateContent) {
						farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading NDRE farm state...</div>'
					}
					const url = farmNdreFarmStateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const payload = await runNdviRequest('ndre farm state', url, {
							method: 'GET',
							returnRaw: true,
						})
						if (!payload) {
							if (farmStateContent) {
								farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Unable to load NDRE farm state.</div>'
							}
							return
						}
						const data = unwrapResponseData(payload?.data ?? payload)
						const stateVal = data?.state ?? 'unknown'
						const meanNdre = data?.mean_ndre ?? null
						const maxNdre = data?.max_ndre ?? null
						const minNdre = data?.min_ndre ?? null
						const trend = data?.trend ?? null
						const interpretation = data?.interpretation ?? ''
						const action = data?.action ?? ''

						const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
						const formatNumber = typeof ndviUi.formatNumber === 'function' ? ndviUi.formatNumber : (v) => String(v)

						const stateLabels = {
							high: 'High',
							moderate: 'Moderate',
							low: 'Low',
							declining: 'Declining',
							unknown: 'Unknown',
						}
						const stateLevel = {
							high: 'success',
							moderate: 'success',
							low: 'warning',
							declining: 'warning',
							unknown: 'info',
						}
						const facts = []
						pushFact(facts, 'State', stateLabels[stateVal] ?? stateVal)
						pushFact(facts, 'Mean NDRE', meanNdre !== null ? formatNumber(meanNdre, 3) : '-')
						pushFact(facts, 'Max NDRE', maxNdre !== null ? formatNumber(maxNdre, 3) : '-')
						pushFact(facts, 'Min NDRE', minNdre !== null ? formatNumber(minNdre, 3) : '-')
						pushFact(facts, 'Trend', trend !== null ? (trend >= 0 ? `+${formatNumber(trend, 4)}` : formatNumber(trend, 4)) : '-')
						const card = renderResultCard({
							title: 'NDRE Farm State',
							level: stateLevel[stateVal] ?? 'info',
							badges: [
								stateLabels[stateVal] ?? stateVal,
							],
							summary: interpretation || `NDRE farm state: ${stateLabels[stateVal] ?? stateVal}`,
							callout: action || 'No action available',
							facts,
							debug: data,
						})
						if (farmStateContent) {
							farmStateContent.innerHTML = ''
							farmStateContent.appendChild(card)
						}
					} catch (error) {
						console.error('[farm_intelligence_platform] NDRE farm state error', error)
						if (farmStateContent) {
							farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Failed to load NDRE farm state.</div>'
						}
					}
				})
			}

			if (biomassLatestButton) {
				biomassLatestButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (ndviOutput) {
						ndviOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading latest Biomass...</div>'
					}
					latestBiomassState = reduceLatestState(latestBiomassState, { type: 'request' })
					renderLatestCard(latestBiomassState, () => {}, 'Latest Biomass')
					const payload = await runNdviRequest('latest Biomass', farmBiomassLatestUrl, {
						method: 'GET',
						returnRaw: true,
					})
					if (!payload) {
						latestBiomassState = reduceLatestState(latestBiomassState, {
							type: 'failure',
							message: 'Unable to load latest Biomass.',
						})
						renderLatestCard(latestBiomassState, () => {}, 'Latest Biomass')
						return
					}
					latestBiomassState = reduceLatestState(latestBiomassState, { type: 'success', payload }, new Date())
					renderLatestCard(latestBiomassState, () => {}, 'Latest Biomass')
				})
			}
			if (biomassTimeseriesButton) {
				biomassTimeseriesButton.addEventListener('click', async () => {
					clearFarmsNotes()
					clearNdviError()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateTimeseriesInputs(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					timeseriesBiomassState = reduceTimeseriesState(
						timeseriesBiomassState,
						{ type: 'request' },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesBiomassState, () => {}, 'Biomass timeseries')
					renderNdviCalendar(timeseriesBiomassState)
					const payload = await runNdviRequest('biomass_timeseries', farmBiomassTimeseriesUrl, {
						method: 'GET',
						query: buildNdviQuery('biomass_timeseries', {
							start: validation.start,
							end: validation.end,
						}),
						returnRaw: true,
					})
					if (!payload) {
						timeseriesBiomassState = reduceTimeseriesState(
							timeseriesBiomassState,
							{ type: 'failure', message: 'Unable to load Biomass timeseries.' },
							validation.start,
							validation.end,
						)
						renderTimeseriesCard(timeseriesBiomassState, () => {}, 'Biomass timeseries')
						renderNdviCalendar(timeseriesBiomassState)
						if (ndviTable) {
							ndviTable.textContent = ''
						}
						return
					}
					timeseriesBiomassState = reduceTimeseriesState(
						timeseriesBiomassState,
						{ type: 'success', payload },
						validation.start,
						validation.end,
					)
					renderTimeseriesCard(timeseriesBiomassState, () => {}, 'Biomass timeseries')
					renderNdviCalendar(timeseriesBiomassState)
					if (timeseriesBiomassState.status === NDVI_SERIES_STATE.has_data) {
						renderNdviTable(timeseriesBiomassState.vm?.points ?? [])
					} else if (ndviTable) {
						ndviTable.textContent = ''
					}
				})
			}
			if (biomassRasterButton) {
				biomassRasterButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const url = farmBiomassRasterUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const query = buildNdviQuery('biomass_raster', { date: validation.date })
					const queryString = buildQueryString(query)
					const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
					try {
						const tileUrlTemplate = farmBiomassRasterUrl
							.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
							.replace('/raster.png', '/tiles/{z}/{x}/{y}.png')
						const response = await fetch(ncGenerateUrl(finalUrl), {
							credentials: 'same-origin',
							headers: {
								'OCS-APIRequest': 'true',
								'X-Requested-With': 'XMLHttpRequest',
								requesttoken: resolveRequestToken() ?? '',
							},
						})
						if (!response.ok) {
							showNdviError(
								`Unable to load Biomass raster preview (HTTP ${response.status}).`,
							)
							return
						}
						const contentType = response.headers.get('content-type') || ''
						if (!contentType.startsWith('image/')) {
							showNdviError(
								'Biomass raster preview did not return an image.',
							)
							return
						}
						const blob = await response.blob()
						if (!blob || blob.size === 0) {
							showNdviError('Biomass raster preview response was empty.')
							return
						}
						if (ndviRasterObjectUrl) {
							URL.revokeObjectURL(ndviRasterObjectUrl)
						}
						ndviRasterObjectUrl = URL.createObjectURL(blob)
						showRasterMap(ndviRasterObjectUrl, selectedFarm, tileUrlTemplate, 'Biomass')
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Unable to load Biomass raster preview.'
						showNdviError(message)
					}
				})
			}
			if (biomassQueueButton) {
				biomassQueueButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					const state = readNdviDateState()
					const validation = validateRasterInput(state)
					if (!validation.ok) {
						showNdviError(validation.message)
						return
					}
					const queueOperation = resolveOperation('biomass_raster_queue')
					const bodyDateField = queueOperation?.bodyFields ? Object.keys(queueOperation.bodyFields).find(k => k.toLowerCase().includes('date')) : null
					try {
						const data = await runNdviRequest('queue Biomass raster', farmBiomassRasterQueueUrl, {
							method: 'POST',
							body: bodyDateField ? buildNdviBody('biomass_raster_queue', { date: validation.date }) : null,
							query: !bodyDateField
								? buildNdviQuery('biomass_raster_queue', { date: validation.date })
								: undefined,
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued Biomass raster job #${jobId}`
							: 'Queued Biomass raster job'
						const card = renderResultCard({
							title: 'Biomass raster queue',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to queue Biomass raster.'
						showNdviError(message)
					}
				})
			}
			if (biomassRefreshButton) {
				biomassRefreshButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					try {
						const data = await runNdviRequest('refresh Biomass', farmBiomassRefreshUrl, {
							method: 'POST',
							body: buildNdviBody('biomass_refresh'),
						})
						const jobId = data?.data?.job_id ?? data?.job_id ?? null
						const message = jobId !== null
							? `Queued Biomass refresh job #${jobId}`
							: 'Queued Biomass refresh job'
						const card = renderResultCard({
							title: 'Biomass refresh',
							level: 'info',
							summary: message,
							debug: data,
						})
						if (ndviOutput) {
							ndviOutput.innerHTML = ''
							ndviOutput.appendChild(card)
						}
					} catch (error) {
						const message = error instanceof Error ? error.message : 'Failed to refresh Biomass.'
						showNdviError(message)
					}
				})
			}
			if (biomassStateButton) {
				biomassStateButton.addEventListener('click', async () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showNdviError('Select a farm first.')
						return
					}
					if (farmStateOutput) {
						farmStateOutput.hidden = false
					}
					if (farmStateContent) {
						farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading Biomass farm state...</div>'
					}
					const url = farmBiomassFarmStateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const payload = await runNdviRequest('biomass farm state', url, {
							method: 'GET',
							returnRaw: true,
						})
						if (!payload) {
							if (farmStateContent) {
								farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Unable to load Biomass farm state.</div>'
							}
							return
						}
						const data = unwrapResponseData(payload?.data ?? payload)
						const stateVal = data?.state ?? 'unknown'
						const meanBiomass = data?.mean_biomass ?? null
						const maxBiomass = data?.max_biomass ?? null
						const minBiomass = data?.min_biomass ?? null
						const trend = data?.trend ?? null
						const interpretation = data?.interpretation ?? ''
						const action = data?.action ?? ''

						const ndviUi = window.FarmIntelligencePlatformNdviUi ?? window.FarmIntelligencePlatformNdviLatest ?? {}
						const formatNumber = typeof ndviUi.formatNumber === 'function' ? ndviUi.formatNumber : (v) => String(v)

						const stateLabels = {
							high: 'High',
							moderate: 'Moderate',
							low: 'Low',
							declining: 'Declining',
							unknown: 'Unknown',
						}
						const stateLevel = {
							high: 'success',
							moderate: 'success',
							low: 'warning',
							declining: 'warning',
							unknown: 'info',
						}
						const facts = []
						pushFact(facts, 'State', stateLabels[stateVal] ?? stateVal)
						pushFact(facts, 'Mean Biomass', meanBiomass !== null ? formatNumber(meanBiomass, 3) : '-')
						pushFact(facts, 'Max Biomass', maxBiomass !== null ? formatNumber(maxBiomass, 3) : '-')
						pushFact(facts, 'Min Biomass', minBiomass !== null ? formatNumber(minBiomass, 3) : '-')
						pushFact(facts, 'Trend', trend !== null ? (trend >= 0 ? `+${formatNumber(trend, 4)}` : formatNumber(trend, 4)) : '-')
						const card = renderResultCard({
							title: 'Biomass Farm State',
							level: stateLevel[stateVal] ?? 'info',
							badges: [
								stateLabels[stateVal] ?? stateVal,
							],
							summary: interpretation || `Biomass farm state: ${stateLabels[stateVal] ?? stateVal}`,
							callout: action || 'No action available',
							facts,
							debug: data,
						})
						if (farmStateContent) {
							farmStateContent.innerHTML = ''
							farmStateContent.appendChild(card)
						}
					} catch (error) {
						console.error('[farm_intelligence_platform] Biomass farm state error', error)
						if (farmStateContent) {
							farmStateContent.innerHTML = '<div class="farm-intelligence-platform-farms__note error">Failed to load Biomass farm state.</div>'
						}
					}
				})
			}

			// In-Situ Event Handlers
			const insituValidationButton = document.getElementById('farm-intelligence-platform-insitu-validation')
			const insituMoistureListButton = document.getElementById('farm-intelligence-platform-insitu-moisture-list')
			const insituMoistureCreateButton = document.getElementById('farm-intelligence-platform-insitu-moisture-create')
			const insituHarvestListButton = document.getElementById('farm-intelligence-platform-insitu-harvest-list')
			const insituHarvestCreateButton = document.getElementById('farm-intelligence-platform-insitu-harvest-create')
			const insituBiomassListButton = document.getElementById('farm-intelligence-platform-insitu-biomass-list')
			const insituBiomassCreateButton = document.getElementById('farm-intelligence-platform-insitu-biomass-create')
			const insituTreeListButton = document.getElementById('farm-intelligence-platform-insitu-tree-list')
			const insituTreeCreateButton = document.getElementById('farm-intelligence-platform-insitu-tree-create')
			const insituOutput = document.getElementById('farm-intelligence-platform-insitu-output')
			const insituError = document.getElementById('farm-intelligence-platform-insitu-error')

			const clearInsituError = () => {
				if (insituError) {
					insituError.textContent = ''
					insituError.hidden = true
				}
			}

			const showInsituError = (message) => {
				if (insituError) {
					insituError.textContent = message
					insituError.hidden = false
				}
			}

			const renderInsituCard = (title, data, level = 'info') => {
				const card = document.createElement('div')
				card.className = `farm-intelligence-platform-farms__note ${level}`
				card.innerHTML = `<strong>${title}</strong><pre style="max-height:400px;overflow:auto;font-size:12px;">${JSON.stringify(data, null, 2)}</pre>`
				return card
			}

			if (insituValidationButton) {
				insituValidationButton.addEventListener('click', async () => {
					clearInsituError()
					if (!selectedFarm) {
						showInsituError('Select a farm first.')
						return
					}
					if (insituOutput) {
						insituOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading validation report...</div>'
					}
					const url = form.dataset.farmInsituValidationUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const response = await fetch(url, { headers: { Accept: 'application/json' } })
						const data = await response.json()
						if (insituOutput) {
							insituOutput.innerHTML = ''
							insituOutput.appendChild(renderInsituCard('Validation Report', data))
						}
					} catch (error) {
						showInsituError(error instanceof Error ? error.message : 'Failed to load validation report.')
					}
				})
			}

			if (insituMoistureListButton) {
				insituMoistureListButton.addEventListener('click', async () => {
					clearInsituError()
					if (!selectedFarm) {
						showInsituError('Select a farm first.')
						return
					}
					if (insituOutput) {
						insituOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading soil moisture samples...</div>'
					}
					const url = form.dataset.farmInsituMoistureSamplesUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const response = await fetch(url, { headers: { Accept: 'application/json' } })
						const data = await response.json()
						if (insituOutput) {
							insituOutput.innerHTML = ''
							insituOutput.appendChild(renderInsituCard('Soil Moisture Samples', data))
						}
					} catch (error) {
						showInsituError(error instanceof Error ? error.message : 'Failed to load samples.')
					}
				})
			}

			if (insituMoistureCreateButton) {
				insituMoistureCreateButton.addEventListener('click', async () => {
					clearInsituError()
					if (!selectedFarm) {
						showInsituError('Select a farm first.')
						return
					}
					const collectedAt = prompt('Collected at (YYYY-MM-DDTHH:MM:SS):')
					if (!collectedAt) return
					const depthCm = prompt('Depth (cm):')
					if (!depthCm) return
					const method = prompt('Method (gravimetric/volumetric_probe/tdr/other):')
					if (!method) return
					const moistureFrac = prompt('Moisture fraction (0-1):')
					if (!moistureFrac) return
					const notes = prompt('Notes (optional):') || ''
					const url = form.dataset.farmInsituMoistureSampleCreateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const response = await fetch(url, {
							method: 'POST',
							headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
							body: JSON.stringify({
								collected_at: collectedAt,
								depth_cm: parseFloat(depthCm),
								method,
								moisture_frac: parseFloat(moistureFrac),
								notes,
							}),
						})
						const data = await response.json()
						if (insituOutput) {
							insituOutput.innerHTML = ''
							insituOutput.appendChild(renderInsituCard('Sample Created', data))
						}
					} catch (error) {
						showInsituError(error instanceof Error ? error.message : 'Failed to create sample.')
					}
				})
			}

			if (insituHarvestListButton) {
				insituHarvestListButton.addEventListener('click', async () => {
					clearInsituError()
					if (!selectedFarm) {
						showInsituError('Select a farm first.')
						return
					}
					if (insituOutput) {
						insituOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading harvest records...</div>'
					}
					const url = form.dataset.farmInsituHarvestsUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const response = await fetch(url, { headers: { Accept: 'application/json' } })
						const data = await response.json()
						if (insituOutput) {
							insituOutput.innerHTML = ''
							insituOutput.appendChild(renderInsituCard('Harvest Records', data))
						}
					} catch (error) {
						showInsituError(error instanceof Error ? error.message : 'Failed to load harvests.')
					}
				})
			}

			if (insituHarvestCreateButton) {
				insituHarvestCreateButton.addEventListener('click', async () => {
					clearInsituError()
					if (!selectedFarm) {
						showInsituError('Select a farm first.')
						return
					}
					const cropType = prompt('Crop type:')
					if (!cropType) return
					const harvestedAt = prompt('Harvested at (YYYY-MM-DD):')
					if (!harvestedAt) return
					const areaHa = prompt('Area (ha):')
					if (!areaHa) return
					const yieldTonnes = prompt('Yield (tonnes):')
					if (!yieldTonnes) return
					const notes = prompt('Notes (optional):') || ''
					const url = form.dataset.farmInsituHarvestCreateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const response = await fetch(url, {
							method: 'POST',
							headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
							body: JSON.stringify({
								crop_type: cropType,
								harvested_at: harvestedAt,
								area_ha: parseFloat(areaHa),
								yield_tonnes: parseFloat(yieldTonnes),
								notes,
							}),
						})
						const data = await response.json()
						if (insituOutput) {
							insituOutput.innerHTML = ''
							insituOutput.appendChild(renderInsituCard('Harvest Created', data))
						}
					} catch (error) {
						showInsituError(error instanceof Error ? error.message : 'Failed to create harvest.')
					}
				})
			}

			if (insituBiomassListButton) {
				insituBiomassListButton.addEventListener('click', async () => {
					clearInsituError()
					if (!selectedFarm) {
						showInsituError('Select a farm first.')
						return
					}
					if (insituOutput) {
						insituOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading biomass observations...</div>'
					}
					const url = form.dataset.farmInsituBiomassObsUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const response = await fetch(url, { headers: { Accept: 'application/json' } })
						const data = await response.json()
						if (insituOutput) {
							insituOutput.innerHTML = ''
							insituOutput.appendChild(renderInsituCard('Biomass Observations', data))
						}
					} catch (error) {
						showInsituError(error instanceof Error ? error.message : 'Failed to load observations.')
					}
				})
			}

			if (insituBiomassCreateButton) {
				insituBiomassCreateButton.addEventListener('click', async () => {
					clearInsituError()
					if (!selectedFarm) {
						showInsituError('Select a farm first.')
						return
					}
					const observedAt = prompt('Observed at (YYYY-MM-DDTHH:MM:SS):')
					if (!observedAt) return
					const plantHeightCm = prompt('Plant height (cm):')
					if (!plantHeightCm) return
					const standCountPerHa = prompt('Stand count per ha:')
					if (!standCountPerHa) return
					const growthStage = prompt('Growth stage:')
					if (!growthStage) return
					const notes = prompt('Notes (optional):') || ''
					const url = form.dataset.farmInsituBiomassObsCreateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const response = await fetch(url, {
							method: 'POST',
							headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
							body: JSON.stringify({
								observed_at: observedAt,
								plant_height_cm: parseFloat(plantHeightCm),
								stand_count_per_ha: parseFloat(standCountPerHa),
								growth_stage: growthStage,
								notes,
							}),
						})
						const data = await response.json()
						if (insituOutput) {
							insituOutput.innerHTML = ''
							insituOutput.appendChild(renderInsituCard('Observation Created', data))
						}
					} catch (error) {
						showInsituError(error instanceof Error ? error.message : 'Failed to create observation.')
					}
				})
			}

			if (insituTreeListButton) {
				insituTreeListButton.addEventListener('click', async () => {
					clearInsituError()
					if (!selectedFarm) {
						showInsituError('Select a farm first.')
						return
					}
					if (insituOutput) {
						insituOutput.innerHTML = '<div class="farm-intelligence-platform-farms__note">Loading tree surveys...</div>'
					}
					const url = form.dataset.farmInsituTreeSurveysUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					try {
						const response = await fetch(url, { headers: { Accept: 'application/json' } })
						const data = await response.json()
						if (insituOutput) {
							insituOutput.innerHTML = ''
							insituOutput.appendChild(renderInsituCard('Tree Surveys', data))
						}
					} catch (error) {
						showInsituError(error instanceof Error ? error.message : 'Failed to load surveys.')
					}
				})
			}

			if (insituTreeCreateButton) {
				insituTreeCreateButton.addEventListener('click', async () => {
					clearInsituError()
					if (!selectedFarm) {
						showInsituError('Select a farm first.')
						return
					}
					const observedAt = prompt('Observed at (YYYY-MM-DDTHH:MM:SS):')
					if (!observedAt) return
					const dbhCm = prompt('DBH (cm) or leave blank:')
					const crownDiameterM = prompt('Crown diameter (m) or leave blank:')
					if (!dbhCm && !crownDiameterM) {
						showInsituError('At least one of DBH or crown diameter is required.')
						return
					}
					const species = prompt('Species (optional):') || ''
					const notes = prompt('Notes (optional):') || ''
					const url = form.dataset.farmInsituTreeSurveyCreateUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const body = { observed_at: observedAt, species, notes }
					if (dbhCm) body.dbh_cm = parseFloat(dbhCm)
					if (crownDiameterM) body.crown_diameter_m = parseFloat(crownDiameterM)
					try {
						const response = await fetch(url, {
							method: 'POST',
							headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
							body: JSON.stringify(body),
						})
						const data = await response.json()
						if (insituOutput) {
							insituOutput.innerHTML = ''
							insituOutput.appendChild(renderInsituCard('Survey Created', data))
						}
					} catch (error) {
						showInsituError(error instanceof Error ? error.message : 'Failed to create survey.')
					}
				})
			}

			if (weatherCurrentTab) {
				weatherCurrentTab.addEventListener('click', () => {
					loadWeatherTab('current')
				})
			}
			if (weatherHourlyTab) {
				weatherHourlyTab.addEventListener('click', () => {
					loadWeatherTab('hourly')
				})
			}
			if (weatherDailyTab) {
				weatherDailyTab.addEventListener('click', () => {
					loadWeatherTab('daily')
				})
			}

			const bindNdviInput = (input, key) => {
				if (!input) {
					return
				}
				const markTouched = () => {
					ndviTouched[key] = true
					updateNdviActionState()
				}
				const normalizeOnChange = () => {
					const parsed = parseIsoDate(input.value)
					if (!parsed.invalid && parsed.iso && parsed.iso !== parsed.raw) {
						input.value = parsed.iso
					}
					markTouched()
				}
				input.addEventListener('change', normalizeOnChange)
				input.addEventListener('input', markTouched)
			}

			bindNdviInput(ndviStartInput, 'start')
			bindNdviInput(ndviEndInput, 'end')
			bindNdviInput(ndviDateInput, 'raster')

			setFarmsActionsEnabled(false)
			updateNdviActionState()

			;(async () => {
				const schemaOk = await getSchemaReady('init')
				if (schemaOk) {
					await refreshFarms()
				}
			})().catch((error) => {
				const message = error instanceof Error ? error.message : 'Unable to load farms.'
				showFarmsError(message)
			})
		}

		const runAdminAction = async (url, onSuccess, allowPasswordRetry = true) => {
			if (!url) {
				const message = 'Admin action is not available.'
				status.textContent = message
				status.classList.add('error')
				toast(message)
				return
			}

			clearStatus()
			clearCredentialsPanel()

			if (allowPasswordRetry) {
				await requirePasswordConfirmationAsync()
			}

			let result = await performAdminRequest(url)
			if (!result.parsed) {
				showParseError(result.text)
				return
			}

			if (allowPasswordRetry && isPasswordConfirmationRequired(result.response, result.data)) {
				const message = pickMessage(result.data, 'Password confirmation required.')
				status.textContent = message
				status.classList.add('error')
				toast(message)
				await requirePasswordConfirmationAsync()
				clearStatus()
				result = await performAdminRequest(url)
				if (!result.parsed) {
					showParseError(result.text)
					return
				}
			}

			const isOk = result.response.ok && result.data?.ok === true
			if (!isOk) {
				const message = pickMessage(result.data, 'Unable to perform admin action.')
				status.textContent = message
				status.classList.add('error')
				toast(message)
				return
			}

			onSuccess(result.data)
		}

		const handleGenerate = async () => {
			await runAdminAction(generateUrl, (data) => {
				const clientId = typeof data?.clientId === 'string' ? data.clientId : ''
				const hmacSecret = typeof data?.hmacSecret === 'string' ? data.hmacSecret : ''

				if (!clientId || !hmacSecret) {
					const message = 'Credential response is malformed.'
					status.textContent = message
					status.classList.add('error')
					toast(message)
					return
				}

				if (clientIdInput) {
					clientIdInput.value = clientId
				}

				showCredentials(clientId, hmacSecret)
				const message = pickMessage(data, 'Generated credentials. Shown once.')
				toast(message)
			})
		}

		const handleRotate = async () => {
			await runAdminAction(rotateUrl, (data) => {
				const hmacSecret = typeof data?.hmacSecret === 'string' ? data.hmacSecret : ''
				if (!hmacSecret) {
					const message = 'Rotation response is malformed.'
					status.textContent = message
					status.classList.add('error')
					toast(message)
					return
				}

				const clientId = typeof data?.clientId === 'string' ? data.clientId : (clientIdInput?.value ?? '')
				showCredentials(clientId, hmacSecret)
				const message = pickMessage(data, 'Rotated secret. Shown once.')
				toast(message)
			})
		}

		const handleTestConnection = async () => {
			if (!testConnectionUrl) {
				const message = 'Test connection is not available.'
				if (connectionStatus) {
					connectionStatus.textContent = message
					connectionStatus.classList.add('error')
				}
				toast(message)
				return
			}

			clearConnectionStatus()

			const result = await performAdminRequest(testConnectionUrl)
			if (!result.parsed) {
				showConnectionParseError(result.text)
				return
			}

			const isOk = result.response.ok
				&& (result.data?.ok === true || result.data?.status === 0)
			const expiresIn = Number.isFinite(result.data?.data?.expires_in)
				? Number(result.data.data.expires_in)
				: null
			let message = isOk
				? pickMessage(result.data, 'Connection successful.')
				: buildConnectionErrorMessage(result.response, result.data)
			if (isOk && expiresIn !== null) {
				message = `${message} (expires_in=${expiresIn}s)`
			}
			if (connectionStatus) {
				connectionStatus.textContent = message
				connectionStatus.classList.add(isOk ? 'success' : 'error')
			}
			toast(message)
		}

		const handleDiagnostics = async () => {
			if (!diagnosticsUrl) {
				const message = 'Diagnostics are not available.'
				if (diagnosticsSummary) {
					diagnosticsSummary.textContent = message
					diagnosticsSummary.classList.add('error')
				}
				toast(message)
				return
			}

			clearDiagnostics()

			const result = await performAdminGet(diagnosticsUrl)
			if (!result.parsed) {
				if (diagnosticsSummary) {
					const snippet = result.text.trim().slice(0, 200)
					const message = snippet || 'Unable to parse diagnostics response.'
					diagnosticsSummary.textContent = message
					diagnosticsSummary.classList.add('error')
					toast(message)
				}
				return
			}

			const payload = result.data?.data && typeof result.data.data === 'object'
				? result.data.data
				: result.data

			const tokenResult = payload?.token && typeof payload.token === 'object' ? payload.token : null
			const statusResult = payload?.status && typeof payload.status === 'object' ? payload.status : null
			const pngResult = payload?.png && typeof payload.png === 'object' ? payload.png : null

			const hasResults = tokenResult || statusResult || pngResult
			if (!hasResults) {
				const message = pickMessage(result.data, 'Diagnostics response is malformed.')
				if (diagnosticsSummary) {
					diagnosticsSummary.textContent = message
					diagnosticsSummary.classList.add('error')
				}
				toast(message)
				return
			}

			if (diagnosticsResults) {
				diagnosticsResults.hidden = false
			}

			const tokenOk = tokenResult?.ok === true
			const tokenMessage = tokenOk
				? joinParts([
					'OK',
					Number.isFinite(tokenResult?.expires_in)
						? `expires_in=${Number(tokenResult.expires_in)}s`
						: null,
				])
				: joinParts([
					'FAILED',
					formatHttp(tokenResult?.http),
					tokenResult?.message,
					tokenResult?.code ? `code=${tokenResult.code}` : null,
				])
			setDiagnosticsRow(diagnosticsTokenRow, diagnosticsTokenValue, tokenOk, tokenMessage || (tokenOk ? 'OK' : 'FAILED'))

			const statusOk = statusResult?.ok === true
			const statusMessage = statusOk
				? joinParts([
					'OK',
					formatHttp(statusResult?.http),
					statusResult?.version ? `version=${statusResult.version}` : null,
					statusResult?.server_time ? `server_time=${statusResult.server_time}` : null,
				])
				: joinParts([
					'FAILED',
					formatHttp(statusResult?.http),
					statusResult?.message,
					statusResult?.code ? `code=${statusResult.code}` : null,
				])
			setDiagnosticsRow(diagnosticsStatusRow, diagnosticsStatusValue, statusOk, statusMessage || (statusOk ? 'OK' : 'FAILED'))

			const previewDetails = previewUrl ? await fetchPreviewDiagnostics(previewUrl) : null
			const pngOkBase = pngResult?.ok === true
			const pngOk = pngOkBase && (previewDetails?.ok ?? true)
			const pngParts = pngOkBase
				? ['OK', formatHttp(pngResult?.http)]
				: [
					'FAILED',
					formatHttp(pngResult?.http),
					pngResult?.message,
					pngResult?.code ? `code=${pngResult.code}` : null,
				]
			if (previewDetails) {
				pngParts.push(
					previewDetails.http ? `preview_http=${previewDetails.http}` : null,
					previewDetails.contentType ? `content_type=${previewDetails.contentType}` : null,
					Number.isFinite(previewDetails.size) ? `bytes=${previewDetails.size}` : null,
					previewDetails.signatureOk === true ? 'signature=ok' : previewDetails.signatureOk === false ? 'signature=bad' : null,
					previewDetails.error ?? null,
				)
			}
			const pngMessage = joinParts(pngParts)
			setDiagnosticsRow(diagnosticsPngRow, diagnosticsPngValue, pngOk, pngMessage || (pngOk ? 'OK' : 'FAILED'))

			const overallOk = tokenOk && statusOk && pngOk
			const summaryMessage = pickMessage(
				result.data,
				overallOk ? 'Diagnostics passed.' : 'Diagnostics completed with failures.',
			)
			if (diagnosticsSummary) {
				diagnosticsSummary.textContent = summaryMessage
				diagnosticsSummary.classList.add(overallOk ? 'success' : 'error')
			}
			toast(summaryMessage)

			if (diagnosticsPreviewWrap && diagnosticsPreview) {
				const previewOk = previewDetails?.ok ?? pngOk
				if (previewOk && previewUrl) {
					diagnosticsPreview.src = previewDetails?.url ?? buildPreviewUrl(previewUrl)
					diagnosticsPreviewWrap.hidden = false
				} else {
					diagnosticsPreviewWrap.hidden = true
					diagnosticsPreview.removeAttribute('src')
				}
			}
		}

		if (generateButton) {
			generateButton.addEventListener('click', () => {
				console.info('[farm_intelligence_platform] generate clicked')
				handleGenerate().catch((error) => {
					const message = error instanceof Error ? error.message : 'Unable to generate credentials.'
					status.textContent = message
					status.classList.add('error')
					toast(message)
				})
			})
		}

		if (rotateButton) {
			rotateButton.addEventListener('click', () => {
				console.info('[farm_intelligence_platform] rotate clicked')
				handleRotate().catch((error) => {
					const message = error instanceof Error ? error.message : 'Unable to rotate secret.'
					status.textContent = message
					status.classList.add('error')
					toast(message)
				})
			})
		}

		if (testConnectionButton) {
			testConnectionButton.addEventListener('click', () => {
				console.info('[farm_intelligence_platform] test connection clicked')
				if (testConnectionButton) {
					testConnectionButton.disabled = true
				}
				handleTestConnection().catch((error) => {
					const message = error instanceof Error ? error.message : 'Unable to test connection.'
					if (connectionStatus) {
						connectionStatus.textContent = message
						connectionStatus.classList.add('error')
					}
					toast(message)
				}).finally(() => {
					if (testConnectionButton) {
						testConnectionButton.disabled = false
					}
				})
			})
		}

		if (diagnosticsButton) {
			diagnosticsButton.addEventListener('click', () => {
				console.info('[farm_intelligence_platform] diagnostics clicked')
				if (diagnosticsButton) {
					diagnosticsButton.disabled = true
				}
				handleDiagnostics().catch((error) => {
					const message = error instanceof Error ? error.message : 'Unable to run diagnostics.'
					if (diagnosticsSummary) {
						diagnosticsSummary.textContent = message
						diagnosticsSummary.classList.add('error')
					}
					toast(message)
				}).finally(() => {
					if (diagnosticsButton) {
						diagnosticsButton.disabled = false
					}
				})
			})
		}

		if (closeCredentialsButton) {
			closeCredentialsButton.addEventListener('click', () => {
				clearCredentialsPanel()
			})
		}

		if (copyClientIdButton && generatedClientIdInput) {
			copyClientIdButton.addEventListener('click', () => {
				const value = generatedClientIdInput.value.trim()
				copyToClipboard(value, generatedClientIdInput).then((ok) => {
					const message = ok ? 'Client ID copied.' : 'Unable to copy client ID.'
					toast(message)
				})
			})
		}

		if (copySecretButton && generatedSecretInput) {
			copySecretButton.addEventListener('click', () => {
				const value = generatedSecretInput.value.trim()
				copyToClipboard(value, generatedSecretInput).then((ok) => {
					const message = ok ? 'Secret copied.' : 'Unable to copy secret.'
					toast(message)
					if (ok) {
						clearCredentialsPanel()
					}
				})
			})
		}

		if (copyExportButton && exportSnippetInput) {
			copyExportButton.addEventListener('click', () => {
				const value = exportSnippetInput.value.trim()
				copyToClipboard(value, exportSnippetInput).then((ok) => {
					const message = ok ? 'Export snippet copied.' : 'Unable to copy export snippet.'
					toast(message)
				})
			})
		}

		setupFarms()
		setupRadio()

		const buildFormData = () => {
			const formData = new FormData(form)
			formData.set('baseUrl', (baseUrlInput?.value ?? '').trim())
			formData.set('clientId', (clientIdInput?.value ?? '').trim())
			formData.set('apiKey', (apiKeyInput?.value ?? '').trim())
			formData.set('hmacSecret', (hmacSecretInput?.value ?? '').trim())
			formData.set('allowlistHosts', (allowlistInput?.value ?? '').trim())
			formData.set('devAllowHttp', devAllowHttpInput?.checked ? '1' : '0')

			const parsedTimeout = Number.parseInt(timeoutInput?.value ?? '10', 10)
			const timeout = Number.isFinite(parsedTimeout) && parsedTimeout > 0 ? String(parsedTimeout) : '10'
			formData.set('timeoutSeconds', timeout)

			const token = resolveRequestToken()
			if (token) {
				formData.set('requesttoken', token)
			}

			return formData
		}

		form.addEventListener('submit', (event) => {
			event.preventDefault()
			clearStatus()

			const performSave = async (allowPasswordRetry = true) => {
				const token = resolveRequestToken()
				const response = await fetch(action, {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						Accept: 'application/json',
						requesttoken: token,
						'OCS-APIRequest': 'true',
						'X-Requested-With': 'XMLHttpRequest',
					},
					body: buildFormData(),
				})

				const { parsed, data, text } = await readJsonResponse(response)
				if (!parsed) {
					showParseError(text)
					return
				}

				// Requirement: if password confirmation is required, prompt then retry once
				if (allowPasswordRetry && isPasswordConfirmationRequired(response, data)) {
					const message = pickMessage(data, 'Password confirmation required.')
					status.textContent = message
					status.classList.add('error')
					toast(message)
					await requirePasswordConfirmationAsync()
					clearStatus()
					return performSave(false)
				}

				const isOk = response.ok && (data?.status === 'ok' || data?.ok === true)
				if (!isOk) {
					const message = pickMessage(data, 'Unable to save settings.')
					status.textContent = message
					status.classList.add('error')
					toast(message) // ✅ toast on error
					return
				}

				const message = pickMessage(data, 'Settings saved.')
				status.textContent = message
				status.classList.add('success')
				toast(message) // ✅ toast on success

				// Keep same behavior: clear secrets after successful save
				if (apiKeyInput) apiKeyInput.value = ''
				if (hmacSecretInput) hmacSecretInput.value = ''
			}

			;(async () => {
				// Keep existing behavior: request confirmation up front when needed
				await requirePasswordConfirmationAsync()
				await performSave(true)
			})().catch((error) => {
				const message = error instanceof Error ? error.message : 'Unable to save settings.'
				status.textContent = message
				status.classList.add('error')
				toast(message) // ✅ toast on unexpected error
			})
		})
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init)
	} else {
		init()
	}
})()
