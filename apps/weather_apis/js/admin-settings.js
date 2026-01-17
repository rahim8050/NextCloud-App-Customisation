(() => {
	'use strict'

	if (window.__weatherApisAdminSettingsLoaded) {
		return
	}
	window.__weatherApisAdminSettingsLoaded = true
	console.info('[weather_apis] admin-settings loaded')

	const init = () => {
		const form = document.getElementById('weather-apis-settings-form')
		const status = document.getElementById('weather-apis-settings-status')
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
		const credentialsPanel = document.getElementById('weather-apis-credentials-result')
		const generatedClientIdInput = document.getElementById('weather-apis-generated-client-id')
		const generatedSecretInput = document.getElementById('weather-apis-generated-secret')
		const exportSnippetInput = document.getElementById('weather-apis-generated-export')
		const copyClientIdButton = document.getElementById('weather-apis-copy-client-id')
		const copySecretButton = document.getElementById('weather-apis-copy-secret')
		const copyExportButton = document.getElementById('weather-apis-copy-export')
		const closeCredentialsButton = document.getElementById('weather-apis-credentials-close')
		const generateButton = document.getElementById('weather-apis-generate')
		const rotateButton = document.getElementById('weather-apis-rotate')
		const testConnectionButton = document.getElementById('weather-apis-test-connection')
		const connectionStatus = document.getElementById('weather-apis-connection-status')
		const diagnosticsButton = document.getElementById('weather-apis-run-diagnostics')
		const diagnosticsSummary = document.getElementById('weather-apis-diagnostics-summary')
		const diagnosticsResults = document.getElementById('weather-apis-diagnostics-results')
		const diagnosticsTokenRow = document.getElementById('weather-apis-diagnostics-token-row')
		const diagnosticsStatusRow = document.getElementById('weather-apis-diagnostics-status-row')
		const diagnosticsPngRow = document.getElementById('weather-apis-diagnostics-png-row')
		const diagnosticsTokenValue = document.getElementById('weather-apis-diagnostics-token')
		const diagnosticsStatusValue = document.getElementById('weather-apis-diagnostics-status')
		const diagnosticsPngValue = document.getElementById('weather-apis-diagnostics-png')
		const diagnosticsPreviewWrap = document.getElementById('weather-apis-diagnostics-preview-wrap')
		const diagnosticsPreview = document.getElementById('weather-apis-diagnostics-preview')
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

		const pickMessage = (data, fallback) => toText(
			data?.message
			?? data?.error?.message
			?? data?.errors?.detail
			?? fallback,
			fallback,
		)

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
				const ok = document.execCommand('copy')
				inputEl.blur()
				return ok
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

		const performAdminRequest = async (url) => {
			const token = resolveRequestToken()
			console.info('[weather_apis] POST', url)
			const response = await fetch(url, {
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
			const token = resolveRequestToken()
			console.info('[weather_apis] GET', url)
			const response = await fetch(url, {
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

			const pngOk = pngResult?.ok === true
			const pngMessage = pngOk
				? joinParts(['OK', formatHttp(pngResult?.http)])
				: joinParts([
					'FAILED',
					formatHttp(pngResult?.http),
					pngResult?.message,
					pngResult?.code ? `code=${pngResult.code}` : null,
				])
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
				if (pngOk && previewUrl) {
					const separator = previewUrl.includes('?') ? '&' : '?'
					diagnosticsPreview.src = `${previewUrl}${separator}ts=${Date.now()}`
					diagnosticsPreviewWrap.hidden = false
				} else {
					diagnosticsPreviewWrap.hidden = true
					diagnosticsPreview.removeAttribute('src')
				}
			}
		}

		if (generateButton) {
			generateButton.addEventListener('click', () => {
				console.info('[weather_apis] generate clicked')
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
				console.info('[weather_apis] rotate clicked')
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
				console.info('[weather_apis] test connection clicked')
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
				console.info('[weather_apis] diagnostics clicked')
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
