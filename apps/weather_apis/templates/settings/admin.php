<?php
style('weather_apis', 'admin-settings');
script('weather_apis', 'admin-settings');
?>
<div class="section weather-apis-settings">
	<h1><?php p($l->t('Weather APIs')); ?></h1>

	<form id="weather-apis-settings-form" class="weather-apis-settings__form" method="post" action="<?php p($_['saveUrl']); ?>">
		<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']); ?>" />
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
			<label for="weather-apis-signing-secret"><?php p($l->t('Signing secret')); ?></label>
			<input id="weather-apis-signing-secret" type="password" name="signingSecret" placeholder="<?php p($_['secretSet'] ? $l->t('Already set') : $l->t('Enter signing secret')); ?>" autocomplete="new-password" />
			<p class="hint"><?php p($l->t('Leave blank to keep the stored secret.')); ?></p>
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
			<textarea id="weather-apis-allowlist" name="devAllowlistHosts" rows="3"><?php p($_['devAllowlistHosts']); ?></textarea>
		</div>

		<input type="submit" class="button primary" value="<?php p($l->t('Save')); ?>" />
		<div id="weather-apis-settings-status" class="status weather-apis-settings__status"></div>
	</form>
</div>
