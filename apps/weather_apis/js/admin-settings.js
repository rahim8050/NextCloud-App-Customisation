(() => {
	'use strict'

	const init = () => {
		const form = document.getElementById('weather-apis-settings-form')
		const status = document.getElementById('weather-apis-settings-status')
		if (!form || !status || typeof fetch === 'undefined') {
			return
		}

		const action = form.getAttribute('action') || ''
		const requestTokenInput = form.querySelector('input[name="requesttoken"]')
		const requestToken = String(requestTokenInput?.value ?? window.OC?.requestToken ?? '').trim()
		if (!action || !requestToken) {
			return
		}

		const baseUrlInput = form.querySelector('[name="baseUrl"]')
		const clientIdInput = form.querySelector('[name="clientId"]')
		const apiKeyInput = form.querySelector('[name="apiKey"]')
		const signingSecretInput = form.querySelector('[name="signingSecret"]')
		const timeoutInput = form.querySelector('[name="timeoutSeconds"]')
		const devAllowHttpInput = form.querySelector('[name="devAllowHttp"]')
		const allowlistInput = form.querySelector('[name="devAllowlistHosts"]')

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

		const pickMessage = (data, fallback) => toText(
			data?.message
			?? data?.error?.message
			?? data?.errors?.detail
			?? fallback,
			fallback,
		)

		/**
		 * Toast helper (Nextcloud-native).
		 * Keeps existing behavior intact: if toast API is missing, UI still works via inline status.
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

		const requirePasswordConfirmationAsync = () => new Promise((resolve) => {
			const confirmation = window.OC?.PasswordConfirmation
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
			if (response.status !== 403) return false
			const msg = pickMessage(data, '')
			return /password confirmation/i.test(msg)
		}

		form.addEventListener('submit', (event) => {
			event.preventDefault()
			status.textContent = ''
			status.classList.remove('success', 'error')

			const payload = {
				baseUrl: (baseUrlInput?.value ?? '').trim(),
				clientId: (clientIdInput?.value ?? '').trim(),
				apiKey: (apiKeyInput?.value ?? '').trim(),
				signingSecret: (signingSecretInput?.value ?? '').trim(),
				timeoutSeconds: Number.parseInt(timeoutInput?.value ?? '10', 10) || 10,
				devAllowHttp: Boolean(devAllowHttpInput?.checked),
				devAllowlistHosts: (allowlistInput?.value ?? '').trim(),
			}

			const performSave = async (allowPasswordRetry = true) => {
				const response = await fetch(action, {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
						requesttoken: requestToken,
						'X-Requested-With': 'XMLHttpRequest',
					},
					body: JSON.stringify(payload),
				})

				const data = await response.json().catch(() => ({}))

				// Requirement: if password confirmation is required, prompt then retry once
				if (allowPasswordRetry && isPasswordConfirmationRequired(response, data)) {
					await requirePasswordConfirmationAsync()
					return performSave(false)
				}

				if (!response.ok) {
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
				if (signingSecretInput) signingSecretInput.value = ''
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
