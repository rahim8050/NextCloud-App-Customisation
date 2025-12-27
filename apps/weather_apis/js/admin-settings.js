(() => {
	'use strict'

	const form = document.getElementById('weather-apis-settings-form')
	const status = document.getElementById('weather-apis-settings-status')
	if (!form || !status || typeof fetch === 'undefined') {
		return
	}

	const baseUrlInput = form.querySelector('[name="baseUrl"]')
	const clientIdInput = form.querySelector('[name="clientId"]')
	const apiKeyInput = form.querySelector('[name="apiKey"]')
	const signingSecretInput = form.querySelector('[name="signingSecret"]')
	const timeoutInput = form.querySelector('[name="timeoutSeconds"]')
	const devAllowHttpInput = form.querySelector('[name="devAllowHttp"]')
	const allowlistInput = form.querySelector('[name="devAllowlistHosts"]')

	const showNotification = (message) => {
		if (window.OC && window.OC.Notification && typeof window.OC.Notification.show === 'function') {
			window.OC.Notification.show({
				message,
				type: 'error',
			})
		}
	}

	form.addEventListener('submit', async (event) => {
		event.preventDefault()
		status.textContent = ''
		status.classList.remove('success', 'error')

		const payload = {
			baseUrl: baseUrlInput.value.trim(),
			clientId: clientIdInput.value.trim(),
			apiKey: apiKeyInput.value.trim(),
			signingSecret: signingSecretInput.value.trim(),
			timeoutSeconds: timeoutInput.value,
			devAllowHttp: devAllowHttpInput.checked,
			devAllowlistHosts: allowlistInput.value.trim(),
		}

		try {
			const response = await fetch(OC.generateUrl('/apps/weather_apis/settings/admin'), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					requesttoken: OC.requestToken,
				},
				body: JSON.stringify(payload),
			})

			const data = await response.json().catch(() => ({}))
			if (!response.ok) {
				const message = data?.error?.message ?? 'Unable to save settings.'
				status.textContent = message
				status.classList.add('error')
				showNotification(message)
				return
			}

			status.textContent = 'Settings saved.'
			status.classList.add('success')
			apiKeyInput.value = ''
			signingSecretInput.value = ''
		} catch (error) {
			const message = error instanceof Error ? error.message : 'Unable to save settings.'
			status.textContent = message
			status.classList.add('error')
			showNotification(message)
		}
	})
})()
