const assert = require('assert')
const ndvi = require('../../js/ndvi-latest.js')

const latestPayload = {
	status: 0,
	message: 'NDVI latest (cached)',
	data: {
		observation: {
			bucket_date: '2026-01-27',
			mean: 0.42,
			min: 0.2,
			max: 0.6,
			sample_count: 120,
			cloud_fraction: 0.15,
		},
		engine: 'stac',
		lookback_days: 14,
		max_cloud: 30,
		stale: false,
	},
}

const latestState = ndvi.reduceLatestState(undefined, { type: 'success', payload: latestPayload }, '2026-02-02')
assert.strictEqual(latestState.status, ndvi.NDVI_LATEST_STATE.fresh)
assert.strictEqual(latestState.vm.hasObservation, true)
assert.strictEqual(latestState.vm.date, '2026-01-27')
assert.strictEqual(latestState.vm.mean, 0.42)
assert.strictEqual(latestState.vm.cloudFraction, 0.15)
assert.strictEqual(latestState.vm.sampleCount, 120)
assert.strictEqual(latestState.vm.cached, true)
assert.strictEqual(latestState.vm.stale, false)

const latestModel = ndvi.buildLatestCardModel(latestState)
assert.ok(!JSON.stringify(latestModel).includes('No recent observation.'))
assert.ok(JSON.stringify(latestModel).includes('Mean'))

const timeseriesPayload = {
	status: 0,
	data: {
		observations: [
			{
				bucket_date: '2026-01-04',
				mean: '0.31',
				min: 0.2,
				max: 0.5,
				cloud_fraction: 0.1,
				sample_count: 50,
			},
			{
				date: '2026-01-18',
				mean: 0.44,
				min: 0.3,
				max: 0.6,
				cloud_fraction: 0.2,
				sampleCount: '80',
			},
		],
	},
}

const seriesState = ndvi.reduceTimeseriesState(
	undefined,
	{ type: 'success', payload: timeseriesPayload },
	'2026-01-01',
	'2026-01-30',
)
assert.strictEqual(seriesState.status, ndvi.NDVI_SERIES_STATE.has_data)
assert.ok(seriesState.vm.receivedCount >= 2)
assert.ok(seriesState.vm.shownCount >= 2)
const seriesModel = ndvi.buildTimeseriesCardModel(seriesState)
assert.ok(!JSON.stringify(seriesModel).includes('No observations for the selected range.'))
assert.ok(seriesModel.summary.includes('Received'))

const filteredState = ndvi.reduceTimeseriesState(
	undefined,
	{ type: 'success', payload: timeseriesPayload },
	'2025-12-01',
	'2025-12-31',
)
assert.strictEqual(filteredState.status, ndvi.NDVI_SERIES_STATE.no_data)
assert.strictEqual(filteredState.vm.filterWarning, true)
const filteredModel = ndvi.buildTimeseriesCardModel(filteredState)
assert.ok(filteredModel.summary.includes('Received'))
assert.ok(filteredModel.facts.some((fact) => String(fact.value).includes('Received')))
assert.ok(
	filteredModel.facts.some(
		(fact) => String(fact.value)
			=== `API returned ${filteredState.vm.receivedCount} points but none matched range (check date parsing / inclusive end).`,
	),
)

const errorState = ndvi.reduceLatestState(undefined, {
	type: 'success',
	payload: { status: 1, message: 'failed' },
})
assert.strictEqual(errorState.status, ndvi.NDVI_LATEST_STATE.error)
const errorModel = ndvi.buildLatestCardModel(errorState)
assert.strictEqual(errorModel.showRetry, true)

const seriesErrorState = ndvi.reduceTimeseriesState(
	undefined,
	{ type: 'success', payload: { status: 1, message: 'fail' } },
	'2026-01-01',
	'2026-01-30',
)
assert.strictEqual(seriesErrorState.status, ndvi.NDVI_SERIES_STATE.error)
const seriesErrorModel = ndvi.buildTimeseriesCardModel(seriesErrorState)
assert.strictEqual(seriesErrorModel.showRetry, true)

assert.strictEqual(ndvi.parseDateOnly('not-a-date'), null)
