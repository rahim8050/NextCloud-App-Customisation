<?php
style('weather_apis', 'admin-settings');
?>
<div id="weather-apis-settings-root" class="section weather-apis-settings">
	<h1><?php p($l->t('Weather APIs')); ?></h1>

	<form id="weather-apis-settings-form" class="weather-apis-settings__form" method="post" action="<?php p($_['saveUrl']); ?>" data-generate-url="<?php p($_['generateCredentialsUrl']); ?>" data-rotate-url="<?php p($_['rotateHmacUrl']); ?>" data-config-url="<?php p($_['configUrl']); ?>" data-test-connection-url="<?php p($_['testConnectionUrl']); ?>" data-diagnostics-url="<?php p($_['diagnosticsUrl']); ?>" data-preview-url="<?php p($_['previewUrl']); ?>" data-farm-schema-url="<?php p($_['farmSchemaUrl']); ?>" data-farm-list-url="<?php p($_['farmListUrl']); ?>" data-farm-create-url="<?php p($_['farmCreateUrl']); ?>" data-farm-get-url="<?php p($_['farmGetUrl']); ?>" data-farm-update-url="<?php p($_['farmUpdateUrl']); ?>" data-farm-patch-url="<?php p($_['farmPatchUrl']); ?>" data-farm-delete-url="<?php p($_['farmDeleteUrl']); ?>" data-farm-ndvi-latest-url="<?php p($_['farmNdviLatestUrl']); ?>" data-farm-ndvi-timeseries-url="<?php p($_['farmNdviTimeseriesUrl']); ?>" data-farm-ndvi-raster-url="<?php p($_['farmNdviRasterUrl']); ?>" data-farm-ndvi-raster-queue-url="<?php p($_['farmNdviRasterQueueUrl']); ?>" data-farm-ndvi-refresh-url="<?php p($_['farmNdviRefreshUrl']); ?>">
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
					<p class="hint"><?php p($l->t('Manage farms and NDVI from the DRF backend via the admin proxy.')); ?></p>
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
				<div id="weather-apis-ndvi-table" class="weather-apis-farms__ndvi-table"></div>
				<div id="weather-apis-ndvi-raster-preview" class="weather-apis-farms__ndvi-preview" hidden>
					<img id="weather-apis-ndvi-raster-img" alt="<?php p($l->t('NDVI raster preview')); ?>" />
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
