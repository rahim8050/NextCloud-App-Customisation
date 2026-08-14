<?php
style('farm_intelligence_platform', 'admin-settings');
?>
<div id="farm-intelligence-platform-settings-root" class="section farm-intelligence-platform-settings">
	<h1><?php p($l->t('Farm Intelligence Platform')); ?></h1>

	<form id="farm-intelligence-platform-settings-form" class="farm-intelligence-platform-settings__form" method="post" action="<?php p($_['saveUrl']); ?>" data-generate-url="<?php p($_['generateCredentialsUrl']); ?>" data-rotate-url="<?php p($_['rotateHmacUrl']); ?>" data-config-url="<?php p($_['configUrl']); ?>" data-test-connection-url="<?php p($_['testConnectionUrl']); ?>" data-diagnostics-url="<?php p($_['diagnosticsUrl']); ?>" data-preview-url="<?php p($_['previewUrl']); ?>" data-farm-schema-url="<?php p($_['farmSchemaUrl']); ?>" data-farm-list-url="<?php p($_['farmListUrl']); ?>" data-farm-create-url="<?php p($_['farmCreateUrl']); ?>" data-farm-get-url="<?php p($_['farmGetUrl']); ?>" data-farm-update-url="<?php p($_['farmUpdateUrl']); ?>" data-farm-patch-url="<?php p($_['farmPatchUrl']); ?>" data-farm-delete-url="<?php p($_['farmDeleteUrl']); ?>" data-farm-sync-url="<?php p($_['farmSyncUrl']); ?>" data-farm-ndvi-latest-url="<?php p($_['farmNdviLatestUrl']); ?>" data-farm-ndvi-timeseries-url="<?php p($_['farmNdviTimeseriesUrl']); ?>" data-farm-ndvi-raster-url="<?php p($_['farmNdviRasterUrl']); ?>" data-farm-ndvi-raster-queue-url="<?php p($_['farmNdviRasterQueueUrl']); ?>" data-farm-ndvi-refresh-url="<?php p($_['farmNdviRefreshUrl']); ?>" data-farm-ndvi-geotiff-url="<?php p($_['farmNdviGeotiffUrl']); ?>" data-farm-ndwi-latest-url="<?php p($_['farmNdwiLatestUrl']); ?>" data-farm-ndwi-timeseries-url="<?php p($_['farmNdwiTimeseriesUrl']); ?>" data-farm-ndwi-raster-url="<?php p($_['farmNdwiRasterUrl']); ?>" data-farm-ndwi-raster-queue-url="<?php p($_['farmNdwiRasterQueueUrl']); ?>" data-farm-ndwi-refresh-url="<?php p($_['farmNdwiRefreshUrl']); ?>" data-farm-ndwi-farm-state-url="<?php p($_['farmNdwiFarmStateUrl']); ?>" data-farm-ndwi-geotiff-url="<?php p($_['farmNdwiGeotiffUrl']); ?>" data-farm-ndmi-latest-url="<?php p($_['farmNdmiLatestUrl']); ?>"
		data-farm-ndmi-timeseries-url="<?php p($_['farmNdmiTimeseriesUrl']); ?>"
		data-farm-ndmi-raster-url="<?php p($_['farmNdmiRasterUrl']); ?>"
		data-farm-ndmi-raster-queue-url="<?php p($_['farmNdmiRasterQueueUrl']); ?>"
		data-farm-ndmi-refresh-url="<?php p($_['farmNdmiRefreshUrl']); ?>"
		data-farm-ndmi-farm-state-url="<?php p($_['farmNdmiFarmStateUrl']); ?>" data-farm-ndmi-geotiff-url="<?php p($_['farmNdmiGeotiffUrl']); ?>" data-farm-rvi-latest-url="<?php p($_['farmRviLatestUrl']); ?>" data-farm-rvi-timeseries-url="<?php p($_['farmRviTimeseriesUrl']); ?>" data-farm-rvi-raster-url="<?php p($_['farmRviRasterUrl']); ?>" data-farm-rvi-raster-queue-url="<?php p($_['farmRviRasterQueueUrl']); ?>" data-farm-rvi-refresh-url="<?php p($_['farmRviRefreshUrl']); ?>" data-farm-rvi-farm-state-url="<?php p($_['farmRviFarmStateUrl']); ?>" data-farm-s1-smi-geotiff-url="<?php p($_['farmS1SmiGeotiffUrl']); ?>" data-farm-rvi-geotiff-url="<?php p($_['farmRviGeotiffUrl']); ?>" data-farm-s1-smi-latest-url="<?php p($_['farmS1SmiLatestUrl']); ?>" data-farm-s1-smi-timeseries-url="<?php p($_['farmS1SmiTimeseriesUrl']); ?>" data-farm-s1-smi-raster-url="<?php p($_['farmS1SmiRasterUrl']); ?>" data-farm-s1-smi-raster-queue-url="<?php p($_['farmS1SmiRasterQueueUrl']); ?>" data-farm-s1-smi-refresh-url="<?php p($_['farmS1SmiRefreshUrl']); ?>" data-farm-s1-smi-farm-state-url="<?php p($_['farmS1SmiFarmStateUrl']); ?>" data-farm-s3-lst-latest-url="<?php p($_['farmS3LstLatestUrl']); ?>" data-farm-s3-lst-timeseries-url="<?php p($_['farmS3LstTimeseriesUrl']); ?>" data-farm-s3-lst-raster-url="<?php p($_['farmS3LstRasterUrl']); ?>" data-farm-s3-lst-raster-queue-url="<?php p($_['farmS3LstRasterQueueUrl']); ?>" data-farm-s3-lst-refresh-url="<?php p($_['farmS3LstRefreshUrl']); ?>" data-farm-s3-lst-farm-state-url="<?php p($_['farmS3LstFarmStateUrl']); ?>" data-farm-s3-lst-geotiff-url="<?php p($_['farmS3LstGeotiffUrl']); ?>" 		data-farm-landsat-lst-latest-url="<?php p($_['farmLandsatLstLatestUrl']); ?>" data-farm-landsat-lst-timeseries-url="<?php p($_['farmLandsatLstTimeseriesUrl']); ?>" data-farm-landsat-lst-raster-url="<?php p($_['farmLandsatLstRasterUrl']); ?>" data-farm-landsat-lst-raster-queue-url="<?php p($_['farmLandsatLstRasterQueueUrl']); ?>" data-farm-landsat-lst-refresh-url="<?php p($_['farmLandsatLstRefreshUrl']); ?>" data-farm-landsat-lst-farm-state-url="<?php p($_['farmLandsatLstFarmStateUrl']); ?>" data-farm-landsat-lst-geotiff-url="<?php p($_['farmLandsatLstGeotiffUrl']); ?>" data-farm-iron-oxide-latest-url="<?php p($_['farmIronOxideLatestUrl']); ?>" data-farm-iron-oxide-timeseries-url="<?php p($_['farmIronOxideTimeseriesUrl']); ?>" data-farm-iron-oxide-raster-url="<?php p($_['farmIronOxideRasterUrl']); ?>" data-farm-iron-oxide-raster-queue-url="<?php p($_['farmIronOxideRasterQueueUrl']); ?>" data-farm-iron-oxide-refresh-url="<?php p($_['farmIronOxideRefreshUrl']); ?>" data-farm-iron-oxide-farm-state-url="<?php p($_['farmIronOxideFarmStateUrl']); ?>" data-farm-iron-oxide-geotiff-url="<?php p($_['farmIronOxideGeotiffUrl']); ?>" data-farm-evi-latest-url="<?php p($_['farmEviLatestUrl']); ?>" data-farm-evi-timeseries-url="<?php p($_['farmEviTimeseriesUrl']); ?>" data-farm-evi-raster-url="<?php p($_['farmEviRasterUrl']); ?>" data-farm-evi-raster-queue-url="<?php p($_['farmEviRasterQueueUrl']); ?>" data-farm-evi-refresh-url="<?php p($_['farmEviRefreshUrl']); ?>" data-farm-evi-farm-state-url="<?php p($_['farmEviFarmStateUrl']); ?>" data-farm-evi-geotiff-url="<?php p($_['farmEviGeotiffUrl']); ?>" data-farm-l-rvi-latest-url="<?php p($_['farmLRviLatestUrl']); ?>" data-farm-l-rvi-timeseries-url="<?php p($_['farmLRviTimeseriesUrl']); ?>" data-farm-l-rvi-raster-url="<?php p($_['farmLRviRasterUrl']); ?>" data-farm-l-rvi-raster-queue-url="<?php p($_['farmLRviRasterQueueUrl']); ?>" data-farm-l-rvi-refresh-url="<?php p($_['farmLRviRefreshUrl']); ?>" data-farm-l-rvi-farm-state-url="<?php p($_['farmLRviFarmStateUrl']); ?>" data-farm-l-rvi-geotiff-url="<?php p($_['farmLRviGeotiffUrl']); ?>" data-farm-nisar-smi-latest-url="<?php p($_['farmNisarSmiLatestUrl']); ?>" data-farm-nisar-smi-timeseries-url="<?php p($_['farmNisarSmiTimeseriesUrl']); ?>" data-farm-nisar-smi-raster-url="<?php p($_['farmNisarSmiRasterUrl']); ?>" data-farm-nisar-smi-raster-queue-url="<?php p($_['farmNisarSmiRasterQueueUrl']); ?>" data-farm-nisar-smi-refresh-url="<?php p($_['farmNisarSmiRefreshUrl']); ?>" data-farm-nisar-smi-farm-state-url="<?php p($_['farmNisarSmiFarmStateUrl']); ?>" data-farm-nisar-smi-geotiff-url="<?php p($_['farmNisarSmiGeotiffUrl']); ?>" data-farm-weather-current-url="<?php p($_['farmWeatherCurrentUrl']); ?>" data-farm-weather-hourly-url="<?php p($_['farmWeatherHourlyUrl']); ?>" data-farm-weather-daily-url="<?php p($_['farmWeatherDailyUrl']); ?>" data-farm-state-url="<?php p($_['farmStateUrl']); ?>" data-farm-decision-url="<?php p($_['farmDecisionUrl']); ?>" data-farm-observations-url="<?php p($_['farmObservationsUrl']); ?>" data-farm-observation-url="<?php p($_['farmObservationUrl']); ?>" data-farm-activities-url="<?php p($_['farmActivitiesUrl']); ?>" data-farm-activity-url="<?php p($_['farmActivityUrl']); ?>" data-activity-schema-url="<?php p($_['activitySchemaUrl']); ?>" data-activity-list-url="<?php p($_['activityListUrl']); ?>" data-activity-create-url="<?php p($_['activityCreateUrl']); ?>" data-activity-get-url="<?php p($_['activityGetUrl']); ?>" data-activity-update-url="<?php p($_['activityUpdateUrl']); ?>" data-activity-patch-url="<?php p($_['activityPatchUrl']); ?>" data-activity-delete-url="<?php p($_['activityDeleteUrl']); ?>" data-radio-providers-url="<?php p($_['radioProvidersUrl']); ?>" data-radio-stations-url="<?php p($_['radioStationsUrl']); ?>" data-radio-station-url="<?php p($_['radioStationUrl']); ?>" data-radio-stream-url="<?php p($_['radioStreamUrl']); ?>" data-radio-now-playing-url="<?php p($_['radioNowPlayingUrl']); ?>" data-radio-analytics-url="<?php p($_['radioAnalyticsUrl']); ?>" data-radio-station-health-url="<?php p($_['radioStationHealthUrl']); ?>" data-radio-station-health-history-url="<?php p($_['radioStationHealthHistoryUrl']); ?>" data-radio-health-url="<?php p($_['radioHealthUrl']); ?>" data-radio-emergency-current-url="<?php p($_['radioEmergencyCurrentUrl']); ?>" data-radio-emergency-history-url="<?php p($_['radioEmergencyHistoryUrl']); ?>" data-radio-emergency-create-url="<?php p($_['radioEmergencyCreateUrl']); ?>" data-radio-emergency-update-url="<?php p($_['radioEmergencyUpdateUrl']); ?>" data-radio-emergency-delete-url="<?php p($_['radioEmergencyDeleteUrl']); ?>" data-radio-tts-url="<?php p($_['radioTtsUrl']); ?>">
		<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken'] ?? \OC::$server->getRequest()->getParam('requesttoken', '') ?? ''); ?>" />
		<input type="hidden" name="format" value="json" />
		<div class="form-group">
			<label for="farm-intelligence-platform-base-url"><?php p($l->t('Base URL')); ?></label>
			<input id="farm-intelligence-platform-base-url" type="url" name="baseUrl" value="<?php p($_['baseUrl']); ?>" required />
		</div>

		<div class="form-group">
			<label for="farm-intelligence-platform-client-id"><?php p($l->t('Client ID')); ?></label>
			<input id="farm-intelligence-platform-client-id" type="text" name="clientId" value="<?php p($_['clientId']); ?>" maxlength="128" required />
		</div>

		<div class="form-group secret-field">
			<label for="farm-intelligence-platform-api-key"><?php p($l->t('API key')); ?></label>
			<input id="farm-intelligence-platform-api-key" type="password" name="apiKey" placeholder="<?php p($_['apiKeySet'] ? $l->t('Already set') : $l->t('Enter API key')); ?>" autocomplete="new-password" />
			<p class="hint"><?php p($l->t('Leave blank to retain the current value.')); ?></p>
		</div>

		<div class="form-group secret-field">
			<label for="farm-intelligence-platform-signing-secret"><?php p($l->t('Signing secret (base64)')); ?></label>
			<input id="farm-intelligence-platform-signing-secret" type="password" name="hmacSecret" placeholder="<?php p($_['hmacSecretSet'] ? $l->t('Already set') : $l->t('Enter base64 secret')); ?>" autocomplete="new-password" />
			<p class="hint"><?php p($l->t('Base64-encoded secret. Leave blank to keep the stored value.')); ?></p>
		</div>

		<div class="form-group">
			<label><?php p($l->t('HMAC credentials')); ?></label>
			<div class="farm-intelligence-platform-credentials__actions">
				<button id="farm-intelligence-platform-generate" type="button" class="button"><?php p($l->t('Generate client + secret')); ?></button>
				<button id="farm-intelligence-platform-rotate" type="button" class="button"><?php p($l->t('Rotate secret')); ?></button>
			</div>
			<p class="hint"><?php p($l->t('Shown once. Store securely.')); ?></p>
		</div>

		<div id="farm-intelligence-platform-credentials-result" class="farm-intelligence-platform-credentials" hidden>
			<div class="farm-intelligence-platform-credentials__header">
				<strong><?php p($l->t('Generated credentials')); ?></strong>
				<button id="farm-intelligence-platform-credentials-close" type="button" class="button"><?php p($l->t('Clear')); ?></button>
			</div>
			<p class="warning"><?php p($l->t('Shown once. Store securely.')); ?></p>
			<div class="form-group">
				<label for="farm-intelligence-platform-generated-client-id"><?php p($l->t('Client ID')); ?></label>
				<div class="farm-intelligence-platform-credentials__row">
					<input id="farm-intelligence-platform-generated-client-id" type="text" readonly />
					<button id="farm-intelligence-platform-copy-client-id" type="button" class="button"><?php p($l->t('Copy')); ?></button>
				</div>
			</div>
			<div class="form-group">
				<label for="farm-intelligence-platform-generated-secret"><?php p($l->t('HMAC secret (base64)')); ?></label>
				<div class="farm-intelligence-platform-credentials__row">
					<input id="farm-intelligence-platform-generated-secret" type="text" readonly />
					<button id="farm-intelligence-platform-copy-secret" type="button" class="button"><?php p($l->t('Copy')); ?></button>
				</div>
			</div>
			<div class="form-group">
				<label for="farm-intelligence-platform-generated-export"><?php p($l->t('DRF export snippet')); ?></label>
				<div class="farm-intelligence-platform-credentials__row">
					<textarea id="farm-intelligence-platform-generated-export" class="farm-intelligence-platform-credentials__snippet" rows="3" readonly></textarea>
					<button id="farm-intelligence-platform-copy-export" type="button" class="button"><?php p($l->t('Copy')); ?></button>
				</div>
				<p class="hint"><?php p($l->t('Set these values in the DRF environment.')); ?></p>
			</div>
		</div>

		<div class="form-group farm-intelligence-platform-connection">
			<label><?php p($l->t('Integration Status')); ?></label>
			<div class="farm-intelligence-platform-connection__actions">
				<button id="farm-intelligence-platform-test-connection" type="button" class="button"><?php p($l->t('Test connection')); ?></button>
				<div id="farm-intelligence-platform-connection-status" class="farm-intelligence-platform-connection__status" role="status" aria-live="polite"></div>
			</div>
			<p class="hint"><?php p($l->t('Performs a backend token request to verify HMAC connectivity.')); ?></p>
		</div>

		<div class="form-group farm-intelligence-platform-diagnostics">
			<label><?php p($l->t('Diagnostics')); ?></label>
			<div class="farm-intelligence-platform-diagnostics__actions">
				<button id="farm-intelligence-platform-run-diagnostics" type="button" class="button"><?php p($l->t('Run diagnostics')); ?></button>
				<div id="farm-intelligence-platform-diagnostics-summary" class="farm-intelligence-platform-diagnostics__summary" role="status" aria-live="polite"></div>
			</div>
			<div id="farm-intelligence-platform-diagnostics-results" class="farm-intelligence-platform-diagnostics__results" hidden>
				<div class="farm-intelligence-platform-diagnostics__row" id="farm-intelligence-platform-diagnostics-token-row">
					<span class="farm-intelligence-platform-diagnostics__label"><?php p($l->t('Token')); ?></span>
					<span id="farm-intelligence-platform-diagnostics-token" class="farm-intelligence-platform-diagnostics__value"></span>
				</div>
				<div class="farm-intelligence-platform-diagnostics__row" id="farm-intelligence-platform-diagnostics-status-row">
					<span class="farm-intelligence-platform-diagnostics__label"><?php p($l->t('Status')); ?></span>
					<span id="farm-intelligence-platform-diagnostics-status" class="farm-intelligence-platform-diagnostics__value"></span>
				</div>
				<div class="farm-intelligence-platform-diagnostics__row" id="farm-intelligence-platform-diagnostics-png-row">
					<span class="farm-intelligence-platform-diagnostics__label"><?php p($l->t('PNG preview')); ?></span>
					<span id="farm-intelligence-platform-diagnostics-png" class="farm-intelligence-platform-diagnostics__value"></span>
				</div>
				<div class="farm-intelligence-platform-diagnostics__preview" id="farm-intelligence-platform-diagnostics-preview-wrap" hidden>
					<img id="farm-intelligence-platform-diagnostics-preview" alt="<?php p($l->t('DRF preview')); ?>" />
				</div>
			</div>
			<p class="hint"><?php p($l->t('Runs token minting, status, and PNG preview through the proxy.')); ?></p>
		</div>

		<div class="form-group farm-intelligence-platform-farms" id="farm-intelligence-platform-farms">
			<div class="farm-intelligence-platform-farms__header">
				<div>
					<strong><?php p($l->t('Farms')); ?></strong>
					<p class="hint"><?php p($l->t('Manage farms, NDVI, and weather from the DRF backend via the admin proxy.')); ?></p>
				</div>
				<div class="farm-intelligence-platform-farms__actions">
					<button id="farm-intelligence-platform-farms-refresh" type="button" class="button"><?php p($l->t('Refresh farms')); ?></button>
					<button id="farm-intelligence-platform-farms-create" type="button" class="button primary"><?php p($l->t('New farm')); ?></button>
				</div>
			</div>
			<div id="farm-intelligence-platform-farms-warning" class="farm-intelligence-platform-farms__note warning" hidden></div>
			<div id="farm-intelligence-platform-farms-error" class="farm-intelligence-platform-farms__note error" hidden></div>
			<div class="farm-intelligence-platform-farms__table-wrap">
				<table class="farm-intelligence-platform-farms__table">
					<thead>
						<tr id="farm-intelligence-platform-farms-columns"></tr>
					</thead>
					<tbody id="farm-intelligence-platform-farms-body"></tbody>
				</table>
			</div>
			<div class="farm-intelligence-platform-farms__pagination" id="farm-intelligence-platform-farms-pagination" hidden>
				<button id="farm-intelligence-platform-farms-prev" type="button" class="button"><?php p($l->t('Previous')); ?></button>
				<div id="farm-intelligence-platform-farms-page" class="farm-intelligence-platform-farms__page"></div>
				<button id="farm-intelligence-platform-farms-next" type="button" class="button"><?php p($l->t('Next')); ?></button>
			</div>
			<div class="farm-intelligence-platform-farms__panels">
				<div class="farm-intelligence-platform-farms__ndvi" id="farm-intelligence-platform-farms-ndvi" hidden>
					<div class="farm-intelligence-platform-farms__ndvi-header">
						<strong><?php p($l->t('NDVI')); ?></strong>
						<span id="farm-intelligence-platform-farms-ndvi-title" class="farm-intelligence-platform-farms__ndvi-title"></span>
					</div>
					<div class="farm-intelligence-platform-farms__ndvi-actions">
						<button id="farm-intelligence-platform-ndvi-latest" type="button" class="button"><?php p($l->t('Latest NDVI')); ?></button>
						<button id="farm-intelligence-platform-ndvi-timeseries" type="button" class="button"><?php p($l->t('Timeseries')); ?></button>
						<button id="farm-intelligence-platform-ndvi-queue" type="button" class="button"><?php p($l->t('Queue raster')); ?></button>
						<button id="farm-intelligence-platform-ndvi-raster" type="button" class="button"><?php p($l->t('NDVI Raster PNG')); ?></button>
						<button id="farm-intelligence-platform-ndvi-geotiff" type="button" class="button"><?php p($l->t('NDVI GeoTIFF')); ?></button>
						<button id="farm-intelligence-platform-ndvi-refresh" type="button" class="button"><?php p($l->t('Refresh')); ?></button>
						<button id="farm-intelligence-platform-farm-state" type="button" class="button"><?php p($l->t('Farm State')); ?></button>
						<button id="farm-intelligence-platform-decision" type="button" class="button"><?php p($l->t('Decision')); ?></button>
					</div>
					<div class="farm-intelligence-platform-farms__ndvi-actions">
						<button id="farm-intelligence-platform-ndwi-latest" type="button" class="button"><?php p($l->t('Latest NDWI')); ?></button>
						<button id="farm-intelligence-platform-ndwi-timeseries" type="button" class="button"><?php p($l->t('NDWI Timeseries')); ?></button>
						<button id="farm-intelligence-platform-ndwi-queue" type="button" class="button"><?php p($l->t('Queue NDWI raster')); ?></button>
						<button id="farm-intelligence-platform-ndwi-refresh" type="button" class="button"><?php p($l->t('Refresh NDWI')); ?></button>
						<button id="farm-intelligence-platform-ndwi-raster" type="button" class="button"><?php p($l->t('NDWI Raster PNG')); ?></button>
						<button id="farm-intelligence-platform-ndwi-geotiff" type="button" class="button"><?php p($l->t('NDWI GeoTIFF')); ?></button>
						<button id="farm-intelligence-platform-ndwi-state" type="button" class="button"><?php p($l->t('NDWI State')); ?></button>
						<button id="farm-intelligence-platform-ndmi-latest" type="button" class="button"><?php p($l->t('Latest NDMI')); ?></button>
						<button id="farm-intelligence-platform-ndmi-timeseries" type="button" class="button"><?php p($l->t('NDMI Timeseries')); ?></button>
						<button id="farm-intelligence-platform-ndmi-queue" type="button" class="button"><?php p($l->t('Queue NDMI raster')); ?></button>
						<button id="farm-intelligence-platform-ndmi-refresh" type="button" class="button"><?php p($l->t('Refresh NDMI')); ?></button>
						<button id="farm-intelligence-platform-ndmi-raster" type="button" class="button"><?php p($l->t('NDMI Raster PNG')); ?></button>
						<button id="farm-intelligence-platform-ndmi-geotiff" type="button" class="button"><?php p($l->t('NDMI GeoTIFF')); ?></button>
						<button id="farm-intelligence-platform-ndmi-state" type="button" class="button"><?php p($l->t('NDMI State')); ?></button>
					</div>
					<div class="farm-intelligence-platform-farms__ndvi-actions">
						<button id="farm-intelligence-platform-rvi-latest" type="button" class="button"><?php p($l->t('Latest RVI')); ?></button>
						<button id="farm-intelligence-platform-rvi-timeseries" type="button" class="button"><?php p($l->t('RVI Timeseries')); ?></button>
						<button id="farm-intelligence-platform-rvi-queue" type="button" class="button"><?php p($l->t('Queue RVI raster')); ?></button>
						<button id="farm-intelligence-platform-rvi-refresh" type="button" class="button"><?php p($l->t('Refresh RVI')); ?></button>
						<button id="farm-intelligence-platform-rvi-raster" type="button" class="button"><?php p($l->t('RVI Raster PNG')); ?></button>
						<button id="farm-intelligence-platform-rvi-geotiff" type="button" class="button"><?php p($l->t('RVI GeoTIFF')); ?></button>
						<button id="farm-intelligence-platform-rvi-state" type="button" class="button"><?php p($l->t('RVI State')); ?></button>
					</div>
					<div class="farm-intelligence-platform-farms__ndvi-actions">
						<button id="farm-intelligence-platform-s1-smi-latest" type="button" class="button"><?php p($l->t('Latest S1_SMI')); ?></button>
						<button id="farm-intelligence-platform-s1-smi-timeseries" type="button" class="button"><?php p($l->t('S1_SMI Timeseries')); ?></button>
						<button id="farm-intelligence-platform-s1-smi-queue" type="button" class="button"><?php p($l->t('Queue S1_SMI raster')); ?></button>
						<button id="farm-intelligence-platform-s1-smi-refresh" type="button" class="button"><?php p($l->t('Refresh S1_SMI')); ?></button>
						<button id="farm-intelligence-platform-s1-smi-raster" type="button" class="button"><?php p($l->t('S1_SMI Raster PNG')); ?></button>
						<button id="farm-intelligence-platform-s1-smi-geotiff" type="button" class="button"><?php p($l->t('S1_SMI GeoTIFF')); ?></button>
						<button id="farm-intelligence-platform-s1-smi-state" type="button" class="button"><?php p($l->t('S1_SMI State')); ?></button>
					</div>
					<div class="farm-intelligence-platform-farms__ndvi-actions">
						<button id="farm-intelligence-platform-s3-lst-latest" type="button" class="button"><?php p($l->t('Latest S3_LST')); ?></button>
						<button id="farm-intelligence-platform-s3-lst-timeseries" type="button" class="button"><?php p($l->t('S3_LST Timeseries')); ?></button>
						<button id="farm-intelligence-platform-s3-lst-queue" type="button" class="button"><?php p($l->t('Queue S3_LST raster')); ?></button>
						<button id="farm-intelligence-platform-s3-lst-refresh" type="button" class="button"><?php p($l->t('Refresh S3_LST')); ?></button>
						<button id="farm-intelligence-platform-s3-lst-raster" type="button" class="button"><?php p($l->t('S3_LST Raster PNG')); ?></button>
						<button id="farm-intelligence-platform-s3-lst-geotiff" type="button" class="button"><?php p($l->t('S3_LST GeoTIFF')); ?></button>
						<button id="farm-intelligence-platform-s3-lst-state" type="button" class="button"><?php p($l->t('S3_LST State')); ?></button>
					</div>
					<div class="farm-intelligence-platform-farms__ndvi-actions">
						<button id="farm-intelligence-platform-landsat-lst-latest" type="button" class="button"><?php p($l->t('Latest LANDSAT_LST')); ?></button>
						<button id="farm-intelligence-platform-landsat-lst-timeseries" type="button" class="button"><?php p($l->t('LANDSAT_LST Timeseries')); ?></button>
						<button id="farm-intelligence-platform-landsat-lst-queue" type="button" class="button"><?php p($l->t('Queue LANDSAT_LST raster')); ?></button>
						<button id="farm-intelligence-platform-landsat-lst-refresh" type="button" class="button"><?php p($l->t('Refresh LANDSAT_LST')); ?></button>
						<button id="farm-intelligence-platform-landsat-lst-raster" type="button" class="button"><?php p($l->t('LANDSAT_LST Raster PNG')); ?></button>
						<button id="farm-intelligence-platform-landsat-lst-geotiff" type="button" class="button"><?php p($l->t('LANDSAT_LST GeoTIFF')); ?></button>
						<button id="farm-intelligence-platform-landsat-lst-state" type="button" class="button"><?php p($l->t('LANDSAT_LST State')); ?></button>
					</div>
					<div class="farm-intelligence-platform-farms__ndvi-actions">
						<button id="farm-intelligence-platform-iron-oxide-latest" type="button" class="button"><?php p($l->t('Latest IRON_OXIDE')); ?></button>
						<button id="farm-intelligence-platform-iron-oxide-timeseries" type="button" class="button"><?php p($l->t('IRON_OXIDE Timeseries')); ?></button>
						<button id="farm-intelligence-platform-iron-oxide-queue" type="button" class="button"><?php p($l->t('Queue IRON_OXIDE raster')); ?></button>
						<button id="farm-intelligence-platform-iron-oxide-refresh" type="button" class="button"><?php p($l->t('Refresh IRON_OXIDE')); ?></button>
						<button id="farm-intelligence-platform-iron-oxide-raster" type="button" class="button"><?php p($l->t('IRON_OXIDE Raster PNG')); ?></button>
						<button id="farm-intelligence-platform-iron-oxide-geotiff" type="button" class="button"><?php p($l->t('IRON_OXIDE GeoTIFF')); ?></button>
						<button id="farm-intelligence-platform-iron-oxide-state" type="button" class="button"><?php p($l->t('IRON_OXIDE State')); ?></button>
					</div>
					<div class="farm-intelligence-platform-farms__ndvi-actions">
						<button id="farm-intelligence-platform-evi-latest" type="button" class="button"><?php p($l->t('Latest EVI')); ?></button>
						<button id="farm-intelligence-platform-evi-timeseries" type="button" class="button"><?php p($l->t('EVI Timeseries')); ?></button>
						<button id="farm-intelligence-platform-evi-queue" type="button" class="button"><?php p($l->t('Queue EVI raster')); ?></button>
						<button id="farm-intelligence-platform-evi-refresh" type="button" class="button"><?php p($l->t('Refresh EVI')); ?></button>
						<button id="farm-intelligence-platform-evi-raster" type="button" class="button"><?php p($l->t('EVI Raster PNG')); ?></button>
						<button id="farm-intelligence-platform-evi-geotiff" type="button" class="button"><?php p($l->t('EVI GeoTIFF')); ?></button>
						<button id="farm-intelligence-platform-evi-state" type="button" class="button"><?php p($l->t('EVI State')); ?></button>
					</div>
					<div class="farm-intelligence-platform-farms__ndvi-actions">
						<button id="farm-intelligence-platform-nisar-smi-latest" type="button" class="button"><?php p($l->t('Latest NISAR_SMI')); ?></button>
						<button id="farm-intelligence-platform-nisar-smi-timeseries" type="button" class="button"><?php p($l->t('NISAR_SMI Timeseries')); ?></button>
						<button id="farm-intelligence-platform-nisar-smi-queue" type="button" class="button"><?php p($l->t('Queue NISAR_SMI raster')); ?></button>
						<button id="farm-intelligence-platform-nisar-smi-refresh" type="button" class="button"><?php p($l->t('Refresh NISAR_SMI')); ?></button>
						<button id="farm-intelligence-platform-nisar-smi-raster" type="button" class="button"><?php p($l->t('NISAR_SMI Raster PNG')); ?></button>
						<button id="farm-intelligence-platform-nisar-smi-geotiff" type="button" class="button"><?php p($l->t('NISAR_SMI GeoTIFF')); ?></button>
						<button id="farm-intelligence-platform-nisar-smi-state" type="button" class="button"><?php p($l->t('NISAR_SMI State')); ?></button>
					</div>
					<div class="farm-intelligence-platform-farms__ndvi-actions">
						<button id="farm-intelligence-platform-l-rvi-latest" type="button" class="button"><?php p($l->t('Latest L_RVI')); ?></button>
						<button id="farm-intelligence-platform-l-rvi-timeseries" type="button" class="button"><?php p($l->t('L_RVI Timeseries')); ?></button>
						<button id="farm-intelligence-platform-l-rvi-queue" type="button" class="button"><?php p($l->t('Queue L_RVI raster')); ?></button>
						<button id="farm-intelligence-platform-l-rvi-refresh" type="button" class="button"><?php p($l->t('Refresh L_RVI')); ?></button>
						<button id="farm-intelligence-platform-l-rvi-raster" type="button" class="button"><?php p($l->t('L_RVI Raster PNG')); ?></button>
						<button id="farm-intelligence-platform-l-rvi-geotiff" type="button" class="button"><?php p($l->t('L_RVI GeoTIFF')); ?></button>
						<button id="farm-intelligence-platform-l-rvi-state" type="button" class="button"><?php p($l->t('L_RVI State')); ?></button>
					</div>
					<div class="farm-intelligence-platform-farms__ndvi-row">
						<label for="farm-intelligence-platform-ndvi-start"><?php p($l->t('Start')); ?></label>
						<input id="farm-intelligence-platform-ndvi-start" type="date" />
						<label for="farm-intelligence-platform-ndvi-end"><?php p($l->t('End')); ?></label>
						<input id="farm-intelligence-platform-ndvi-end" type="date" />
						<label for="farm-intelligence-platform-ndvi-date"><?php p($l->t('Raster date')); ?></label>
						<input id="farm-intelligence-platform-ndvi-date" type="date" />

					</div>
					<div id="farm-intelligence-platform-ndvi-error" class="farm-intelligence-platform-farms__note error farm-intelligence-platform-farms__ndvi-error" hidden></div>
					<div id="farm-intelligence-platform-ndvi-output" class="farm-intelligence-platform-farms__ndvi-output"></div>
					<div id="farm-intelligence-platform-ndvi-calendar" class="farm-intelligence-platform-farms__ndvi-calendar" hidden>
						<div id="farm-intelligence-platform-ndvi-weekdays" class="farm-intelligence-platform-farms__ndvi-weekdays" aria-hidden="true"></div>
						<div id="farm-intelligence-platform-ndvi-calendar-grid" class="farm-intelligence-platform-farms__ndvi-calendar-grid" role="grid" aria-label="<?php p($l->t('NDVI date range calendar')); ?>"></div>
					</div>
					<div id="farm-intelligence-platform-ndvi-table" class="farm-intelligence-platform-farms__ndvi-table"></div>
				<div id="farm-intelligence-platform-ndvi-raster-preview" class="farm-intelligence-platform-farms__ndvi-preview" hidden>
					<img id="farm-intelligence-platform-ndvi-raster-img" alt="<?php p($l->t('NDVI raster preview')); ?>" />
				</div>
				<div id="farm-intelligence-platform-raster-controls" class="farm-intelligence-platform-farms__raster-controls" hidden>
					<label for="farm-intelligence-platform-raster-colormap"><?php p($l->t('Colormap')); ?></label>
					<select id="farm-intelligence-platform-raster-colormap">
						<option value="rdylgn">RdYlGn (vegetation)</option>
						<option value="brbg">BrBG (moisture)</option>
						<option value="viridis">Viridis</option>
					</select>
					<label for="farm-intelligence-platform-raster-date-slider"><?php p($l->t('Date')); ?></label>
					<input type="range" id="farm-intelligence-platform-raster-date-slider" min="0" max="0" value="0" disabled>
					<span id="farm-intelligence-platform-raster-date-label"></span>
				</div>
				<div id="farm-intelligence-platform-raster-map" class="farm-intelligence-platform-farms__raster-map" hidden></div>
					<div id="farm-intelligence-platform-farm-state-output" class="farm-intelligence-platform-farms__ndvi-output" hidden>
						<div id="farm-intelligence-platform-farm-state-content" class="farm-intelligence-platform-farms__farm-state-content"></div>
					</div>
					<div id="farm-intelligence-platform-decision-output" class="farm-intelligence-platform-farms__ndvi-output" hidden>
						<div id="farm-intelligence-platform-decision-content" class="farm-intelligence-platform-farms__farm-state-content"></div>
					</div>
				</div>
				<div class="farm-intelligence-platform-farms__weather" id="farm-intelligence-platform-farms-weather" hidden>
					<div class="farm-intelligence-platform-farms__weather-header">
						<strong><?php p($l->t('Weather')); ?></strong>
						<span id="farm-intelligence-platform-farms-weather-title" class="farm-intelligence-platform-farms__weather-title"></span>
					</div>
					<div class="farm-intelligence-platform-farms__weather-tabs">
						<button id="farm-intelligence-platform-weather-current-tab" type="button" class="button"><?php p($l->t('Current')); ?></button>
						<button id="farm-intelligence-platform-weather-hourly-tab" type="button" class="button"><?php p($l->t('Hourly')); ?></button>
						<button id="farm-intelligence-platform-weather-daily-tab" type="button" class="button"><?php p($l->t('Daily')); ?></button>
					</div>
					<div id="farm-intelligence-platform-weather-loading" class="farm-intelligence-platform-farms__note" hidden><?php p($l->t('Loading weather...')); ?></div>
					<div id="farm-intelligence-platform-weather-error" class="farm-intelligence-platform-farms__note error" hidden></div>
					<div id="farm-intelligence-platform-weather-current" class="farm-intelligence-platform-farms__weather-panel" hidden>
						<div id="farm-intelligence-platform-weather-current-grid" class="farm-intelligence-platform-farms__weather-grid"></div>
					</div>
					<div id="farm-intelligence-platform-weather-hourly" class="farm-intelligence-platform-farms__weather-panel" hidden>
						<div id="farm-intelligence-platform-weather-hourly-table" class="farm-intelligence-platform-farms__weather-table"></div>
					</div>
					<div id="farm-intelligence-platform-weather-daily" class="farm-intelligence-platform-farms__weather-panel" hidden>
						<div id="farm-intelligence-platform-weather-daily-table" class="farm-intelligence-platform-farms__weather-table"></div>
					</div>
				</div>
				<div class="farm-intelligence-platform-farms__observations" id="farm-intelligence-platform-farms-observations" hidden>
					<div class="farm-intelligence-platform-farms__weather-header">
						<strong><?php p($l->t('Observations')); ?></strong>
						<span id="farm-intelligence-platform-farms-observations-title" class="farm-intelligence-platform-farms__weather-title"></span>
					</div>
					<div class="farm-intelligence-platform-farms__ndvi-row">
						<label for="farm-intelligence-platform-observations-start"><?php p($l->t('Start')); ?></label>
						<input id="farm-intelligence-platform-observations-start" type="datetime-local" />
						<label for="farm-intelligence-platform-observations-end"><?php p($l->t('End')); ?></label>
						<input id="farm-intelligence-platform-observations-end" type="datetime-local" />
						<label for="farm-intelligence-platform-observations-type"><?php p($l->t('Event type')); ?></label>
						<input id="farm-intelligence-platform-observations-type" type="text" />
						<label for="farm-intelligence-platform-observations-limit"><?php p($l->t('Limit')); ?></label>
						<input id="farm-intelligence-platform-observations-limit" type="number" min="1" max="500" />
						<button id="farm-intelligence-platform-observations-refresh" type="button" class="button"><?php p($l->t('Refresh')); ?></button>
						<button id="farm-intelligence-platform-observations-create" type="button" class="button primary"><?php p($l->t('New observation')); ?></button>
					</div>
					<div id="farm-intelligence-platform-farms-observations-error" class="farm-intelligence-platform-farms__note error" hidden></div>
					<div id="farm-intelligence-platform-farms-observations-table" class="farm-intelligence-platform-farms__weather-table"></div>
					<div class="farm-intelligence-platform-farms__pagination" id="farm-intelligence-platform-farms-observations-pagination" hidden>
						<button id="farm-intelligence-platform-farms-observations-prev" type="button" class="button"><?php p($l->t('Previous')); ?></button>
						<div id="farm-intelligence-platform-farms-observations-page" class="farm-intelligence-platform-farms__page"></div>
						<button id="farm-intelligence-platform-farms-observations-next" type="button" class="button"><?php p($l->t('Next')); ?></button>
					</div>
				</div>
				<div class="farm-intelligence-platform-farms__activities" id="farm-intelligence-platform-farms-activities" hidden>
					<div class="farm-intelligence-platform-farms__weather-header">
						<strong><?php p($l->t('Activities')); ?></strong>
						<span id="farm-intelligence-platform-farms-activities-title" class="farm-intelligence-platform-farms__weather-title"></span>
					</div>
					<div class="farm-intelligence-platform-farms__ndvi-row">
						<label for="farm-intelligence-platform-activities-status"><?php p($l->t('Status')); ?></label>
						<input id="farm-intelligence-platform-activities-status" type="text" />
						<label for="farm-intelligence-platform-activities-type-filter"><?php p($l->t('Type')); ?></label>
						<input id="farm-intelligence-platform-activities-type-filter" type="text" />
						<label for="farm-intelligence-platform-activities-limit"><?php p($l->t('Limit')); ?></label>
						<input id="farm-intelligence-platform-activities-limit" type="number" min="1" max="500" />
						<button id="farm-intelligence-platform-activities-refresh" type="button" class="button"><?php p($l->t('Refresh')); ?></button>
						<button id="farm-intelligence-platform-activities-create" type="button" class="button primary"><?php p($l->t('New activity')); ?></button>
					</div>
					<div id="farm-intelligence-platform-farms-activities-error" class="farm-intelligence-platform-farms__note error" hidden></div>
					<div id="farm-intelligence-platform-farms-activities-table" class="farm-intelligence-platform-farms__weather-table"></div>
					<div class="farm-intelligence-platform-farms__pagination" id="farm-intelligence-platform-farms-activities-pagination" hidden>
						<button id="farm-intelligence-platform-farms-activities-prev" type="button" class="button"><?php p($l->t('Previous')); ?></button>
						<div id="farm-intelligence-platform-farms-activities-page" class="farm-intelligence-platform-farms__page"></div>
						<button id="farm-intelligence-platform-farms-activities-next" type="button" class="button"><?php p($l->t('Next')); ?></button>
					</div>
				</div>
			</div>
			<div class="farm-intelligence-platform-farms__modal" id="farm-intelligence-platform-farms-activity-modal" hidden>
				<div class="farm-intelligence-platform-farms__modal-card">
					<div class="farm-intelligence-platform-farms__modal-header">
						<strong id="farm-intelligence-platform-farms-activity-modal-title"><?php p($l->t('Activity')); ?></strong>
						<button id="farm-intelligence-platform-farms-activity-modal-close" type="button" class="button"><?php p($l->t('Close')); ?></button>
					</div>
					<div class="farm-intelligence-platform-farms__modal-body">
						<div id="farm-intelligence-platform-farms-activity-fields" class="farm-intelligence-platform-farms__modal-fields"></div>
					</div>
					<div class="farm-intelligence-platform-farms__modal-actions">
						<button id="farm-intelligence-platform-farms-activity-modal-save" type="button" class="button primary"><?php p($l->t('Save')); ?></button>
					</div>
				</div>
			</div>
			<div class="farm-intelligence-platform-farms__modal" id="farm-intelligence-platform-farms-modal" hidden>
				<div class="farm-intelligence-platform-farms__modal-card">
					<div class="farm-intelligence-platform-farms__modal-header">
						<strong id="farm-intelligence-platform-farms-modal-title"></strong>
						<button id="farm-intelligence-platform-farms-modal-close" type="button" class="button"><?php p($l->t('Close')); ?></button>
					</div>
					<div class="farm-intelligence-platform-farms__modal-body">
						<div id="farm-intelligence-platform-farms-modal-fields" class="farm-intelligence-platform-farms__modal-fields"></div>
					</div>
					<div class="farm-intelligence-platform-farms__modal-actions">
						<button id="farm-intelligence-platform-farms-modal-save" type="button" class="button primary"><?php p($l->t('Save')); ?></button>
					</div>
				</div>
			</div>
			<div class="farm-intelligence-platform-farms__modal" id="farm-intelligence-platform-farms-sync-modal" hidden>
				<div class="farm-intelligence-platform-farms__modal-card">
					<div class="farm-intelligence-platform-farms__modal-header">
						<strong id="farm-intelligence-platform-farms-sync-modal-title"><?php p($l->t('Sync Farm to DRF')); ?></strong>
						<button id="farm-intelligence-platform-farms-sync-modal-close" type="button" class="button"><?php p($l->t('Close')); ?></button>
					</div>
					<div class="farm-intelligence-platform-farms__modal-body">
						<p class="farm-intelligence-platform-farms__sync-description"><?php p($l->t('Sync this farm to the DRF backend. This will create or update the farm using external identifiers.')); ?></p>
						<div id="farm-intelligence-platform-farms-sync-fields" class="farm-intelligence-platform-farms__modal-fields">
							<div class="farm-intelligence-platform-farms__field">
								<label><?php p($l->t('External Farm ID')); ?></label>
								<input id="farm-intelligence-platform-sync-external-farm-id" type="text" readonly />
							</div>
							<div class="farm-intelligence-platform-farms__field">
								<label><?php p($l->t('External User ID')); ?></label>
								<input id="farm-intelligence-platform-sync-external-user-id" type="text" readonly />
							</div>
							<div class="farm-intelligence-platform-farms__field">
								<label><?php p($l->t('Farm Name')); ?></label>
								<input id="farm-intelligence-platform-sync-name" type="text" readonly />
							</div>
						</div>
						<p class="hint"><?php p($l->t('This action will sync the farm to the DRF backend using the integration token.')); ?></p>
					</div>
					<div class="farm-intelligence-platform-farms__modal-actions">
						<button id="farm-intelligence-platform-farms-sync-modal-cancel" type="button" class="button"><?php p($l->t('Cancel')); ?></button>
						<button id="farm-intelligence-platform-farms-sync-modal-confirm" type="button" class="button primary"><?php p($l->t('Sync Farm')); ?></button>
					</div>
				</div>
			</div>
			<div class="farm-intelligence-platform-farms__modal" id="farm-intelligence-platform-farms-observation-modal" hidden>
				<div class="farm-intelligence-platform-farms__modal-card">
					<div class="farm-intelligence-platform-farms__modal-header">
						<strong id="farm-intelligence-platform-farms-observation-modal-title"><?php p($l->t('Observation')); ?></strong>
						<button id="farm-intelligence-platform-farms-observation-modal-close" type="button" class="button"><?php p($l->t('Close')); ?></button>
					</div>
					<div class="farm-intelligence-platform-farms__modal-body">
						<div id="farm-intelligence-platform-farms-observation-fields" class="farm-intelligence-platform-farms__modal-fields">
							<div class="farm-intelligence-platform-farms__field">
								<label><?php p($l->t('Observed at')); ?></label>
								<input id="farm-intelligence-platform-observation-observed-at" type="datetime-local" />
							</div>
							<div class="farm-intelligence-platform-farms__field">
								<label><?php p($l->t('Event type')); ?></label>
								<select id="farm-intelligence-platform-observation-event-type">
									<option value=""><?php p($l->t('Select event type')); ?></option>
									<option value="planting"><?php p($l->t('Planting')); ?></option>
									<option value="harvest"><?php p($l->t('Harvest')); ?></option>
									<option value="irrigation"><?php p($l->t('Irrigation')); ?></option>
									<option value="fertilization"><?php p($l->t('Fertilization')); ?></option>
									<option value="pest_control"><?php p($l->t('Pest control')); ?></option>
									<option value="scouting"><?php p($l->t('Scouting')); ?></option>
									<option value="soil_test"><?php p($l->t('Soil test')); ?></option>
									<option value="weather_impact"><?php p($l->t('Weather impact')); ?></option>
									<option value="other"><?php p($l->t('Other')); ?></option>
								</select>
							</div>
							<div class="farm-intelligence-platform-farms__field">
								<label><?php p($l->t('Note')); ?></label>
								<input id="farm-intelligence-platform-observation-note" type="text" />
							</div>
							<div class="farm-intelligence-platform-farms__field-group-title"><?php p($l->t('Core metadata')); ?></div>
							<div class="farm-intelligence-platform-farms__field">
								<label><?php p($l->t('Source')); ?></label>
								<select id="farm-intelligence-platform-observation-source">
									<option value=""><?php p($l->t('Select source')); ?></option>
									<option value="manual"><?php p($l->t('Manual')); ?></option>
									<option value="sensor"><?php p($l->t('Sensor')); ?></option>
									<option value="integration"><?php p($l->t('Integration')); ?></option>
								</select>
							</div>
							<div class="farm-intelligence-platform-farms__field">
								<label><?php p($l->t('Observer')); ?></label>
								<input id="farm-intelligence-platform-observation-observer" type="text" />
							</div>
							<div class="farm-intelligence-platform-farms__field">
								<label><?php p($l->t('Crop')); ?></label>
								<input id="farm-intelligence-platform-observation-crop" type="text" />
							</div>
							<div class="farm-intelligence-platform-farms__field">
								<label><?php p($l->t('Variety')); ?></label>
								<input id="farm-intelligence-platform-observation-variety" type="text" />
							</div>
							<div class="farm-intelligence-platform-farms__field">
								<label><?php p($l->t('Growth stage')); ?></label>
								<select id="farm-intelligence-platform-observation-growth-stage">
									<option value=""><?php p($l->t('Select growth stage')); ?></option>
									<option value="preplant"><?php p($l->t('Preplant')); ?></option>
									<option value="emergence"><?php p($l->t('Emergence')); ?></option>
									<option value="vegetative"><?php p($l->t('Vegetative')); ?></option>
									<option value="flowering"><?php p($l->t('Flowering')); ?></option>
									<option value="fruiting"><?php p($l->t('Fruiting')); ?></option>
									<option value="maturity"><?php p($l->t('Maturity')); ?></option>
									<option value="postharvest"><?php p($l->t('Postharvest')); ?></option>
								</select>
							</div>
							<div class="farm-intelligence-platform-farms__field">
								<label><?php p($l->t('Area (ha)')); ?></label>
								<input id="farm-intelligence-platform-observation-area-ha" type="number" min="0" step="0.01" />
							</div>
							<div class="farm-intelligence-platform-farms__field">
								<label><?php p($l->t('Location note')); ?></label>
								<input id="farm-intelligence-platform-observation-location-note" type="text" />
							</div>
							<div class="farm-intelligence-platform-farms__field-group" data-event-types="planting">
								<div class="farm-intelligence-platform-farms__field-group-title"><?php p($l->t('Planting details')); ?></div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('Seed rate (kg/ha)')); ?></label>
									<input id="farm-intelligence-platform-observation-seed-rate" type="number" min="0" step="0.01" />
								</div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('Planting method')); ?></label>
									<select id="farm-intelligence-platform-observation-planting-method">
										<option value=""><?php p($l->t('Select method')); ?></option>
										<option value="broadcast"><?php p($l->t('Broadcast')); ?></option>
										<option value="row"><?php p($l->t('Row')); ?></option>
										<option value="transplant"><?php p($l->t('Transplant')); ?></option>
									</select>
								</div>
							</div>
							<div class="farm-intelligence-platform-farms__field-group" data-event-types="irrigation">
								<div class="farm-intelligence-platform-farms__field-group-title"><?php p($l->t('Irrigation details')); ?></div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('Irrigation type')); ?></label>
									<select id="farm-intelligence-platform-observation-irrigation-type">
										<option value=""><?php p($l->t('Select type')); ?></option>
										<option value="drip"><?php p($l->t('Drip')); ?></option>
										<option value="sprinkler"><?php p($l->t('Sprinkler')); ?></option>
										<option value="flood"><?php p($l->t('Flood')); ?></option>
										<option value="other"><?php p($l->t('Other')); ?></option>
									</select>
								</div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('Water applied (mm)')); ?></label>
									<input id="farm-intelligence-platform-observation-water-mm" type="number" min="0" step="0.1" />
								</div>
							</div>
							<div class="farm-intelligence-platform-farms__field-group" data-event-types="fertilization">
								<div class="farm-intelligence-platform-farms__field-group-title"><?php p($l->t('Fertilization details')); ?></div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('Fertilizer type')); ?></label>
									<input id="farm-intelligence-platform-observation-fertilizer-type" type="text" />
								</div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('N (kg/ha)')); ?></label>
									<input id="farm-intelligence-platform-observation-nutrient-n" type="number" min="0" step="0.01" />
								</div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('P (kg/ha)')); ?></label>
									<input id="farm-intelligence-platform-observation-nutrient-p" type="number" min="0" step="0.01" />
								</div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('K (kg/ha)')); ?></label>
									<input id="farm-intelligence-platform-observation-nutrient-k" type="number" min="0" step="0.01" />
								</div>
							</div>
							<div class="farm-intelligence-platform-farms__field-group" data-event-types="pest_control">
								<div class="farm-intelligence-platform-farms__field-group-title"><?php p($l->t('Pest control details')); ?></div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('Pest')); ?></label>
									<input id="farm-intelligence-platform-observation-pest" type="text" />
								</div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('Product')); ?></label>
									<input id="farm-intelligence-platform-observation-product" type="text" />
								</div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('Dose (ml/ha)')); ?></label>
									<input id="farm-intelligence-platform-observation-dose" type="number" min="0" step="0.01" />
								</div>
							</div>
							<div class="farm-intelligence-platform-farms__field-group" data-event-types="harvest">
								<div class="farm-intelligence-platform-farms__field-group-title"><?php p($l->t('Harvest details')); ?></div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('Yield (kg)')); ?></label>
									<input id="farm-intelligence-platform-observation-yield" type="number" min="0" step="0.01" />
								</div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('Moisture (%%)')); ?></label>
									<input id="farm-intelligence-platform-observation-moisture" type="number" min="0" max="100" step="0.1" />
								</div>
							</div>
							<div class="farm-intelligence-platform-farms__field-group" data-event-types="scouting,soil_test">
								<div class="farm-intelligence-platform-farms__field-group-title"><?php p($l->t('Scouting / soil details')); ?></div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('Pest pressure')); ?></label>
									<select id="farm-intelligence-platform-observation-pest-pressure">
										<option value=""><?php p($l->t('Select pressure')); ?></option>
										<option value="none"><?php p($l->t('None')); ?></option>
										<option value="low"><?php p($l->t('Low')); ?></option>
										<option value="medium"><?php p($l->t('Medium')); ?></option>
										<option value="high"><?php p($l->t('High')); ?></option>
									</select>
								</div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('Soil pH')); ?></label>
									<input id="farm-intelligence-platform-observation-soil-ph" type="number" min="0" max="14" step="0.01" />
								</div>
								<div class="farm-intelligence-platform-farms__field">
									<label><?php p($l->t('Organic matter (%%)')); ?></label>
									<input id="farm-intelligence-platform-observation-organic-matter" type="number" min="0" max="100" step="0.1" />
								</div>
							</div>
						</div>
					</div>
					<div class="farm-intelligence-platform-farms__modal-actions">
						<button id="farm-intelligence-platform-farms-observation-modal-save" type="button" class="button primary"><?php p($l->t('Save')); ?></button>
					</div>
				</div>
			</div>
		</div>

		<div class="form-group farm-intelligence-platform-radio" id="farm-intelligence-platform-radio">
			<div class="farm-intelligence-platform-radio__header">
				<div>
					<strong><?php p($l->t('Radio')); ?></strong>
					<p class="hint"><?php p($l->t('Browse and play radio stations from the DRF backend.')); ?></p>
				</div>
				<div class="farm-intelligence-platform-radio__actions">
					<button id="farm-intelligence-platform-radio-refresh" type="button" class="button"><?php p($l->t('Refresh')); ?></button>
				</div>
			</div>
			<div id="farm-intelligence-platform-radio-emergency" class="farm-intelligence-platform-radio__emergency" hidden>
				<div class="farm-intelligence-platform-radio__emergency-header">
					<strong id="farm-intelligence-platform-radio-emergency-title"></strong>
					<span id="farm-intelligence-platform-radio-emergency-priority" class="farm-intelligence-platform-radio__priority"></span>
				</div>
				<p id="farm-intelligence-platform-radio-emergency-message" class="farm-intelligence-platform-radio__emergency-message"></p>
				<div class="farm-intelligence-platform-radio__emergency-meta">
					<span id="farm-intelligence-platform-radio-emergency-window"></span>
					<button id="farm-intelligence-platform-radio-emergency-history-btn" type="button" class="button"><?php p($l->t('View history')); ?></button>
				</div>
			</div>
			<div id="farm-intelligence-platform-radio-health" class="farm-intelligence-platform-radio__health" hidden>
				<div class="farm-intelligence-platform-radio__health-header">
					<strong><?php p($l->t('Radio system health')); ?></strong>
					<span id="farm-intelligence-platform-radio-health-status" class="farm-intelligence-platform-radio__health-status"></span>
				</div>
				<div class="farm-intelligence-platform-radio__health-grid">
					<div class="farm-intelligence-platform-radio__health-card">
						<span class="farm-intelligence-platform-radio__health-label"><?php p($l->t('Total stations')); ?></span>
						<strong id="farm-intelligence-platform-radio-health-total">—</strong>
					</div>
					<div class="farm-intelligence-platform-radio__health-card">
						<span class="farm-intelligence-platform-radio__health-label"><?php p($l->t('Available')); ?></span>
						<strong id="farm-intelligence-platform-radio-health-available">—</strong>
					</div>
					<div class="farm-intelligence-platform-radio__health-card">
						<span class="farm-intelligence-platform-radio__health-label"><?php p($l->t('Unavailable')); ?></span>
						<strong id="farm-intelligence-platform-radio-health-unavailable">—</strong>
					</div>
					<div class="farm-intelligence-platform-radio__health-card">
						<span class="farm-intelligence-platform-radio__health-label"><?php p($l->t('Last probe')); ?></span>
						<strong id="farm-intelligence-platform-radio-health-last-probe">—</strong>
					</div>
				</div>
			</div>
			<div class="farm-intelligence-platform-radio__tabs">
				<button id="farm-intelligence-platform-radio-stations-tab" type="button" class="button primary"><?php p($l->t('Stations')); ?></button>
				<button id="farm-intelligence-platform-radio-providers-tab" type="button" class="button"><?php p($l->t('Providers')); ?></button>
			</div>
			<div id="farm-intelligence-platform-radio-loading" class="farm-intelligence-platform-radio__note" hidden><?php p($l->t('Loading radio...')); ?></div>
			<div id="farm-intelligence-platform-radio-error" class="farm-intelligence-platform-radio__note error" hidden></div>
			<div id="farm-intelligence-platform-radio-stations" class="farm-intelligence-platform-radio__panel">
				<div class="farm-intelligence-platform-radio__filters">
					<label for="farm-intelligence-platform-radio-search"><?php p($l->t('Search')); ?></label>
					<input id="farm-intelligence-platform-radio-search" type="text" placeholder="<?php p($l->t('Filter by name, genre, or country')); ?>" />
					<label for="farm-intelligence-platform-radio-genre-filter"><?php p($l->t('Genre')); ?></label>
					<select id="farm-intelligence-platform-radio-genre-filter">
						<option value=""><?php p($l->t('All genres')); ?></option>
					</select>
					<label for="farm-intelligence-platform-radio-country-filter"><?php p($l->t('Country')); ?></label>
					<select id="farm-intelligence-platform-radio-country-filter">
						<option value=""><?php p($l->t('All countries')); ?></option>
					</select>
				</div>
				<div class="farm-intelligence-platform-radio__table-wrap">
					<table class="farm-intelligence-platform-radio__table">
						<thead>
							<tr id="farm-intelligence-platform-radio-stations-columns"></tr>
						</thead>
						<tbody id="farm-intelligence-platform-radio-stations-body"></tbody>
					</table>
				</div>
				<div id="farm-intelligence-platform-radio-stations-empty" hidden>
					<p><?php p($l->t('No stations found.')); ?></p>
				</div>
			</div>
			<div id="farm-intelligence-platform-radio-station-modal" class="farm-intelligence-platform-radio__modal" hidden>
				<div class="farm-intelligence-platform-radio__modal-card farm-intelligence-platform-radio__modal-card--wide">
					<button id="farm-intelligence-platform-radio-station-modal-close" type="button" class="farm-intelligence-platform-radio__modal-close" aria-label="<?php p($l->t('Close')); ?>">
						<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
					</button>
					<div class="farm-intelligence-platform-radio__station-header">
						<img id="farm-intelligence-platform-radio-station-modal-logo" src="" alt="" class="farm-intelligence-platform-radio__station-logo" hidden />
						<div>
							<strong id="farm-intelligence-platform-radio-station-modal-name"></strong>
							<div class="farm-intelligence-platform-radio__station-meta">
								<span id="farm-intelligence-platform-radio-station-modal-provider"></span>
								<span id="farm-intelligence-platform-radio-station-modal-genre"></span>
								<span id="farm-intelligence-platform-radio-station-modal-country"></span>
							</div>
							<p id="farm-intelligence-platform-radio-station-modal-description" class="farm-intelligence-platform-radio__station-description" hidden></p>
						</div>
					</div>
					<div class="farm-intelligence-platform-radio__station-tabs">
						<button id="farm-intelligence-platform-radio-station-tab-now-playing" type="button" class="button primary"><?php p($l->t('Now playing')); ?></button>
						<button id="farm-intelligence-platform-radio-station-tab-analytics" type="button" class="button"><?php p($l->t('Analytics')); ?></button>
						<button id="farm-intelligence-platform-radio-station-tab-health" type="button" class="button"><?php p($l->t('Health')); ?></button>
					</div>
					<div id="farm-intelligence-platform-radio-station-loading" class="farm-intelligence-platform-radio__note" hidden><?php p($l->t('Loading...')); ?></div>
					<div id="farm-intelligence-platform-radio-station-error" class="farm-intelligence-platform-radio__note error" hidden></div>
					<div id="farm-intelligence-platform-radio-station-panel-now-playing" class="farm-intelligence-platform-radio__station-panel">
						<div class="farm-intelligence-platform-radio__now-playing">
							<div class="farm-intelligence-platform-radio__now-playing-art" id="farm-intelligence-platform-radio-station-now-playing-art">
								<div class="farm-intelligence-platform-radio__now-playing-icon">
									<svg viewBox="0 0 24 24" width="48" height="48"><path fill="currentColor" d="M12 3v10.55A4 4 0 1 0 14 17V7h4V3h-6Z"/></svg>
								</div>
							</div>
							<div class="farm-intelligence-platform-radio__now-playing-meta">
								<strong id="farm-intelligence-platform-radio-station-now-playing-track">—</strong>
								<span id="farm-intelligence-platform-radio-station-now-playing-artist">—</span>
								<span id="farm-intelligence-platform-radio-station-now-playing-album">—</span>
								<span id="farm-intelligence-platform-radio-station-now-playing-updated" class="farm-intelligence-platform-radio__now-playing-updated"></span>
							</div>
						</div>
						<div id="farm-intelligence-platform-radio-station-now-playing-empty" class="farm-intelligence-platform-radio__note" hidden>
							<p><?php p($l->t('No track metadata available for this station.')); ?></p>
						</div>
					</div>
					<div id="farm-intelligence-platform-radio-station-panel-analytics" class="farm-intelligence-platform-radio__station-panel" hidden>
						<div class="farm-intelligence-platform-radio__analytics-controls">
							<label for="farm-intelligence-platform-radio-station-analytics-days"><?php p($l->t('Days')); ?></label>
							<select id="farm-intelligence-platform-radio-station-analytics-days">
								<option value="7">7</option>
								<option value="14">14</option>
								<option value="30" selected>30</option>
								<option value="60">60</option>
								<option value="90">90</option>
							</select>
							<button id="farm-intelligence-platform-radio-station-analytics-refresh" type="button" class="button"><?php p($l->t('Refresh')); ?></button>
						</div>
						<div class="farm-intelligence-platform-radio__analytics-grid">
							<div class="farm-intelligence-platform-radio__analytics-card">
								<span class="farm-intelligence-platform-radio__analytics-label"><?php p($l->t('Total listens')); ?></span>
								<strong id="farm-intelligence-platform-radio-station-analytics-total-listens">—</strong>
							</div>
							<div class="farm-intelligence-platform-radio__analytics-card">
								<span class="farm-intelligence-platform-radio__analytics-label"><?php p($l->t('Total duration')); ?></span>
								<strong id="farm-intelligence-platform-radio-station-analytics-total-duration">—</strong>
							</div>
							<div class="farm-intelligence-platform-radio__analytics-card">
								<span class="farm-intelligence-platform-radio__analytics-label"><?php p($l->t('Unique users')); ?></span>
								<strong id="farm-intelligence-platform-radio-station-analytics-unique-users">—</strong>
							</div>
						</div>
						<div class="farm-intelligence-platform-radio__table-wrap">
							<table class="farm-intelligence-platform-radio__table">
								<thead>
									<tr>
										<th><?php p($l->t('Date')); ?></th>
										<th><?php p($l->t('Listens')); ?></th>
										<th><?php p($l->t('Duration (s)')); ?></th>
										<th><?php p($l->t('Unique users')); ?></th>
									</tr>
								</thead>
								<tbody id="farm-intelligence-platform-radio-station-analytics-body"></tbody>
							</table>
						</div>
						<div id="farm-intelligence-platform-radio-station-analytics-empty" class="farm-intelligence-platform-radio__note" hidden>
							<p><?php p($l->t('No analytics rows in this window.')); ?></p>
						</div>
					</div>
					<div id="farm-intelligence-platform-radio-station-panel-health" class="farm-intelligence-platform-radio__station-panel" hidden>
						<div id="farm-intelligence-platform-radio-station-health-current" class="farm-intelligence-platform-radio__health-current">
							<div class="farm-intelligence-platform-radio__health-card">
								<span class="farm-intelligence-platform-radio__health-label"><?php p($l->t('Status')); ?></span>
								<strong id="farm-intelligence-platform-radio-station-health-status">—</strong>
							</div>
							<div class="farm-intelligence-platform-radio__health-card">
								<span class="farm-intelligence-platform-radio__health-label"><?php p($l->t('Last probe')); ?></span>
								<strong id="farm-intelligence-platform-radio-station-health-last-probe">—</strong>
							</div>
							<div class="farm-intelligence-platform-radio__health-card">
								<span class="farm-intelligence-platform-radio__health-label"><?php p($l->t('Latency (ms)')); ?></span>
								<strong id="farm-intelligence-platform-radio-station-health-latency">—</strong>
							</div>
							<div class="farm-intelligence-platform-radio__health-card">
								<span class="farm-intelligence-platform-radio__health-label"><?php p($l->t('HTTP status')); ?></span>
								<strong id="farm-intelligence-platform-radio-station-health-http">—</strong>
							</div>
						</div>
						<div class="farm-intelligence-platform-radio__table-wrap">
							<table class="farm-intelligence-platform-radio__table">
								<thead>
									<tr>
										<th><?php p($l->t('Checked at')); ?></th>
										<th><?php p($l->t('Reachable')); ?></th>
										<th><?php p($l->t('HTTP')); ?></th>
										<th><?php p($l->t('Latency (ms)')); ?></th>
										<th><?php p($l->t('Error')); ?></th>
									</tr>
								</thead>
								<tbody id="farm-intelligence-platform-radio-station-health-history-body"></tbody>
							</table>
						</div>
						<div id="farm-intelligence-platform-radio-station-health-history-empty" class="farm-intelligence-platform-radio__note" hidden>
							<p><?php p($l->t('No health-check history yet.')); ?></p>
						</div>
					</div>
				</div>
			</div>
			<div id="farm-intelligence-platform-radio-emergency-history-modal" class="farm-intelligence-platform-radio__modal" hidden>
				<div class="farm-intelligence-platform-radio__modal-card farm-intelligence-platform-radio__modal-card--wide">
					<button id="farm-intelligence-platform-radio-emergency-history-modal-close" type="button" class="farm-intelligence-platform-radio__modal-close" aria-label="<?php p($l->t('Close')); ?>">
						<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
					</button>
					<strong class="farm-intelligence-platform-radio__modal-title"><?php p($l->t('Emergency broadcast history')); ?></strong>
					<div id="farm-intelligence-platform-radio-emergency-history-loading" class="farm-intelligence-platform-radio__note" hidden><?php p($l->t('Loading...')); ?></div>
					<div class="farm-intelligence-platform-radio__table-wrap">
						<table class="farm-intelligence-platform-radio__table">
							<thead>
								<tr>
									<th><?php p($l->t('Title')); ?></th>
									<th><?php p($l->t('Priority')); ?></th>
									<th><?php p($l->t('Starts')); ?></th>
									<th><?php p($l->t('Ends')); ?></th>
									<th><?php p($l->t('Active')); ?></th>
								</tr>
							</thead>
							<tbody id="farm-intelligence-platform-radio-emergency-history-body"></tbody>
						</table>
					</div>
					<div id="farm-intelligence-platform-radio-emergency-history-empty" class="farm-intelligence-platform-radio__note" hidden>
						<p><?php p($l->t('No past emergencies.')); ?></p>
					</div>
				</div>
			</div>
			<div id="farm-intelligence-platform-radio-emergency-modal" class="farm-intelligence-platform-radio__modal" hidden>
				<div class="farm-intelligence-platform-radio__modal-card">
					<button id="farm-intelligence-platform-radio-emergency-modal-close" type="button" class="farm-intelligence-platform-radio__modal-close" aria-label="<?php p($l->t('Close')); ?>">
						<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
					</button>
					<strong class="farm-intelligence-platform-radio__modal-title" id="farm-intelligence-platform-radio-emergency-modal-title"><?php p($l->t('Create Emergency Broadcast')); ?></strong>
					<div class="farm-intelligence-platform-radio__modal-form">
						<div class="farm-intelligence-platform-radio__form-group">
							<label for="farm-intelligence-platform-radio-emergency-modal-title-input"><?php p($l->t('Title')); ?></label>
							<input id="farm-intelligence-platform-radio-emergency-modal-title-input" type="text" required />
						</div>
						<div class="farm-intelligence-platform-radio__form-group">
							<label for="farm-intelligence-platform-radio-emergency-modal-message-input"><?php p($l->t('Message')); ?></label>
							<textarea id="farm-intelligence-platform-radio-emergency-modal-message-input" rows="3"></textarea>
						</div>
						<div class="farm-intelligence-platform-radio__form-group">
							<label for="farm-intelligence-platform-radio-emergency-modal-priority-select"><?php p($l->t('Priority')); ?></label>
							<select id="farm-intelligence-platform-radio-emergency-modal-priority-select">
								<option value="low"><?php p($l->t('Low')); ?></option>
								<option value="medium"><?php p($l->t('Medium')); ?></option>
								<option value="high" selected><?php p($l->t('High')); ?></option>
								<option value="critical"><?php p($l->t('Critical')); ?></option>
							</select>
						</div>
						<div class="farm-intelligence-platform-radio__form-group">
							<label for="farm-intelligence-platform-radio-emergency-modal-starts-input"><?php p($l->t('Starts at')); ?></label>
							<input id="farm-intelligence-platform-radio-emergency-modal-starts-input" type="datetime-local" />
						</div>
						<div class="farm-intelligence-platform-radio__form-group">
							<label for="farm-intelligence-platform-radio-emergency-modal-ends-input"><?php p($l->t('Ends at')); ?></label>
							<input id="farm-intelligence-platform-radio-emergency-modal-ends-input" type="datetime-local" />
						</div>
					</div>
					<div class="farm-intelligence-platform-radio__modal-actions">
						<button id="farm-intelligence-platform-radio-emergency-modal-save" type="button" class="button primary"><?php p($l->t('Save')); ?></button>
						<button id="farm-intelligence-platform-radio-emergency-modal-cancel" type="button" class="button"><?php p($l->t('Cancel')); ?></button>
					</div>
					<div id="farm-intelligence-platform-radio-emergency-modal-error" class="farm-intelligence-platform-radio__note error" hidden></div>
				</div>
			</div>

			<div id="farm-intelligence-platform-radio-emergency-mgmt" class="farm-intelligence-platform-radio__section">
				<div class="farm-intelligence-platform-radio__section-header">
					<strong><?php p($l->t('Emergency Broadcasts')); ?></strong>
					<div class="farm-intelligence-platform-radio__actions">
						<button id="farm-intelligence-platform-radio-emergency-create-btn" type="button" class="button"><?php p($l->t('Create Broadcast')); ?></button>
						<button id="farm-intelligence-platform-radio-emergency-mgmt-refresh" type="button" class="button"><?php p($l->t('Refresh')); ?></button>
					</div>
				</div>
				<div class="farm-intelligence-platform-radio__table-wrap">
					<table class="farm-intelligence-platform-radio__table">
						<thead>
							<tr>
								<th><?php p($l->t('ID')); ?></th>
								<th><?php p($l->t('Title')); ?></th>
								<th><?php p($l->t('Priority')); ?></th>
								<th><?php p($l->t('Active')); ?></th>
								<th><?php p($l->t('Starts')); ?></th>
								<th><?php p($l->t('Ends')); ?></th>
								<th><?php p($l->t('Actions')); ?></th>
							</tr>
						</thead>
						<tbody id="farm-intelligence-platform-radio-emergency-mgmt-body"></tbody>
					</table>
				</div>
				<div id="farm-intelligence-platform-radio-emergency-mgmt-empty" class="farm-intelligence-platform-radio__note" hidden>
					<p><?php p($l->t('No emergency broadcasts yet.')); ?></p>
				</div>
			</div>

			<div id="farm-intelligence-platform-radio-tts" class="farm-intelligence-platform-radio__section">
				<div class="farm-intelligence-platform-radio__section-header">
					<strong><?php p($l->t('Text-to-Speech')); ?></strong>
				</div>
				<div class="farm-intelligence-platform-radio__tts-form">
					<div class="farm-intelligence-platform-radio__tts-row">
						<label for="farm-intelligence-platform-radio-tts-text"><?php p($l->t('Text')); ?></label>
						<textarea id="farm-intelligence-platform-radio-tts-text" rows="3" placeholder="<?php p($l->t('Enter text to synthesize...')); ?>"></textarea>
					</div>
					<div class="farm-intelligence-platform-radio__tts-row">
						<label for="farm-intelligence-platform-radio-tts-voice"><?php p($l->t('Voice')); ?></label>
						<select id="farm-intelligence-platform-radio-tts-voice">
							<option value="en-US">en-US</option>
							<option value="en-GB">en-GB</option>
							<option value="fr-FR">fr-FR</option>
							<option value="de-DE">de-DE</option>
							<option value="es-ES">es-ES</option>
						</select>
					</div>
					<div class="farm-intelligence-platform-radio__tts-actions">
						<button id="farm-intelligence-platform-radio-tts-synthesize-btn" type="button" class="button primary"><?php p($l->t('Synthesize')); ?></button>
						<button id="farm-intelligence-platform-radio-tts-download-btn" type="button" class="button" hidden><?php p($l->t('Download WAV')); ?></button>
					</div>
					<div id="farm-intelligence-platform-radio-tts-duration" class="farm-intelligence-platform-radio__tts-duration" hidden></div>
					<audio id="farm-intelligence-platform-radio-tts-audio" controls hidden></audio>
					<div id="farm-intelligence-platform-radio-tts-error" class="farm-intelligence-platform-radio__note error" hidden></div>
				</div>
			</div>

			<div id="farm-intelligence-platform-radio-providers" class="farm-intelligence-platform-radio__panel" hidden>
				<div class="farm-intelligence-platform-radio__table-wrap">
					<table class="farm-intelligence-platform-radio__table">
						<thead>
							<tr id="farm-intelligence-platform-radio-providers-columns"></tr>
						</thead>
						<tbody id="farm-intelligence-platform-radio-providers-body"></tbody>
					</table>
				</div>
				<div id="farm-intelligence-platform-radio-providers-empty" hidden>
					<p><?php p($l->t('No providers found.')); ?></p>
				</div>
			</div>
			<div class="farm-intelligence-platform-radio__player" id="farm-intelligence-platform-radio-player" hidden>
				<div class="farm-intelligence-platform-radio__player-bar">
					<img id="farm-intelligence-platform-radio-bar-logo" src="" alt="" class="farm-intelligence-platform-radio__bar-logo" hidden />
					<span id="farm-intelligence-platform-radio-bar-title" class="farm-intelligence-platform-radio__bar-title"></span>
					<span id="farm-intelligence-platform-radio-bar-time" class="farm-intelligence-platform-radio__bar-time">0:00</span>
					<span id="farm-intelligence-platform-radio-bar-live" class="farm-intelligence-platform-radio__bar-live" aria-live="polite"><?php p($l->t('LIVE')); ?></span>
					<button id="farm-intelligence-platform-radio-bar-rewind" type="button" class="farm-intelligence-platform-radio__bar-btn" aria-label="<?php p($l->t('Rewind 10 seconds')); ?>">
						<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M12.5 3C17.15 3 21.08 6.03 22.47 10.22L20.08 11C18.92 7.68 15.96 5.25 12.5 5.25C8.36 5.25 5 8.61 5 12.75C5 14.06 5.34 15.29 5.93 16.36L10.5 11.79V15.5H6.79L3.29 12L6.79 8.5H10.5V5.79L12.5 3Z"/><text x="9" y="16" font-size="7" fill="currentColor" font-weight="bold">10</text></svg>
					</button>
					<button id="farm-intelligence-platform-radio-bar-forward" type="button" class="farm-intelligence-platform-radio__bar-btn" aria-label="<?php p($l->t('Forward 10 seconds')); ?>">
						<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M11.5 3C6.85 3 2.92 6.03 1.53 10.22L3.92 11C5.08 7.68 8.04 5.25 11.5 5.25C15.64 5.25 19 8.61 19 12.75C19 14.06 18.66 15.29 18.07 16.36L13.5 11.79V15.5H17.21L20.71 12L17.21 8.5H13.5V5.79L11.5 3Z"/><text x="12" y="16" font-size="7" fill="currentColor" font-weight="bold">10</text></svg>
					</button>
					<button id="farm-intelligence-platform-radio-bar-play" type="button" class="farm-intelligence-platform-radio__bar-btn" aria-label="<?php p($l->t('Play')); ?>">
						<svg id="farm-intelligence-platform-radio-bar-icon-play" viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M8 5v14l11-7Z"/></svg>
						<svg id="farm-intelligence-platform-radio-bar-icon-pause" viewBox="0 0 24 24" width="20" height="20" hidden><path fill="currentColor" d="M6 19h4V5H6v14Zm8-14v14h4V5h-4Z"/></svg>
					</button>
					<button id="farm-intelligence-platform-radio-bar-expand" type="button" class="farm-intelligence-platform-radio__bar-btn" aria-label="<?php p($l->t('Expand')); ?>">
						<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M7 14l5-5 5 5H7Z"/></svg>
					</button>
					<button id="farm-intelligence-platform-radio-bar-close" type="button" class="farm-intelligence-platform-radio__bar-btn" aria-label="<?php p($l->t('Close')); ?>">
						<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
					</button>
				</div>
				<div class="farm-intelligence-platform-radio__progress-bar" id="farm-intelligence-platform-radio-progress-container">
					<div class="farm-intelligence-platform-radio__progress-track" id="farm-intelligence-platform-radio-progress-track">
						<div class="farm-intelligence-platform-radio__progress-fill" id="farm-intelligence-platform-radio-progress-fill"></div>
					</div>
				</div>
				<div class="farm-intelligence-platform-radio__modal" id="farm-intelligence-platform-radio-player-modal" hidden>
					<div class="farm-intelligence-platform-radio__modal-card">
						<button id="farm-intelligence-platform-radio-player-close" type="button" class="farm-intelligence-platform-radio__modal-close" aria-label="<?php p($l->t('Close')); ?>">
							<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
						</button>
						<button id="farm-intelligence-platform-radio-player-minimize" type="button" class="farm-intelligence-platform-radio__modal-minimize" aria-label="<?php p($l->t('Minimize')); ?>">
							<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M7 10l5 5 5-5H7Z"/></svg>
						</button>
						<div class="farm-intelligence-platform-radio__modal-body">
							<div class="farm-intelligence-platform-radio__player-art">
								<img id="farm-intelligence-platform-radio-player-logo" src="" alt="" hidden />
								<div id="farm-intelligence-platform-radio-player-icon" class="farm-intelligence-platform-radio__player-icon">
									<svg viewBox="0 0 24 24" width="48" height="48"><path fill="currentColor" d="M12 3v10.55A4 4 0 1 0 14 17V7h4V3h-6Z"/></svg>
								</div>
							</div>
							<div class="farm-intelligence-platform-radio__player-meta">
								<strong id="farm-intelligence-platform-radio-player-title"><?php p($l->t('Now Playing')); ?></strong>
								<span id="farm-intelligence-platform-radio-player-subtitle" class="farm-intelligence-platform-radio__player-subtitle"></span>
								<span id="farm-intelligence-platform-radio-player-time" class="farm-intelligence-platform-radio__player-time">0:00</span>
								<span id="farm-intelligence-platform-radio-player-live" class="farm-intelligence-platform-radio__bar-live"><?php p($l->t('LIVE')); ?></span>
							</div>
							<div class="farm-intelligence-platform-radio__player-controls">
								<button id="farm-intelligence-platform-radio-player-rewind" type="button" class="farm-intelligence-platform-radio__skip-btn" aria-label="<?php p($l->t('Rewind 10 seconds')); ?>">
									<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M12.5 3C17.15 3 21.08 6.03 22.47 10.22L20.08 11C18.92 7.68 15.96 5.25 12.5 5.25C8.36 5.25 5 8.61 5 12.75C5 14.06 5.34 15.29 5.93 16.36L10.5 11.79V15.5H6.79L3.29 12L6.79 8.5H10.5V5.79L12.5 3Z"/><text x="9" y="16" font-size="7" fill="currentColor" font-weight="bold">10</text></svg>
								</button>
								<button id="farm-intelligence-platform-radio-player-play" type="button" class="farm-intelligence-platform-radio__play-btn" aria-label="<?php p($l->t('Play')); ?>">
									<svg id="farm-intelligence-platform-radio-icon-play" viewBox="0 0 24 24" width="32" height="32"><path fill="currentColor" d="M8 5v14l11-7Z"/></svg>
									<svg id="farm-intelligence-platform-radio-icon-pause" viewBox="0 0 24 24" width="32" height="32" hidden><path fill="currentColor" d="M6 19h4V5H6v14Zm8-14v14h4V5h-4Z"/></svg>
								</button>
								<button id="farm-intelligence-platform-radio-player-forward" type="button" class="farm-intelligence-platform-radio__skip-btn" aria-label="<?php p($l->t('Forward 10 seconds')); ?>">
									<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M11.5 3C6.85 3 2.92 6.03 1.53 10.22L3.92 11C5.08 7.68 8.04 5.25 11.5 5.25C15.64 5.25 19 8.61 19 12.75C19 14.06 18.66 15.29 18.07 16.36L13.5 11.79V15.5H17.21L20.71 12L17.21 8.5H13.5V5.79L11.5 3Z"/><text x="12" y="16" font-size="7" fill="currentColor" font-weight="bold">10</text></svg>
								</button>
								<div class="farm-intelligence-platform-radio__volume">
									<svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M3 9v6h4l5 5V4L7 9H3Z"/></svg>
									<input id="farm-intelligence-platform-radio-volume" type="range" min="0" max="100" value="80" class="farm-intelligence-platform-radio__volume-slider" aria-label="<?php p($l->t('Volume')); ?>" />
								</div>
							</div>
							<div class="farm-intelligence-platform-radio__player-progress" id="farm-intelligence-platform-radio-modal-progress">
								<div class="farm-intelligence-platform-radio__progress-track" id="farm-intelligence-platform-radio-modal-progress-track">
									<div class="farm-intelligence-platform-radio__progress-fill" id="farm-intelligence-platform-radio-modal-progress-fill"></div>
								</div>
							</div>
							<audio id="farm-intelligence-platform-radio-audio" hidden></audio>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group" style="margin-top:8px">
				<label for="farm-intelligence-platform-timeout"><?php p($l->t('Timeout seconds')); ?></label>
				<input id="farm-intelligence-platform-timeout" type="number" name="timeoutSeconds" min="1" max="30" value="<?php p($_['timeoutSeconds']); ?>" required />
			</div>
		</div>

		<hr>

		<div class="form-group">
			<label for="farm-intelligence-platform-dev-allow-http">
				<input id="farm-intelligence-platform-dev-allow-http" type="checkbox" name="devAllowHttp" value="1" <?php p($_['devAllowHttp'] ? 'checked' : ''); ?> />
				<?php p($l->t('Dev: allow insecure local HTTP')); ?>
			</label>
		</div>

		<div class="form-group">
			<label for="farm-intelligence-platform-allowlist"><?php p($l->t('Dev: allowlist hosts')); ?></label>
			<textarea id="farm-intelligence-platform-allowlist" name="allowlistHosts" rows="3"><?php p($_['allowlistHosts']); ?></textarea>
		</div>

		<input type="submit" class="button primary" value="<?php p($l->t('Save')); ?>" />
		<div id="farm-intelligence-platform-settings-status" class="status farm-intelligence-platform-settings__status"></div>
	</form>
</div>
