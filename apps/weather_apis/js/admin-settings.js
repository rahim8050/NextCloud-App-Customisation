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
		const farmSchemaUrl = form.dataset.farmSchemaUrl || ''
		const farmListUrl = form.dataset.farmListUrl || ''
		const farmCreateUrl = form.dataset.farmCreateUrl || ''
		const farmGetUrl = form.dataset.farmGetUrl || ''
		const farmPatchUrl = form.dataset.farmPatchUrl || ''
		const farmDeleteUrl = form.dataset.farmDeleteUrl || ''
		const farmNdviLatestUrl = form.dataset.farmNdviLatestUrl || ''
		const farmNdviTimeseriesUrl = form.dataset.farmNdviTimeseriesUrl || ''
		const farmNdviRasterUrl = form.dataset.farmNdviRasterUrl || ''
		const farmNdviRasterQueueUrl = form.dataset.farmNdviRasterQueueUrl || ''
		const farmNdviRefreshUrl = form.dataset.farmNdviRefreshUrl || ''
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
		const farmsRoot = document.getElementById('weather-apis-farms')
		const farmsWarning = document.getElementById('weather-apis-farms-warning')
		const farmsError = document.getElementById('weather-apis-farms-error')
		const farmsColumns = document.getElementById('weather-apis-farms-columns')
		const farmsBody = document.getElementById('weather-apis-farms-body')
		const farmsRefresh = document.getElementById('weather-apis-farms-refresh')
		const farmsCreate = document.getElementById('weather-apis-farms-create')
		const farmsPagination = document.getElementById('weather-apis-farms-pagination')
		const farmsPrev = document.getElementById('weather-apis-farms-prev')
		const farmsNext = document.getElementById('weather-apis-farms-next')
		const farmsPage = document.getElementById('weather-apis-farms-page')
		const farmsNdvi = document.getElementById('weather-apis-farms-ndvi')
		const farmsNdviTitle = document.getElementById('weather-apis-farms-ndvi-title')
		const ndviLatestButton = document.getElementById('weather-apis-ndvi-latest')
		const ndviTimeseriesButton = document.getElementById('weather-apis-ndvi-timeseries')
		const ndviRasterButton = document.getElementById('weather-apis-ndvi-raster')
		const ndviQueueButton = document.getElementById('weather-apis-ndvi-queue')
		const ndviRefreshButton = document.getElementById('weather-apis-ndvi-refresh')
		const ndviStartInput = document.getElementById('weather-apis-ndvi-start')
		const ndviEndInput = document.getElementById('weather-apis-ndvi-end')
		const ndviDateInput = document.getElementById('weather-apis-ndvi-date')
		const ndviOutput = document.getElementById('weather-apis-ndvi-output')
		const ndviTable = document.getElementById('weather-apis-ndvi-table')
		const ndviRasterPreview = document.getElementById('weather-apis-ndvi-raster-preview')
		const ndviRasterImg = document.getElementById('weather-apis-ndvi-raster-img')
		const farmsModal = document.getElementById('weather-apis-farms-modal')
		const farmsModalTitle = document.getElementById('weather-apis-farms-modal-title')
		const farmsModalFields = document.getElementById('weather-apis-farms-modal-fields')
		const farmsModalSave = document.getElementById('weather-apis-farms-modal-save')
		const farmsModalClose = document.getElementById('weather-apis-farms-modal-close')
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

			const queryString = buildQueryString(options.query)
			const finalUrl = queryString
				? `${url}${url.includes('?') ? '&' : '?'}${queryString}`
				: url

			const axiosClient = window.OC?.axios || window.axios
			if (axiosClient) {
				try {
					const response = await axiosClient({
						method,
						url: finalUrl,
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

		const setupFarms = () => {
			if (!farmsRoot) {
				return
			}

			const ncGenerateUrl = typeof window.OC?.generateUrl === 'function'
				? window.OC.generateUrl
				: (path) => path

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

			let farmSchema = null
			let farmFields = {}
			let farmOperations = {}
			let activeColumns = []
			let nextParams = null
			let prevParams = null
			let selectedFarm = null
			let modalInitial = {}

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

			const setFarmsCreateEnabled = (enabled) => {
				if (farmsCreate) {
					farmsCreate.disabled = !enabled
				}
			}

			const unwrapResponseData = (data) => {
				if (data && typeof data === 'object' && data.data !== undefined) {
					return data.data
				}
				return data ?? {}
			}

			const resolveOperation = (key) => (farmOperations && farmOperations[key]) || null

			const resolveFarmId = (farm) => {
				if (!farm || typeof farm !== 'object') {
					return null
				}
				if (farm.id !== undefined && farm.id !== null && farm.id !== '') {
					return farm.id
				}
				if (farm.slug !== undefined && farm.slug !== null && farm.slug !== '') {
					return farm.slug
				}
				return null
			}

			const renderColumns = () => {
				if (!farmsColumns) {
					return
				}
				activeColumns = fieldOrder.filter((name) => Object.prototype.hasOwnProperty.call(farmFields, name))
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
					const ndviButton = document.createElement('button')
					ndviButton.type = 'button'
					ndviButton.className = 'button'
					ndviButton.textContent = 'NDVI'

					const farmId = resolveFarmId(farm)
					if (farmId === null) {
						editButton.disabled = true
						deleteButton.disabled = true
						ndviButton.disabled = true
					} else {
						editButton.addEventListener('click', () => openFarmModal('edit', farmId))
						deleteButton.addEventListener('click', () => deleteFarm(farmId))
						ndviButton.addEventListener('click', () => openNdviPanel(farmId, farm))
					}

					actions.appendChild(editButton)
					actions.appendChild(deleteButton)
					actions.appendChild(ndviButton)
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
				farmsPrev.disabled = !prevParams
				farmsNext.disabled = !nextParams
				farmsPage.textContent = count !== null
					? `${itemCount} of ${count}`
					: 'Pagination available'
			}

			const loadSchema = async () => {
				clearFarmsNotes()
				setFarmsCreateEnabled(false)
				if (!farmSchemaUrl) {
					showFarmsError('Farm schema endpoint is not available.')
					return false
				}

				const result = await performJsonRequest('GET', farmSchemaUrl)
				if (!result.parsed) {
					showFarmsError('Unable to parse farm schema response.')
					return false
				}
				const ok = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
				if (!ok) {
					const message = pickMessage(result.data, 'Unable to load farm schema.')
					showFarmsError(message)
					return false
				}

				const payload = unwrapResponseData(result.data)
				const schemaContainer = payload?.schema ?? {}
				const effectiveSchema = schemaContainer?.schema ?? schemaContainer
				farmSchema = effectiveSchema ?? {}
				farmFields = farmSchema?.fields ?? {}
				farmOperations = farmSchema?.operations ?? {}

				if (!farmFields || Object.keys(farmFields).length === 0) {
					showFarmsError('Farm schema did not return any fields.')
					setFarmsCreateEnabled(false)
					return false
				}

				if (payload?.warning) {
					showFarmsWarning(payload.warning)
				}

				renderColumns()
				setFarmsCreateEnabled(true)
				return true
			}

			const refreshFarms = async (params = null) => {
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
				const ok = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
				if (!ok) {
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

			const openFarmModal = async (mode, farmId) => {
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
					const ok = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
					if (!ok) {
						const message = pickMessage(result.data, 'Unable to load farm.')
						showFarmsError(message)
						return
					}
					existing = unwrapResponseData(result.data) ?? {}
					modalInitial = existing
				}

				farmsModalTitle.textContent = mode === 'edit' ? 'Edit farm' : 'Create farm'
				farmsModalFields.innerHTML = ''

				const entries = Object.entries(farmFields).filter(([, def]) => !def?.readOnly)
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
					const entries = Object.entries(farmFields).filter(([, def]) => !def?.readOnly)
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
					const ok = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
					if (!ok) {
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

			const deleteFarm = async (farmId) => {
				clearFarmsNotes()
				const confirmed = await confirmDeleteAsync()
				if (!confirmed) {
					return
				}
				const url = farmDeleteUrl.replace('__ID__', encodeURIComponent(farmId))
				const result = await performJsonRequest('DELETE', url)
				if (!result.parsed) {
					showFarmsError('Unable to parse delete response.')
					return
				}
				const ok = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
				if (!ok) {
					const message = pickMessage(result.data, 'Unable to delete farm.')
					showFarmsError(message)
					return
				}
				await refreshFarms()
			}

			const clearNdviOutput = () => {
				if (ndviOutput) ndviOutput.textContent = ''
				if (ndviTable) ndviTable.textContent = ''
				if (ndviRasterPreview) ndviRasterPreview.hidden = true
				if (ndviRasterImg) ndviRasterImg.removeAttribute('src')
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

			const buildNdviQuery = (operationKey) => {
				const operation = resolveOperation(operationKey)
				const params = operation?.queryParams ?? []
				const query = {}
				const startName = resolveParamName(params, 'start')
				const endName = resolveParamName(params, 'end')
				const dateName = resolveParamName(params, 'date')
				if (startName && ndviStartInput?.value) {
					query[startName] = ndviStartInput.value
				}
				if (endName && ndviEndInput?.value) {
					query[endName] = ndviEndInput.value
				}
				if (dateName && ndviDateInput?.value) {
					query[dateName] = ndviDateInput.value
				}
				return query
			}

			const buildNdviBody = (operationKey) => {
				const operation = resolveOperation(operationKey)
				const fields = operation?.bodyFields ?? {}
				const body = {}
				const startName = resolveBodyFieldName(fields, 'start')
				const endName = resolveBodyFieldName(fields, 'end')
				const dateName = resolveBodyFieldName(fields, 'date')
				if (startName && ndviStartInput?.value) {
					body[startName] = ndviStartInput.value
				}
				if (endName && ndviEndInput?.value) {
					body[endName] = ndviEndInput.value
				}
				if (dateName && ndviDateInput?.value) {
					body[dateName] = ndviDateInput.value
				}
				return body
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
				table.className = 'weather-apis-farms__table'
				const thead = document.createElement('thead')
				const headerRow = document.createElement('tr')
				columns.forEach((name) => {
					const th = document.createElement('th')
					th.textContent = name
					headerRow.appendChild(th)
				})
				thead.appendChild(headerRow)
				table.appendChild(thead)
				const tbody = document.createElement('tbody')
				items.forEach((item) => {
					const row = document.createElement('tr')
					columns.forEach((name) => {
						const cell = document.createElement('td')
						cell.textContent = item?.[name] !== undefined ? String(item[name]) : ''
						row.appendChild(cell)
					})
					tbody.appendChild(row)
				})
				table.appendChild(tbody)
				ndviTable.innerHTML = ''
				ndviTable.appendChild(table)
			}

			const openNdviPanel = (farmId, farm) => {
				selectedFarm = { id: farmId, data: farm }
				if (farmsNdvi) {
					farmsNdvi.hidden = false
				}
				if (farmsNdviTitle) {
					const label = farm?.name ? `${farm.name} (#${farmId})` : `Farm #${farmId}`
					farmsNdviTitle.textContent = label
				}
				clearNdviOutput()
			}

			const runNdviRequest = async (operationKey, urlTemplate, options = {}) => {
				clearFarmsNotes()
				if (!selectedFarm) {
					showFarmsError('Select a farm first.')
					return
				}
				const url = urlTemplate.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
				const result = await performJsonRequest(options.method || 'GET', url, options)
				if (!result.parsed) {
					showFarmsError('Unable to parse NDVI response.')
					return null
				}
				const ok = result.response.ok && (result.data?.status === 'ok' || result.data?.ok === true)
				if (!ok) {
					const message = pickMessage(result.data, `Unable to load ${operationKey}.`)
					showFarmsError(message)
					return null
				}
				return unwrapResponseData(result.data)
			}

			if (farmsRefresh) {
				farmsRefresh.addEventListener('click', () => refreshFarms())
			}
			if (farmsCreate) {
				farmsCreate.addEventListener('click', () => openFarmModal('create'))
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
			if (farmsModalClose) {
				farmsModalClose.addEventListener('click', closeFarmModal)
			}
			if (ndviLatestButton) {
				ndviLatestButton.addEventListener('click', async () => {
					const data = await runNdviRequest('latest NDVI', farmNdviLatestUrl, {
						method: 'GET',
						query: buildNdviQuery('ndvi_latest'),
					})
					if (data && ndviOutput) {
						ndviOutput.textContent = JSON.stringify(data, null, 2)
					}
				})
			}
			if (ndviTimeseriesButton) {
				ndviTimeseriesButton.addEventListener('click', async () => {
					const data = await runNdviRequest('timeseries', farmNdviTimeseriesUrl, {
						method: 'GET',
						query: buildNdviQuery('ndvi_timeseries'),
					})
					if (data) {
						const observations = Array.isArray(data?.observations)
							? data.observations
							: Array.isArray(data?.results)
								? data.results
								: Array.isArray(data)
									? data
									: []
						renderNdviTable(observations)
						if (ndviOutput) {
							ndviOutput.textContent = observations.length === 0 ? JSON.stringify(data, null, 2) : ''
						}
					}
				})
			}
			if (ndviRasterButton) {
				ndviRasterButton.addEventListener('click', () => {
					clearFarmsNotes()
					if (!selectedFarm) {
						showFarmsError('Select a farm first.')
						return
					}
					const url = farmNdviRasterUrl.replace('__FARM_ID__', encodeURIComponent(selectedFarm.id))
					const query = buildNdviQuery('ndvi_raster')
					const queryString = buildQueryString(query)
					const finalUrl = queryString ? `${url}${url.includes('?') ? '&' : '?'}${queryString}` : url
					const resolvedUrl = ncGenerateUrl(finalUrl)
					if (ndviRasterImg) {
						ndviRasterImg.src = resolvedUrl
					}
					if (ndviRasterPreview) {
						ndviRasterPreview.hidden = false
					}
				})
			}
			if (ndviQueueButton) {
				ndviQueueButton.addEventListener('click', async () => {
					const data = await runNdviRequest('queue raster', farmNdviRasterQueueUrl, {
						method: 'POST',
						body: buildNdviBody('ndvi_raster_queue'),
					})
					if (data && ndviOutput) {
						ndviOutput.textContent = JSON.stringify(data, null, 2)
					}
				})
			}
			if (ndviRefreshButton) {
				ndviRefreshButton.addEventListener('click', async () => {
					const data = await runNdviRequest('refresh NDVI', farmNdviRefreshUrl, {
						method: 'POST',
						body: buildNdviBody('ndvi_refresh'),
					})
					if (data && ndviOutput) {
						ndviOutput.textContent = JSON.stringify(data, null, 2)
					}
				})
			}

			(async () => {
				const ok = await loadSchema()
				if (ok) {
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

		setupFarms()

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
