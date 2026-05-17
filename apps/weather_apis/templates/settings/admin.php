<?php
style('weather_apis', 'admin-settings');
?>
<div id="weather-apis-settings-root" class="section weather-apis-settings">
	<h1><?php p($l->t('Weather APIs')); ?></h1>

	<form id="weather-apis-settings-form" class="weather-apis-settings__form" method="post" action="<?php p($_['saveUrl']); ?>" data-generate-url="<?php p($_['generateCredentialsUrl']); ?>" data-rotate-url="<?php p($_['rotateHmacUrl']); ?>" data-config-url="<?php p($_['configUrl']); ?>" data-test-connection-url="<?php p($_['testConnectionUrl']); ?>" data-diagnostics-url="<?php p($_['diagnosticsUrl']); ?>" data-preview-url="<?php p($_['previewUrl']); ?>" data-farm-schema-url="<?php p($_['farmSchemaUrl']); ?>" data-farm-list-url="<?php p($_['farmListUrl']); ?>" data-farm-create-url="<?php p($_['farmCreateUrl']); ?>" data-farm-get-url="<?php p($_['farmGetUrl']); ?>" data-farm-update-url="<?php p($_['farmUpdateUrl']); ?>" data-farm-patch-url="<?php p($_['farmPatchUrl']); ?>" data-farm-delete-url="<?php p($_['farmDeleteUrl']); ?>" data-farm-sync-url="<?php p($_['farmSyncUrl']); ?>" data-farm-ndvi-latest-url="<?php p($_['farmNdviLatestUrl']); ?>" data-farm-ndvi-timeseries-url="<?php p($_['farmNdviTimeseriesUrl']); ?>" data-farm-ndvi-raster-url="<?php p($_['farmNdviRasterUrl']); ?>" data-farm-ndvi-raster-queue-url="<?php p($_['farmNdviRasterQueueUrl']); ?>" data-farm-ndvi-refresh-url="<?php p($_['farmNdviRefreshUrl']); ?>" data-farm-weather-current-url="<?php p($_['farmWeatherCurrentUrl']); ?>" data-farm-weather-hourly-url="<?php p($_['farmWeatherHourlyUrl']); ?>" data-farm-weather-daily-url="<?php p($_['farmWeatherDailyUrl']); ?>" data-farm-state-url="<?php p($_['farmStateUrl']); ?>" data-farm-observations-url="<?php p($_['farmObservationsUrl']); ?>" data-farm-observation-url="<?php p($_['farmObservationUrl']); ?>" data-farm-activities-url="<?php p($_['farmActivitiesUrl']); ?>" data-farm-activity-url="<?php p($_['farmActivityUrl']); ?>" data-activity-schema-url="<?php p($_['activitySchemaUrl']); ?>" data-activity-list-url="<?php p($_['activityListUrl']); ?>" data-activity-create-url="<?php p($_['activityCreateUrl']); ?>" data-activity-get-url="<?php p($_['activityGetUrl']); ?>" data-activity-update-url="<?php p($_['activityUpdateUrl']); ?>" data-activity-patch-url="<?php p($_['activityPatchUrl']); ?>" data-activity-delete-url="<?php p($_['activityDeleteUrl']); ?>" data-radio-providers-url="<?php p($_['radioProvidersUrl']); ?>" data-radio-stations-url="<?php p($_['radioStationsUrl']); ?>" data-radio-station-url="<?php p($_['radioStationUrl']); ?>" data-radio-stream-url="<?php p($_['radioStreamUrl']); ?>">
		<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken'] ?? \OC::$server->getRequest()->getParam('requesttoken', '') ?? ''); ?>" />
		<input type="hidden" name="format" value="json" />
		<div class="form-group">
			<label for="weather-apis-base-url"><?php p($l->t('Base URL')); ?></label>
			<input id="weather-apis-base-url" type="url" name="baseUrl" value="<?php p($_['baseUrl']); ?>" required />
		</div>

		<div class="form-group">
			<label for="weather-apis-client-id"><?php p($l->t('Client ID')); ?></label>
			<input id="weather-apis-client-id" type="text" name="clientId" value="<?php p($_['clientId']); ?>" maxlength="128" required />
		</div>

		<div class="form-group secret-field">
			<label for="weather-apis-api-key"><?php p($l->t('API key')); ?></label>
			<input id="weather-apis-api-key" type="password" name="apiKey" placeholder="<?php p($_['apiKeySet'] ? $l->t('Already set') : $l->t('Enter API key')); ?>" autocomplete="new-password" />
			<p class="hint"><?php p($l->t('Leave blank to retain the current value.')); ?></p>
		</div>

		<div class="form-group secret-field">
			<label for="weather-apis-signing-secret"><?php p($l->t('Signing secret (base64)')); ?></label>
			<input id="weather-apis-signing-secret" type="password" name="hmacSecret" placeholder="<?php p($_['hmacSecretSet'] ? $l->t('Already set') : $l->t('Enter base64 secret')); ?>" autocomplete="new-password" />
			<p class="hint"><?php p($l->t('Base64-encoded secret. Leave blank to keep the stored value.')); ?></p>
		</div>

		<div class="form-group">
			<label><?php p($l->t('HMAC credentials')); ?></label>
			<div class="weather-apis-credentials__actions">
				<button id="weather-apis-generate" type="button" class="button"><?php p($l->t('Generate client + secret')); ?></button>
				<button id="weather-apis-rotate" type="button" class="button"><?php p($l->t('Rotate secret')); ?></button>
			</div>
			<p class="hint"><?php p($l->t('Shown once. Store securely.')); ?></p>
		</div>

		<div id="weather-apis-credentials-result" class="weather-apis-credentials" hidden>
			<div class="weather-apis-credentials__header">
				<strong><?php p($l->t('Generated credentials')); ?></strong>
				<button id="weather-apis-credentials-close" type="button" class="button"><?php p($l->t('Clear')); ?></button>
			</div>
			<p class="warning"><?php p($l->t('Shown once. Store securely.')); ?></p>
			<div class="form-group">
				<label for="weather-apis-generated-client-id"><?php p($l->t('Client ID')); ?></label>
				<div class="weather-apis-credentials__row">
					<input id="weather-apis-generated-client-id" type="text" readonly />
					<button id="weather-apis-copy-client-id" type="button" class="button"><?php p($l->t('Copy')); ?></button>
				</div>
			</div>
			<div class="form-group">
				<label for="weather-apis-generated-secret"><?php p($l->t('HMAC secret (base64)')); ?></label>
				<div class="weather-apis-credentials__row">
					<input id="weather-apis-generated-secret" type="text" readonly />
					<button id="weather-apis-copy-secret" type="button" class="button"><?php p($l->t('Copy')); ?></button>
				</div>
			</div>
			<div class="form-group">
				<label for="weather-apis-generated-export"><?php p($l->t('DRF export snippet')); ?></label>
				<div class="weather-apis-credentials__row">
					<textarea id="weather-apis-generated-export" class="weather-apis-credentials__snippet" rows="3" readonly></textarea>
					<button id="weather-apis-copy-export" type="button" class="button"><?php p($l->t('Copy')); ?></button>
				</div>
				<p class="hint"><?php p($l->t('Set these values in the DRF environment.')); ?></p>
			</div>
		</div>

		<div class="form-group weather-apis-connection">
			<label><?php p($l->t('Integration Status')); ?></label>
			<div class="weather-apis-connection__actions">
				<button id="weather-apis-test-connection" type="button" class="button"><?php p($l->t('Test connection')); ?></button>
				<div id="weather-apis-connection-status" class="weather-apis-connection__status" role="status" aria-live="polite"></div>
			</div>
			<p class="hint"><?php p($l->t('Performs a backend token request to verify HMAC connectivity.')); ?></p>
		</div>

		<div class="form-group weather-apis-diagnostics">
			<label><?php p($l->t('Diagnostics')); ?></label>
			<div class="weather-apis-diagnostics__actions">
				<button id="weather-apis-run-diagnostics" type="button" class="button"><?php p($l->t('Run diagnostics')); ?></button>
				<div id="weather-apis-diagnostics-summary" class="weather-apis-diagnostics__summary" role="status" aria-live="polite"></div>
			</div>
			<div id="weather-apis-diagnostics-results" class="weather-apis-diagnostics__results" hidden>
				<div class="weather-apis-diagnostics__row" id="weather-apis-diagnostics-token-row">
					<span class="weather-apis-diagnostics__label"><?php p($l->t('Token')); ?></span>
					<span id="weather-apis-diagnostics-token" class="weather-apis-diagnostics__value"></span>
				</div>
				<div class="weather-apis-diagnostics__row" id="weather-apis-diagnostics-status-row">
					<span class="weather-apis-diagnostics__label"><?php p($l->t('Status')); ?></span>
					<span id="weather-apis-diagnostics-status" class="weather-apis-diagnostics__value"></span>
				</div>
				<div class="weather-apis-diagnostics__row" id="weather-apis-diagnostics-png-row">
					<span class="weather-apis-diagnostics__label"><?php p($l->t('PNG preview')); ?></span>
					<span id="weather-apis-diagnostics-png" class="weather-apis-diagnostics__value"></span>
				</div>
				<div class="weather-apis-diagnostics__preview" id="weather-apis-diagnostics-preview-wrap" hidden>
					<img id="weather-apis-diagnostics-preview" alt="<?php p($l->t('DRF preview')); ?>" />
				</div>
			</div>
			<p class="hint"><?php p($l->t('Runs token minting, status, and PNG preview through the proxy.')); ?></p>
		</div>

		<div class="form-group weather-apis-farms" id="weather-apis-farms">
			<div class="weather-apis-farms__header">
				<div>
					<strong><?php p($l->t('Farms')); ?></strong>
					<p class="hint"><?php p($l->t('Manage farms, NDVI, and weather from the DRF backend via the admin proxy.')); ?></p>
				</div>
				<div class="weather-apis-farms__actions">
					<button id="weather-apis-farms-refresh" type="button" class="button"><?php p($l->t('Refresh farms')); ?></button>
					<button id="weather-apis-farms-create" type="button" class="button primary"><?php p($l->t('New farm')); ?></button>
				</div>
			</div>
			<div id="weather-apis-farms-warning" class="weather-apis-farms__note warning" hidden></div>
			<div id="weather-apis-farms-error" class="weather-apis-farms__note error" hidden></div>
			<div class="weather-apis-farms__table-wrap">
				<table class="weather-apis-farms__table">
					<thead>
						<tr id="weather-apis-farms-columns"></tr>
					</thead>
					<tbody id="weather-apis-farms-body"></tbody>
				</table>
			</div>
			<div class="weather-apis-farms__pagination" id="weather-apis-farms-pagination" hidden>
				<button id="weather-apis-farms-prev" type="button" class="button"><?php p($l->t('Previous')); ?></button>
				<div id="weather-apis-farms-page" class="weather-apis-farms__page"></div>
				<button id="weather-apis-farms-next" type="button" class="button"><?php p($l->t('Next')); ?></button>
			</div>
			<div class="weather-apis-farms__panels">
				<div class="weather-apis-farms__ndvi" id="weather-apis-farms-ndvi" hidden>
					<div class="weather-apis-farms__ndvi-header">
						<strong><?php p($l->t('NDVI')); ?></strong>
						<span id="weather-apis-farms-ndvi-title" class="weather-apis-farms__ndvi-title"></span>
					</div>
					<div class="weather-apis-farms__ndvi-actions">
						<button id="weather-apis-ndvi-latest" type="button" class="button"><?php p($l->t('Latest NDVI')); ?></button>
						<button id="weather-apis-ndvi-timeseries" type="button" class="button"><?php p($l->t('Timeseries')); ?></button>
						<button id="weather-apis-ndvi-queue" type="button" class="button"><?php p($l->t('Queue raster')); ?></button>
						<button id="weather-apis-ndvi-refresh" type="button" class="button"><?php p($l->t('Refresh')); ?></button>
						<button id="weather-apis-farm-state" type="button" class="button"><?php p($l->t('Farm State')); ?></button>
					</div>
					<div class="weather-apis-farms__ndvi-row">
						<label for="weather-apis-ndvi-start"><?php p($l->t('Start')); ?></label>
						<input id="weather-apis-ndvi-start" type="date" />
						<label for="weather-apis-ndvi-end"><?php p($l->t('End')); ?></label>
						<input id="weather-apis-ndvi-end" type="date" />
						<label for="weather-apis-ndvi-date"><?php p($l->t('Raster date')); ?></label>
						<input id="weather-apis-ndvi-date" type="date" />
						<button id="weather-apis-ndvi-raster" type="button" class="button"><?php p($l->t('Raster PNG')); ?></button>
					</div>
					<div id="weather-apis-ndvi-error" class="weather-apis-farms__note error weather-apis-farms__ndvi-error" hidden></div>
					<div id="weather-apis-ndvi-output" class="weather-apis-farms__ndvi-output"></div>
					<div id="weather-apis-ndvi-calendar" class="weather-apis-farms__ndvi-calendar" hidden>
						<div id="weather-apis-ndvi-weekdays" class="weather-apis-farms__ndvi-weekdays" aria-hidden="true"></div>
						<div id="weather-apis-ndvi-calendar-grid" class="weather-apis-farms__ndvi-calendar-grid" role="grid" aria-label="<?php p($l->t('NDVI date range calendar')); ?>"></div>
					</div>
					<div id="weather-apis-ndvi-table" class="weather-apis-farms__ndvi-table"></div>
					<div id="weather-apis-ndvi-raster-preview" class="weather-apis-farms__ndvi-preview" hidden>
						<img id="weather-apis-ndvi-raster-img" alt="<?php p($l->t('NDVI raster preview')); ?>" />
					</div>
					<div id="weather-apis-farm-state-output" class="weather-apis-farms__ndvi-output" hidden>
						<div id="weather-apis-farm-state-content" class="weather-apis-farms__farm-state-content"></div>
					</div>
				</div>
				<div class="weather-apis-farms__weather" id="weather-apis-farms-weather" hidden>
					<div class="weather-apis-farms__weather-header">
						<strong><?php p($l->t('Weather')); ?></strong>
						<span id="weather-apis-farms-weather-title" class="weather-apis-farms__weather-title"></span>
					</div>
					<div class="weather-apis-farms__weather-tabs">
						<button id="weather-apis-weather-current-tab" type="button" class="button"><?php p($l->t('Current')); ?></button>
						<button id="weather-apis-weather-hourly-tab" type="button" class="button"><?php p($l->t('Hourly')); ?></button>
						<button id="weather-apis-weather-daily-tab" type="button" class="button"><?php p($l->t('Daily')); ?></button>
					</div>
					<div id="weather-apis-weather-loading" class="weather-apis-farms__note" hidden><?php p($l->t('Loading weather...')); ?></div>
					<div id="weather-apis-weather-error" class="weather-apis-farms__note error" hidden></div>
					<div id="weather-apis-weather-current" class="weather-apis-farms__weather-panel" hidden>
						<div id="weather-apis-weather-current-grid" class="weather-apis-farms__weather-grid"></div>
					</div>
					<div id="weather-apis-weather-hourly" class="weather-apis-farms__weather-panel" hidden>
						<div id="weather-apis-weather-hourly-table" class="weather-apis-farms__weather-table"></div>
					</div>
					<div id="weather-apis-weather-daily" class="weather-apis-farms__weather-panel" hidden>
						<div id="weather-apis-weather-daily-table" class="weather-apis-farms__weather-table"></div>
					</div>
				</div>
				<div class="weather-apis-farms__observations" id="weather-apis-farms-observations" hidden>
					<div class="weather-apis-farms__weather-header">
						<strong><?php p($l->t('Observations')); ?></strong>
						<span id="weather-apis-farms-observations-title" class="weather-apis-farms__weather-title"></span>
					</div>
					<div class="weather-apis-farms__ndvi-row">
						<label for="weather-apis-observations-start"><?php p($l->t('Start')); ?></label>
						<input id="weather-apis-observations-start" type="datetime-local" />
						<label for="weather-apis-observations-end"><?php p($l->t('End')); ?></label>
						<input id="weather-apis-observations-end" type="datetime-local" />
						<label for="weather-apis-observations-type"><?php p($l->t('Event type')); ?></label>
						<input id="weather-apis-observations-type" type="text" />
						<label for="weather-apis-observations-limit"><?php p($l->t('Limit')); ?></label>
						<input id="weather-apis-observations-limit" type="number" min="1" max="500" />
						<button id="weather-apis-observations-refresh" type="button" class="button"><?php p($l->t('Refresh')); ?></button>
						<button id="weather-apis-observations-create" type="button" class="button primary"><?php p($l->t('New observation')); ?></button>
					</div>
					<div id="weather-apis-farms-observations-error" class="weather-apis-farms__note error" hidden></div>
					<div id="weather-apis-farms-observations-table" class="weather-apis-farms__weather-table"></div>
					<div class="weather-apis-farms__pagination" id="weather-apis-farms-observations-pagination" hidden>
						<button id="weather-apis-farms-observations-prev" type="button" class="button"><?php p($l->t('Previous')); ?></button>
						<div id="weather-apis-farms-observations-page" class="weather-apis-farms__page"></div>
						<button id="weather-apis-farms-observations-next" type="button" class="button"><?php p($l->t('Next')); ?></button>
					</div>
				</div>
				<div class="weather-apis-farms__activities" id="weather-apis-farms-activities" hidden>
					<div class="weather-apis-farms__weather-header">
						<strong><?php p($l->t('Activities')); ?></strong>
						<span id="weather-apis-farms-activities-title" class="weather-apis-farms__weather-title"></span>
					</div>
					<div class="weather-apis-farms__ndvi-row">
						<label for="weather-apis-activities-status"><?php p($l->t('Status')); ?></label>
						<input id="weather-apis-activities-status" type="text" />
						<label for="weather-apis-activities-type-filter"><?php p($l->t('Type')); ?></label>
						<input id="weather-apis-activities-type-filter" type="text" />
						<label for="weather-apis-activities-limit"><?php p($l->t('Limit')); ?></label>
						<input id="weather-apis-activities-limit" type="number" min="1" max="500" />
						<button id="weather-apis-activities-refresh" type="button" class="button"><?php p($l->t('Refresh')); ?></button>
						<button id="weather-apis-activities-create" type="button" class="button primary"><?php p($l->t('New activity')); ?></button>
					</div>
					<div id="weather-apis-farms-activities-error" class="weather-apis-farms__note error" hidden></div>
					<div id="weather-apis-farms-activities-table" class="weather-apis-farms__weather-table"></div>
					<div class="weather-apis-farms__pagination" id="weather-apis-farms-activities-pagination" hidden>
						<button id="weather-apis-farms-activities-prev" type="button" class="button"><?php p($l->t('Previous')); ?></button>
						<div id="weather-apis-farms-activities-page" class="weather-apis-farms__page"></div>
						<button id="weather-apis-farms-activities-next" type="button" class="button"><?php p($l->t('Next')); ?></button>
					</div>
				</div>
			</div>
			<div class="weather-apis-farms__modal" id="weather-apis-farms-activity-modal" hidden>
				<div class="weather-apis-farms__modal-card">
					<div class="weather-apis-farms__modal-header">
						<strong id="weather-apis-farms-activity-modal-title"><?php p($l->t('Activity')); ?></strong>
						<button id="weather-apis-farms-activity-modal-close" type="button" class="button"><?php p($l->t('Close')); ?></button>
					</div>
					<div class="weather-apis-farms__modal-body">
						<div id="weather-apis-farms-activity-fields" class="weather-apis-farms__modal-fields"></div>
					</div>
					<div class="weather-apis-farms__modal-actions">
						<button id="weather-apis-farms-activity-modal-save" type="button" class="button primary"><?php p($l->t('Save')); ?></button>
					</div>
				</div>
			</div>
			<div class="weather-apis-farms__modal" id="weather-apis-farms-modal" hidden>
				<div class="weather-apis-farms__modal-card">
					<div class="weather-apis-farms__modal-header">
						<strong id="weather-apis-farms-modal-title"></strong>
						<button id="weather-apis-farms-modal-close" type="button" class="button"><?php p($l->t('Close')); ?></button>
					</div>
					<div class="weather-apis-farms__modal-body">
						<div id="weather-apis-farms-modal-fields" class="weather-apis-farms__modal-fields"></div>
					</div>
					<div class="weather-apis-farms__modal-actions">
						<button id="weather-apis-farms-modal-save" type="button" class="button primary"><?php p($l->t('Save')); ?></button>
					</div>
				</div>
			</div>
			<div class="weather-apis-farms__modal" id="weather-apis-farms-sync-modal" hidden>
				<div class="weather-apis-farms__modal-card">
					<div class="weather-apis-farms__modal-header">
						<strong id="weather-apis-farms-sync-modal-title"><?php p($l->t('Sync Farm to DRF')); ?></strong>
						<button id="weather-apis-farms-sync-modal-close" type="button" class="button"><?php p($l->t('Close')); ?></button>
					</div>
					<div class="weather-apis-farms__modal-body">
						<p class="weather-apis-farms__sync-description"><?php p($l->t('Sync this farm to the DRF backend. This will create or update the farm using external identifiers.')); ?></p>
						<div id="weather-apis-farms-sync-fields" class="weather-apis-farms__modal-fields">
							<div class="weather-apis-farms__field">
								<label><?php p($l->t('External Farm ID')); ?></label>
								<input id="weather-apis-sync-external-farm-id" type="text" readonly />
							</div>
							<div class="weather-apis-farms__field">
								<label><?php p($l->t('External User ID')); ?></label>
								<input id="weather-apis-sync-external-user-id" type="text" readonly />
							</div>
							<div class="weather-apis-farms__field">
								<label><?php p($l->t('Farm Name')); ?></label>
								<input id="weather-apis-sync-name" type="text" readonly />
							</div>
						</div>
						<p class="hint"><?php p($l->t('This action will sync the farm to the DRF backend using the integration token.')); ?></p>
					</div>
					<div class="weather-apis-farms__modal-actions">
						<button id="weather-apis-farms-sync-modal-cancel" type="button" class="button"><?php p($l->t('Cancel')); ?></button>
						<button id="weather-apis-farms-sync-modal-confirm" type="button" class="button primary"><?php p($l->t('Sync Farm')); ?></button>
					</div>
				</div>
			</div>
			<div class="weather-apis-farms__modal" id="weather-apis-farms-observation-modal" hidden>
				<div class="weather-apis-farms__modal-card">
					<div class="weather-apis-farms__modal-header">
						<strong id="weather-apis-farms-observation-modal-title"><?php p($l->t('Observation')); ?></strong>
						<button id="weather-apis-farms-observation-modal-close" type="button" class="button"><?php p($l->t('Close')); ?></button>
					</div>
					<div class="weather-apis-farms__modal-body">
						<div id="weather-apis-farms-observation-fields" class="weather-apis-farms__modal-fields">
							<div class="weather-apis-farms__field">
								<label><?php p($l->t('Observed at')); ?></label>
								<input id="weather-apis-observation-observed-at" type="datetime-local" />
							</div>
							<div class="weather-apis-farms__field">
								<label><?php p($l->t('Event type')); ?></label>
								<select id="weather-apis-observation-event-type">
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
							<div class="weather-apis-farms__field">
								<label><?php p($l->t('Note')); ?></label>
								<input id="weather-apis-observation-note" type="text" />
							</div>
							<div class="weather-apis-farms__field-group-title"><?php p($l->t('Core metadata')); ?></div>
							<div class="weather-apis-farms__field">
								<label><?php p($l->t('Source')); ?></label>
								<select id="weather-apis-observation-source">
									<option value=""><?php p($l->t('Select source')); ?></option>
									<option value="manual"><?php p($l->t('Manual')); ?></option>
									<option value="sensor"><?php p($l->t('Sensor')); ?></option>
									<option value="integration"><?php p($l->t('Integration')); ?></option>
								</select>
							</div>
							<div class="weather-apis-farms__field">
								<label><?php p($l->t('Observer')); ?></label>
								<input id="weather-apis-observation-observer" type="text" />
							</div>
							<div class="weather-apis-farms__field">
								<label><?php p($l->t('Crop')); ?></label>
								<input id="weather-apis-observation-crop" type="text" />
							</div>
							<div class="weather-apis-farms__field">
								<label><?php p($l->t('Variety')); ?></label>
								<input id="weather-apis-observation-variety" type="text" />
							</div>
							<div class="weather-apis-farms__field">
								<label><?php p($l->t('Growth stage')); ?></label>
								<select id="weather-apis-observation-growth-stage">
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
							<div class="weather-apis-farms__field">
								<label><?php p($l->t('Area (ha)')); ?></label>
								<input id="weather-apis-observation-area-ha" type="number" min="0" step="0.01" />
							</div>
							<div class="weather-apis-farms__field">
								<label><?php p($l->t('Location note')); ?></label>
								<input id="weather-apis-observation-location-note" type="text" />
							</div>
							<div class="weather-apis-farms__field-group" data-event-types="planting">
								<div class="weather-apis-farms__field-group-title"><?php p($l->t('Planting details')); ?></div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('Seed rate (kg/ha)')); ?></label>
									<input id="weather-apis-observation-seed-rate" type="number" min="0" step="0.01" />
								</div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('Planting method')); ?></label>
									<select id="weather-apis-observation-planting-method">
										<option value=""><?php p($l->t('Select method')); ?></option>
										<option value="broadcast"><?php p($l->t('Broadcast')); ?></option>
										<option value="row"><?php p($l->t('Row')); ?></option>
										<option value="transplant"><?php p($l->t('Transplant')); ?></option>
									</select>
								</div>
							</div>
							<div class="weather-apis-farms__field-group" data-event-types="irrigation">
								<div class="weather-apis-farms__field-group-title"><?php p($l->t('Irrigation details')); ?></div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('Irrigation type')); ?></label>
									<select id="weather-apis-observation-irrigation-type">
										<option value=""><?php p($l->t('Select type')); ?></option>
										<option value="drip"><?php p($l->t('Drip')); ?></option>
										<option value="sprinkler"><?php p($l->t('Sprinkler')); ?></option>
										<option value="flood"><?php p($l->t('Flood')); ?></option>
										<option value="other"><?php p($l->t('Other')); ?></option>
									</select>
								</div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('Water applied (mm)')); ?></label>
									<input id="weather-apis-observation-water-mm" type="number" min="0" step="0.1" />
								</div>
							</div>
							<div class="weather-apis-farms__field-group" data-event-types="fertilization">
								<div class="weather-apis-farms__field-group-title"><?php p($l->t('Fertilization details')); ?></div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('Fertilizer type')); ?></label>
									<input id="weather-apis-observation-fertilizer-type" type="text" />
								</div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('N (kg/ha)')); ?></label>
									<input id="weather-apis-observation-nutrient-n" type="number" min="0" step="0.01" />
								</div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('P (kg/ha)')); ?></label>
									<input id="weather-apis-observation-nutrient-p" type="number" min="0" step="0.01" />
								</div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('K (kg/ha)')); ?></label>
									<input id="weather-apis-observation-nutrient-k" type="number" min="0" step="0.01" />
								</div>
							</div>
							<div class="weather-apis-farms__field-group" data-event-types="pest_control">
								<div class="weather-apis-farms__field-group-title"><?php p($l->t('Pest control details')); ?></div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('Pest')); ?></label>
									<input id="weather-apis-observation-pest" type="text" />
								</div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('Product')); ?></label>
									<input id="weather-apis-observation-product" type="text" />
								</div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('Dose (ml/ha)')); ?></label>
									<input id="weather-apis-observation-dose" type="number" min="0" step="0.01" />
								</div>
							</div>
							<div class="weather-apis-farms__field-group" data-event-types="harvest">
								<div class="weather-apis-farms__field-group-title"><?php p($l->t('Harvest details')); ?></div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('Yield (kg)')); ?></label>
									<input id="weather-apis-observation-yield" type="number" min="0" step="0.01" />
								</div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('Moisture (%%)')); ?></label>
									<input id="weather-apis-observation-moisture" type="number" min="0" max="100" step="0.1" />
								</div>
							</div>
							<div class="weather-apis-farms__field-group" data-event-types="scouting,soil_test">
								<div class="weather-apis-farms__field-group-title"><?php p($l->t('Scouting / soil details')); ?></div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('Pest pressure')); ?></label>
									<select id="weather-apis-observation-pest-pressure">
										<option value=""><?php p($l->t('Select pressure')); ?></option>
										<option value="none"><?php p($l->t('None')); ?></option>
										<option value="low"><?php p($l->t('Low')); ?></option>
										<option value="medium"><?php p($l->t('Medium')); ?></option>
										<option value="high"><?php p($l->t('High')); ?></option>
									</select>
								</div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('Soil pH')); ?></label>
									<input id="weather-apis-observation-soil-ph" type="number" min="0" max="14" step="0.01" />
								</div>
								<div class="weather-apis-farms__field">
									<label><?php p($l->t('Organic matter (%%)')); ?></label>
									<input id="weather-apis-observation-organic-matter" type="number" min="0" max="100" step="0.1" />
								</div>
							</div>
						</div>
					</div>
					<div class="weather-apis-farms__modal-actions">
						<button id="weather-apis-farms-observation-modal-save" type="button" class="button primary"><?php p($l->t('Save')); ?></button>
					</div>
				</div>
			</div>
		</div>

		<div class="form-group weather-apis-radio" id="weather-apis-radio">
			<div class="weather-apis-radio__header">
				<div>
					<strong><?php p($l->t('Radio')); ?></strong>
					<p class="hint"><?php p($l->t('Browse and play radio stations from the DRF backend.')); ?></p>
				</div>
				<div class="weather-apis-radio__actions">
					<button id="weather-apis-radio-refresh" type="button" class="button"><?php p($l->t('Refresh')); ?></button>
				</div>
			</div>
			<div class="weather-apis-radio__tabs">
				<button id="weather-apis-radio-stations-tab" type="button" class="button primary"><?php p($l->t('Stations')); ?></button>
				<button id="weather-apis-radio-providers-tab" type="button" class="button"><?php p($l->t('Providers')); ?></button>
			</div>
			<div id="weather-apis-radio-loading" class="weather-apis-radio__note" hidden><?php p($l->t('Loading radio...')); ?></div>
			<div id="weather-apis-radio-error" class="weather-apis-radio__note error" hidden></div>
			<div id="weather-apis-radio-stations" class="weather-apis-radio__panel">
				<div class="weather-apis-radio__filters">
					<label for="weather-apis-radio-search"><?php p($l->t('Search')); ?></label>
					<input id="weather-apis-radio-search" type="text" placeholder="<?php p($l->t('Filter by name, genre, or country')); ?>" />
					<label for="weather-apis-radio-genre-filter"><?php p($l->t('Genre')); ?></label>
					<select id="weather-apis-radio-genre-filter">
						<option value=""><?php p($l->t('All genres')); ?></option>
					</select>
					<label for="weather-apis-radio-country-filter"><?php p($l->t('Country')); ?></label>
					<select id="weather-apis-radio-country-filter">
						<option value=""><?php p($l->t('All countries')); ?></option>
					</select>
				</div>
				<div class="weather-apis-radio__table-wrap">
					<table class="weather-apis-radio__table">
						<thead>
							<tr id="weather-apis-radio-stations-columns"></tr>
						</thead>
						<tbody id="weather-apis-radio-stations-body"></tbody>
					</table>
				</div>
				<div id="weather-apis-radio-stations-empty" hidden>
					<p><?php p($l->t('No stations found.')); ?></p>
				</div>
			</div>
			<div id="weather-apis-radio-providers" class="weather-apis-radio__panel" hidden>
				<div class="weather-apis-radio__table-wrap">
					<table class="weather-apis-radio__table">
						<thead>
							<tr id="weather-apis-radio-providers-columns"></tr>
						</thead>
						<tbody id="weather-apis-radio-providers-body"></tbody>
					</table>
				</div>
				<div id="weather-apis-radio-providers-empty" hidden>
					<p><?php p($l->t('No providers found.')); ?></p>
				</div>
			</div>
			<div class="weather-apis-radio__player" id="weather-apis-radio-player" hidden>
				<div class="weather-apis-radio__player-bar">
					<img id="weather-apis-radio-bar-logo" src="" alt="" class="weather-apis-radio__bar-logo" hidden />
					<span id="weather-apis-radio-bar-title" class="weather-apis-radio__bar-title"></span>
					<span id="weather-apis-radio-bar-time" class="weather-apis-radio__bar-time">0:00</span>
					<button id="weather-apis-radio-bar-rewind" type="button" class="weather-apis-radio__bar-btn" aria-label="<?php p($l->t('Rewind 10 seconds')); ?>">
						<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M12.5 3C17.15 3 21.08 6.03 22.47 10.22L20.08 11C18.92 7.68 15.96 5.25 12.5 5.25C8.36 5.25 5 8.61 5 12.75C5 14.06 5.34 15.29 5.93 16.36L10.5 11.79V15.5H6.79L3.29 12L6.79 8.5H10.5V5.79L12.5 3Z"/><text x="9" y="16" font-size="7" fill="currentColor" font-weight="bold">10</text></svg>
					</button>
					<button id="weather-apis-radio-bar-forward" type="button" class="weather-apis-radio__bar-btn" aria-label="<?php p($l->t('Forward 10 seconds')); ?>">
						<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M11.5 3C6.85 3 2.92 6.03 1.53 10.22L3.92 11C5.08 7.68 8.04 5.25 11.5 5.25C15.64 5.25 19 8.61 19 12.75C19 14.06 18.66 15.29 18.07 16.36L13.5 11.79V15.5H17.21L20.71 12L17.21 8.5H13.5V5.79L11.5 3Z"/><text x="12" y="16" font-size="7" fill="currentColor" font-weight="bold">10</text></svg>
					</button>
					<button id="weather-apis-radio-bar-play" type="button" class="weather-apis-radio__bar-btn" aria-label="<?php p($l->t('Play')); ?>">
						<svg id="weather-apis-radio-bar-icon-play" viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M8 5v14l11-7Z"/></svg>
						<svg id="weather-apis-radio-bar-icon-pause" viewBox="0 0 24 24" width="20" height="20" hidden><path fill="currentColor" d="M6 19h4V5H6v14Zm8-14v14h4V5h-4Z"/></svg>
					</button>
					<button id="weather-apis-radio-bar-expand" type="button" class="weather-apis-radio__bar-btn" aria-label="<?php p($l->t('Expand')); ?>">
						<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M7 14l5-5 5 5H7Z"/></svg>
					</button>
					<button id="weather-apis-radio-bar-close" type="button" class="weather-apis-radio__bar-btn" aria-label="<?php p($l->t('Close')); ?>">
						<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
					</button>
				</div>
				<div class="weather-apis-radio__progress-bar" id="weather-apis-radio-progress-container">
					<div class="weather-apis-radio__progress-track" id="weather-apis-radio-progress-track">
						<div class="weather-apis-radio__progress-fill" id="weather-apis-radio-progress-fill"></div>
					</div>
				</div>
				<div class="weather-apis-radio__modal" id="weather-apis-radio-player-modal" hidden>
					<div class="weather-apis-radio__modal-card">
						<button id="weather-apis-radio-player-close" type="button" class="weather-apis-radio__modal-close" aria-label="<?php p($l->t('Close')); ?>">
							<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
						</button>
						<button id="weather-apis-radio-player-minimize" type="button" class="weather-apis-radio__modal-minimize" aria-label="<?php p($l->t('Minimize')); ?>">
							<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M7 10l5 5 5-5H7Z"/></svg>
						</button>
						<div class="weather-apis-radio__modal-body">
							<div class="weather-apis-radio__player-art">
								<img id="weather-apis-radio-player-logo" src="" alt="" hidden />
								<div id="weather-apis-radio-player-icon" class="weather-apis-radio__player-icon">
									<svg viewBox="0 0 24 24" width="48" height="48"><path fill="currentColor" d="M12 3v10.55A4 4 0 1 0 14 17V7h4V3h-6Z"/></svg>
								</div>
							</div>
							<div class="weather-apis-radio__player-meta">
								<strong id="weather-apis-radio-player-title"><?php p($l->t('Now Playing')); ?></strong>
								<span id="weather-apis-radio-player-subtitle" class="weather-apis-radio__player-subtitle"></span>
								<span id="weather-apis-radio-player-time" class="weather-apis-radio__player-time">0:00</span>
							</div>
							<div class="weather-apis-radio__player-controls">
								<button id="weather-apis-radio-player-rewind" type="button" class="weather-apis-radio__skip-btn" aria-label="<?php p($l->t('Rewind 10 seconds')); ?>">
									<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M12.5 3C17.15 3 21.08 6.03 22.47 10.22L20.08 11C18.92 7.68 15.96 5.25 12.5 5.25C8.36 5.25 5 8.61 5 12.75C5 14.06 5.34 15.29 5.93 16.36L10.5 11.79V15.5H6.79L3.29 12L6.79 8.5H10.5V5.79L12.5 3Z"/><text x="9" y="16" font-size="7" fill="currentColor" font-weight="bold">10</text></svg>
								</button>
								<button id="weather-apis-radio-player-play" type="button" class="weather-apis-radio__play-btn" aria-label="<?php p($l->t('Play')); ?>">
									<svg id="weather-apis-radio-icon-play" viewBox="0 0 24 24" width="32" height="32"><path fill="currentColor" d="M8 5v14l11-7Z"/></svg>
									<svg id="weather-apis-radio-icon-pause" viewBox="0 0 24 24" width="32" height="32" hidden><path fill="currentColor" d="M6 19h4V5H6v14Zm8-14v14h4V5h-4Z"/></svg>
								</button>
								<button id="weather-apis-radio-player-forward" type="button" class="weather-apis-radio__skip-btn" aria-label="<?php p($l->t('Forward 10 seconds')); ?>">
									<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M11.5 3C6.85 3 2.92 6.03 1.53 10.22L3.92 11C5.08 7.68 8.04 5.25 11.5 5.25C15.64 5.25 19 8.61 19 12.75C19 14.06 18.66 15.29 18.07 16.36L13.5 11.79V15.5H17.21L20.71 12L17.21 8.5H13.5V5.79L11.5 3Z"/><text x="12" y="16" font-size="7" fill="currentColor" font-weight="bold">10</text></svg>
								</button>
								<div class="weather-apis-radio__volume">
									<svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M3 9v6h4l5 5V4L7 9H3Z"/></svg>
									<input id="weather-apis-radio-volume" type="range" min="0" max="100" value="80" class="weather-apis-radio__volume-slider" aria-label="<?php p($l->t('Volume')); ?>" />
								</div>
							</div>
							<div class="weather-apis-radio__player-progress" id="weather-apis-radio-modal-progress">
								<div class="weather-apis-radio__progress-track" id="weather-apis-radio-modal-progress-track">
									<div class="weather-apis-radio__progress-fill" id="weather-apis-radio-modal-progress-fill"></div>
								</div>
							</div>
							<audio id="weather-apis-radio-audio" hidden></audio>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="form-group">
			<label for="weather-apis-timeout"><?php p($l->t('Timeout seconds')); ?></label>
			<input id="weather-apis-timeout" type="number" name="timeoutSeconds" min="1" max="30" value="<?php p($_['timeoutSeconds']); ?>" required />
		</div>

		<div class="form-group">
			<label for="weather-apis-dev-allow-http">
				<input id="weather-apis-dev-allow-http" type="checkbox" name="devAllowHttp" value="1" <?php p($_['devAllowHttp'] ? 'checked' : ''); ?> />
				<?php p($l->t('Dev: allow insecure local HTTP')); ?>
			</label>
		</div>

		<div class="form-group">
			<label for="weather-apis-allowlist"><?php p($l->t('Dev: allowlist hosts')); ?></label>
			<textarea id="weather-apis-allowlist" name="allowlistHosts" rows="3"><?php p($_['allowlistHosts']); ?></textarea>
		</div>

		<input type="submit" class="button primary" value="<?php p($l->t('Save')); ?>" />
		<div id="weather-apis-settings-status" class="status weather-apis-settings__status"></div>
	</form>
</div>
