(() => {
	'use strict'

	const DAY_MS = 86400000
	const ISO_DATE_RE = /^\d{4}-\d{2}-\d{2}$/
	const WEEKDAY_ABBREVIATIONS = ['Su', 'M', 'Tu', 'W', 'Th', 'F', 'Sa']
	const MONTH_ABBREVIATIONS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

	const formatWeekday = (dayIndex) => WEEKDAY_ABBREVIATIONS[dayIndex] ?? ''
	const formatMonth = (monthIndex) => MONTH_ABBREVIATIONS[monthIndex] ?? ''
	const formatDateParts = (year, month, day) => `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`

	const isPlainObject = (value) => value && typeof value === 'object' && !Array.isArray(value)

	const toStringOrNull = (value) => {
		if (typeof value === 'string') {
			const trimmed = value.trim()
			return trimmed || null
		}
		if (value === null || value === undefined) {
			return null
		}
		return String(value)
	}

	const parseNumber = (value) => {
		if (value === null || value === undefined || value === '') {
			return null
		}
		const num = Number(value)
		return Number.isFinite(num) ? num : null
	}

	const parseDateOnly = (value) => {
		if (value instanceof Date) {
			const year = value.getUTCFullYear()
			const month = value.getUTCMonth() + 1
			const day = value.getUTCDate()
			const iso = formatDateParts(year, month, day)
			return { iso, date: new Date(Date.UTC(year, month - 1, day)) }
		}
		if (typeof value !== 'string') {
			return null
		}
		const raw = value.trim()
		if (!ISO_DATE_RE.test(raw)) {
			return null
		}
		const [yearStr, monthStr, dayStr] = raw.split('-')
		const year = Number(yearStr)
		const month = Number(monthStr)
		const day = Number(dayStr)
		if (!Number.isFinite(year) || !Number.isFinite(month) || !Number.isFinite(day)) {
			return null
		}
		const date = new Date(Date.UTC(year, month - 1, day))
		if (Number.isNaN(date.getTime())) {
			return null
		}
		return { iso: raw, date }
	}

	const normalizeDateOnly = (value) => parseDateOnly(value)?.iso ?? null

	const resolveNowDate = (value) => {
		if (value instanceof Date) {
			return parseDateOnly(value)
		}
		if (typeof value === 'string') {
			return parseDateOnly(value)
		}
		return parseDateOnly(new Date())
	}

	const diffDays = (fromIso, nowValue) => {
		const start = parseDateOnly(fromIso)
		const end = resolveNowDate(nowValue)
		if (!start || !end) {
			return null
		}
		return Math.floor((end.date.getTime() - start.date.getTime()) / DAY_MS)
	}

	const unwrapDataEnvelope = (payload) => {
		let current = payload
		for (let depth = 0; depth < 2; depth++) {
			if (isPlainObject(current?.data)) {
				current = current.data
				continue
			}
			break
		}
		return current
	}

	const normalizeObservation = (observation) => {
		if (!isPlainObject(observation)) {
			return null
		}
		const dateValue = observation.bucket_date ?? observation.date ?? observation.bucketDate ?? null
		return {
			date: normalizeDateOnly(dateValue),
			mean: parseNumber(observation.mean ?? observation.mean_ndvi ?? observation.ndvi ?? observation.value),
			min: parseNumber(observation.min),
			max: parseNumber(observation.max),
			cloudFraction: parseNumber(observation.cloud_fraction ?? observation.cloudFraction),
			sampleCount: parseNumber(observation.sample_count ?? observation.sampleCount),
		}
	}

	const normalizeNdviLatest = (raw, nowDate) => {
		const envelope = isPlainObject(raw) ? raw : {}
		const data = unwrapDataEnvelope(envelope)
		const observation = normalizeObservation(data?.observation)
		const hasObservation = Boolean(observation)
		const message = typeof envelope.message === 'string' ? envelope.message : ''
		const cached = Boolean(data?.cached)
			|| Boolean(envelope?.cached)
			|| /\(cached\)/i.test(message)
		const lookbackDays = parseNumber(data?.lookback_days ?? data?.lookbackDays ?? data?.lookback)
		const maxCloud = parseNumber(data?.max_cloud ?? data?.maxCloud ?? data?.max_cloud_pct)
		const engine = toStringOrNull(data?.engine)
		const backendStale = typeof data?.stale === 'boolean' ? data.stale : null
		const daysAgo = observation?.date ? diffDays(observation.date, nowDate) : null
		let stale = backendStale ?? false
		if (backendStale === null && observation?.date && lookbackDays !== null && daysAgo !== null) {
			stale = daysAgo > lookbackDays
		}
		return {
			hasObservation,
			date: observation?.date ?? null,
			mean: observation?.mean ?? null,
			min: observation?.min ?? null,
			max: observation?.max ?? null,
			cloudFraction: observation?.cloudFraction ?? null,
			sampleCount: observation?.sampleCount ?? null,
			engine,
			lookbackDays,
			maxCloud,
			cached,
			stale,
			daysAgo,
			raw,
		}
	}

	const normalizeTimeseriesPoint = (point) => {
		if (!isPlainObject(point)) {
			return null
		}
		const dateValue = point.bucket_date ?? point.date ?? point.bucketDate ?? null
		const date = normalizeDateOnly(dateValue)
		if (!date) {
			return null
		}
		return {
			date,
			mean: parseNumber(point.mean ?? point.mean_ndvi ?? point.ndvi ?? point.value),
			min: parseNumber(point.min),
			max: parseNumber(point.max),
			cloudFraction: parseNumber(point.cloud_fraction ?? point.cloudFraction),
			sampleCount: parseNumber(point.sample_count ?? point.sampleCount),
		}
	}

	const normalizeNdviTimeseries = (raw, rangeStart, rangeEnd) => {
		const envelope = isPlainObject(raw) ? raw : {}
		const data = unwrapDataEnvelope(envelope)
		let observations = []
		if (Array.isArray(data?.observations)) {
			observations = data.observations
		} else if (Array.isArray(data?.results)) {
			observations = data.results
		} else if (Array.isArray(data)) {
			observations = data
		}
		const receivedCount = observations.length
		const startInfo = parseDateOnly(rangeStart)
		const endInfo = parseDateOnly(rangeEnd)
		const points = []

		observations.forEach((item) => {
			const normalized = normalizeTimeseriesPoint(item)
			if (!normalized) {
				return
			}
			const pointInfo = parseDateOnly(normalized.date)
			if (!pointInfo) {
				return
			}
			if (startInfo && pointInfo.date < startInfo.date) {
				return
			}
			if (endInfo && pointInfo.date > endInfo.date) {
				return
			}
			points.push({ ...normalized, _dateMs: pointInfo.date.getTime() })
		})

		points.sort((a, b) => a._dateMs - b._dateMs)
		const sanitizedPoints = points.map(({ _dateMs, ...rest }) => rest)
		const shownCount = sanitizedPoints.length
		const filterWarning = receivedCount > 0 && shownCount === 0

		return {
			rangeStart: startInfo?.iso ?? normalizeDateOnly(rangeStart),
			rangeEnd: endInfo?.iso ?? normalizeDateOnly(rangeEnd),
			receivedCount,
			shownCount,
			points: sanitizedPoints,
			filterWarning,
			raw,
		}
	}

	const NDVI_LATEST_STATE = {
		loading: 'loading',
		error: 'error',
		no_data: 'no_data',
		fresh: 'fresh',
		stale: 'stale',
	}

	const NDVI_SERIES_STATE = {
		loading: 'loading',
		error: 'error',
		no_data: 'no_data',
		has_data: 'has_data',
	}

	const isOkStatus = (payload) => {
		const status = payload?.status
		return status === undefined || status === 0 || status === 'ok' || status === true
	}

	const extractMessage = (payload, fallback) => {
		if (!payload || typeof payload !== 'object') {
			return fallback
		}
		const message = payload.message || payload.error?.message
		return message ? String(message) : fallback
	}

	const createEmptyTimeseriesVm = (rangeStart, rangeEnd) => ({
		rangeStart: normalizeDateOnly(rangeStart),
		rangeEnd: normalizeDateOnly(rangeEnd),
		receivedCount: 0,
		shownCount: 0,
		points: [],
		filterWarning: false,
		raw: null,
	})

	const reduceLatestState = (state, action, nowDate) => {
		let current = { status: NDVI_LATEST_STATE.no_data, vm: null, payload: null, message: '' }
		if (state && typeof state === 'object') {
			current = state
		}
		if (!action || typeof action !== 'object') {
			return current
		}
		if (action.type === 'request') {
			return { status: NDVI_LATEST_STATE.loading, vm: null, payload: null, message: '' }
		}
		if (action.type === 'success') {
			if (!isOkStatus(action.payload)) {
				return {
					status: NDVI_LATEST_STATE.error,
					vm: null,
					payload: action.payload ?? null,
					message: extractMessage(action.payload, 'Unable to load latest NDVI.'),
				}
			}
			const vm = normalizeNdviLatest(action.payload, nowDate)
			let status = NDVI_LATEST_STATE.fresh
			if (!vm.hasObservation) {
				status = NDVI_LATEST_STATE.no_data
			} else if (vm.stale) {
				status = NDVI_LATEST_STATE.stale
			}
			return { status, vm, payload: action.payload ?? null, message: '' }
		}
		if (action.type === 'failure') {
			return {
				status: NDVI_LATEST_STATE.error,
				vm: null,
				payload: action.payload ?? null,
				message: action.message ? String(action.message) : 'Unable to load latest NDVI.',
			}
		}
		if (action.type === 'reset') {
			return { status: NDVI_LATEST_STATE.no_data, vm: null, payload: null, message: '' }
		}
		return current
	}

	const reduceTimeseriesState = (state, action, rangeStart, rangeEnd) => {
		let current = {
			status: NDVI_SERIES_STATE.no_data,
			vm: createEmptyTimeseriesVm(rangeStart, rangeEnd),
			payload: null,
			message: '',
		}
		if (state && typeof state === 'object') {
			current = state
		}
		if (!action || typeof action !== 'object') {
			return current
		}
		if (action.type === 'request') {
			return {
				status: NDVI_SERIES_STATE.loading,
				vm: createEmptyTimeseriesVm(rangeStart, rangeEnd),
				payload: null,
				message: '',
			}
		}
		if (action.type === 'success') {
			if (!isOkStatus(action.payload)) {
				return {
					status: NDVI_SERIES_STATE.error,
					vm: createEmptyTimeseriesVm(rangeStart, rangeEnd),
					payload: action.payload ?? null,
					message: extractMessage(action.payload, 'Unable to load NDVI timeseries.'),
				}
			}
			const vm = normalizeNdviTimeseries(action.payload, rangeStart, rangeEnd)
			const status = vm.shownCount === 0 ? NDVI_SERIES_STATE.no_data : NDVI_SERIES_STATE.has_data
			return { status, vm, payload: action.payload ?? null, message: '' }
		}
		if (action.type === 'failure') {
			return {
				status: NDVI_SERIES_STATE.error,
				vm: createEmptyTimeseriesVm(rangeStart, rangeEnd),
				payload: action.payload ?? null,
				message: action.message ? String(action.message) : 'Unable to load NDVI timeseries.',
			}
		}
		if (action.type === 'reset') {
			return {
				status: NDVI_SERIES_STATE.no_data,
				vm: createEmptyTimeseriesVm(rangeStart, rangeEnd),
				payload: null,
				message: '',
			}
		}
		return current
	}

	const formatNumber = (value, digits = 3) => {
		if (value === null || value === undefined) {
			return '-'
		}
		const num = Number(value)
		if (!Number.isFinite(num)) {
			return '-'
		}
		const safeDigits = Number.isFinite(digits)
			? Math.max(0, Math.min(6, Math.trunc(digits)))
			: 3
		const fixed = safeDigits > 0 ? num.toFixed(safeDigits) : String(Math.round(num))
		const trimmed = safeDigits > 0
			? fixed.replace(/(?:\.0+|(\.\d*?)0+)$/, '$1')
			: fixed
		const [whole, fraction] = trimmed.split('.')
		const grouped = whole.replace(/\B(?=(\d{3})+(?!\d))/g, ',')
		return fraction ? `${grouped}.${fraction}` : grouped
	}

	const formatPercent = (value, digits = 1) => {
		if (value === null || value === undefined) {
			return '-'
		}
		const num = Number(value)
		if (!Number.isFinite(num)) {
			return '-'
		}
		return `${formatNumber(num * 100, digits)}%`
	}

	const formatCount = (value) => formatNumber(value, 0)

	const resolveCurrentYear = (nowValue) => {
		const now = resolveNowDate(nowValue)
		return now?.date?.getUTCFullYear() ?? new Date().getUTCFullYear()
	}

	const formatDateLabel = (date, includeYear) => {
		const day = date.getUTCDate()
		const month = formatMonth(date.getUTCMonth())
		const year = date.getUTCFullYear()
		const base = `${day} ${month}`.trim()
		return includeYear ? `${base} ${year}` : base
	}

	const formatDateWithWeekday = (value, nowValue) => {
		if (!value) {
			return '-'
		}
		const parsed = parseDateOnly(value)
		if (!parsed) {
			return String(value)
		}
		const currentYear = resolveCurrentYear(nowValue)
		const includeYear = parsed.date.getUTCFullYear() !== currentYear
		const weekday = formatWeekday(parsed.date.getUTCDay())
		const dateLabel = formatDateLabel(parsed.date, includeYear)
		return weekday ? `${weekday} ${dateLabel}` : dateLabel
	}

	const formatDateRangeLabel = (startValue, endValue) => {
		const start = parseDateOnly(startValue)
		const end = parseDateOnly(endValue)
		if (!start && !end) {
			return '-'
		}
		if (start && !end) {
			return formatDateLabel(start.date, true)
		}
		if (!start && end) {
			return formatDateLabel(end.date, true)
		}
		const startDate = start.date
		const endDate = end.date
		const sameYear = startDate.getUTCFullYear() === endDate.getUTCFullYear()
		const sameMonth = sameYear && startDate.getUTCMonth() === endDate.getUTCMonth()
		if (sameMonth) {
			const month = formatMonth(startDate.getUTCMonth())
			const year = startDate.getUTCFullYear()
			const startDay = startDate.getUTCDate()
			const endDay = endDate.getUTCDate()
			if (startDay === endDay) {
				return `${month} ${startDay}, ${year}`
			}
			return `${month} ${startDay}\u2013${endDay}, ${year}`
		}
		if (sameYear) {
			const year = startDate.getUTCFullYear()
			const startLabel = `${formatMonth(startDate.getUTCMonth())} ${startDate.getUTCDate()}`
			const endLabel = `${formatMonth(endDate.getUTCMonth())} ${endDate.getUTCDate()}`
			return `${startLabel}\u2013${endLabel}, ${year}`
		}
		const startLabel = `${formatMonth(startDate.getUTCMonth())} ${startDate.getUTCDate()}, ${startDate.getUTCFullYear()}`
		const endLabel = `${formatMonth(endDate.getUTCMonth())} ${endDate.getUTCDate()}, ${endDate.getUTCFullYear()}`
		return `${startLabel}\u2013${endLabel}`
	}

	const formatDateWithAge = (dateValue, daysAgo) => {
		if (!dateValue) {
			return '-'
		}
		const formatted = formatDateWithWeekday(dateValue)
		if (daysAgo === null || daysAgo === undefined) {
			return formatted
		}
		return `${formatted} (${daysAgo} day${daysAgo === 1 ? '' : 's'} ago)`
	}

	const buildLatestCardModel = (state) => {
		const model = {
			title: 'Latest NDVI',
			level: 'info',
			summary: '',
			badges: [],
			facts: [],
			showRetry: false,
		}
		if (!state || typeof state !== 'object') {
			return model
		}
		switch (state.status) {
		case NDVI_LATEST_STATE.loading:
			return { ...model, summary: 'Loading latest NDVI...' }
		case NDVI_LATEST_STATE.error:
			return {
				...model,
				level: 'error',
				summary: state.message || 'Unable to load latest NDVI.',
				showRetry: true,
			}
		case NDVI_LATEST_STATE.no_data:
			return {
				...model,
				level: 'warning',
				summary: 'No latest NDVI observation available.',
			}
		case NDVI_LATEST_STATE.stale:
		case NDVI_LATEST_STATE.fresh: {
			const vm = state.vm || {}
			const badges = [state.status === NDVI_LATEST_STATE.stale ? 'Stale' : 'Fresh']
			if (vm.cached) {
				badges.push('Cached')
			}
			const facts = []
			if (vm.date) {
				facts.push({ label: 'Date', value: formatDateWithAge(vm.date, vm.daysAgo) })
			}
			facts.push({ label: 'Min', value: formatNumber(vm.min) })
			facts.push({ label: 'Max', value: formatNumber(vm.max) })
			facts.push({ label: 'Cloud %', value: formatPercent(vm.cloudFraction, 1) })
			facts.push({ label: 'Samples', value: formatCount(vm.sampleCount) })
			facts.push({ label: 'Engine', value: vm.engine ?? '-' })
			facts.push({ label: 'Lookback (days)', value: formatCount(vm.lookbackDays) })
			facts.push({ label: 'Max cloud (%)', value: formatCount(vm.maxCloud) })
			if (state.status === NDVI_LATEST_STATE.stale) {
				facts.push({ label: 'Warning', value: 'Observation is stale.' })
			}
			return {
				...model,
				level: state.status === NDVI_LATEST_STATE.stale ? 'warning' : 'success',
				summary: `Mean ${formatNumber(vm.mean)}`,
				badges,
				facts,
			}
		}
		default:
			return model
		}
	}

	const buildTimeseriesCardModel = (state) => {
		const model = {
			title: 'NDVI timeseries',
			level: 'info',
			summary: '',
			badges: [],
			facts: [],
			showRetry: false,
			emptyMessage: '',
		}
		if (!state || typeof state !== 'object') {
			return model
		}
		const vm = state.vm || createEmptyTimeseriesVm(null, null)
		const rangeLabel = formatDateRangeLabel(vm.rangeStart, vm.rangeEnd)
		const rangeStart = vm.rangeStart ? formatDateWithWeekday(vm.rangeStart) : '-'
		const rangeEnd = vm.rangeEnd ? formatDateWithWeekday(vm.rangeEnd) : '-'
		const shown = Number.isFinite(vm.shownCount) ? vm.shownCount : vm.receivedCount
		const observationLabel = shown === 1 ? 'observation' : 'observations'
		const summary = `${shown} ${observationLabel} (${rangeLabel})`
		const facts = [
			{ label: 'Range start', value: rangeStart },
			{ label: 'Range end', value: rangeEnd },
			{ label: 'Counts', value: `${vm.receivedCount} total, ${vm.shownCount} shown` },
		]
		if (state.status === NDVI_SERIES_STATE.loading) {
			return { ...model, summary, facts }
		}
		if (state.status === NDVI_SERIES_STATE.error) {
			return {
				...model,
				level: 'error',
				summary: state.message || 'Unable to load NDVI timeseries.',
				facts,
				showRetry: true,
			}
		}
		if (state.status === NDVI_SERIES_STATE.no_data) {
			if (vm.filterWarning) {
				facts.push({
					label: 'Warning',
					value: `API returned ${vm.receivedCount} points but none matched range (check date parsing / inclusive end).`,
				})
			}
			return {
				...model,
				level: 'warning',
				summary,
				facts,
				emptyMessage: 'No NDVI observations in the selected range.',
			}
		}
		return {
			...model,
			level: 'success',
			summary,
			facts,
		}
	}

	const api = {
		normalizeNdviLatest,
		normalizeNdviTimeseries,
		NDVI_LATEST_STATE,
		NDVI_SERIES_STATE,
		reduceLatestState,
		reduceTimeseriesState,
		buildLatestCardModel,
		buildTimeseriesCardModel,
		parseDateOnly,
		formatNumber,
		formatPercent,
		formatCount,
		formatDateWithWeekday,
		formatDateRangeLabel,
	}

	if (typeof window !== 'undefined') {
		window.WeatherApisNdviUi = api
		window.WeatherApisNdviLatest = api
	}
	if (typeof module !== 'undefined' && module.exports) {
		module.exports = api
	}
})()
