<?php
style('weather_apis', 'admin-settings');
?>
<div id="weather-apis-settings-root" class="section weather-apis-settings">
	<h1><?php p($l->t('Weather APIs')); ?></h1>

	<form id="weather-apis-settings-form" class="weather-apis-settings__form" method="post" action="<?php p($_['saveUrl']); ?>" data-generate-url="<?php p($_['generateCredentialsUrl']); ?>" data-rotate-url="<?php p($_['rotateHmacUrl']); ?>" data-config-url="<?php p($_['configUrl']); ?>" data-test-connection-url="<?php p($_['testConnectionUrl']); ?>">
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
