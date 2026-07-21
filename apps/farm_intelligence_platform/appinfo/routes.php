<?php

return [
	'routes' => [
		[
			'name' => 'api#getWhoami',
			'url' => '/api/test/whoami',
			'verb' => 'GET',
		],
		[
			'name' => 'api#getIntegrationWhoami',
			'url' => '/api/v1/integration/whoami',
			'verb' => 'GET',
		],
		[
			'name' => 'settings#saveAdmin',
			'url' => '/settings/admin',
			'verb' => 'POST',
		],
		[
			'name' => 'adminConfig#generateCredentials',
			'url' => '/api/v1/admin/generate-credentials',
			'verb' => 'POST',
		],
		[
			'name' => 'adminConfig#rotateHmac',
			'url' => '/api/v1/admin/rotate-hmac',
			'verb' => 'POST',
		],
		[
			'name' => 'adminConfig#getConfig',
			'url' => '/api/v1/admin/config',
			'verb' => 'GET',
		],
		// Admin-only test connection (calls DRF token endpoint).
		[
			'name' => 'adminConfig#testConnection',
			'url' => '/api/v1/admin/test-connection',
			'verb' => 'POST',
		],
		[
			'name' => 'adminConfig#diagnostics',
			'url' => '/admin/diagnostics',
			'verb' => 'GET',
		],
		[
			'name' => 'adminConfig#previewPng',
			'url' => '/admin/preview.png',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getSchema',
			'url' => '/api/v1/admin/farms/schema',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#listFarms',
			'url' => '/api/v1/admin/farms/list',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#createFarm',
			'url' => '/api/v1/admin/farms/create',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#syncFarm',
			'url' => '/api/v1/admin/farms/sync',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#getFarm',
			'url' => '/api/v1/admin/farms/{id}',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#updateFarm',
			'url' => '/api/v1/admin/farms/{id}',
			'verb' => 'PUT',
		],
		[
			'name' => 'adminFarms#patchFarm',
			'url' => '/api/v1/admin/farms/{id}',
			'verb' => 'PATCH',
		],
		[
			'name' => 'adminFarms#deleteFarm',
			'url' => '/api/v1/admin/farms/{id}',
			'verb' => 'DELETE',
		],
		[
			'name' => 'adminFarms#getNdviLatest',
			'url' => '/api/v1/admin/farms/{farmId}/ndvi/latest',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getNdviTimeseries',
			'url' => '/api/v1/admin/farms/{farmId}/ndvi/timeseries',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getNdviRasterPng',
			'url' => '/api/v1/admin/farms/{farmId}/ndvi/raster.png',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#queueNdviRaster',
			'url' => '/api/v1/admin/farms/{farmId}/ndvi/raster/queue',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#refreshNdvi',
			'url' => '/api/v1/admin/farms/{farmId}/ndvi/refresh',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#getWeatherCurrent',
			'url' => '/api/v1/admin/farms/{farmId}/weather/current',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getWeatherHourly',
			'url' => '/api/v1/admin/farms/{farmId}/weather/hourly',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getWeatherDaily',
			'url' => '/api/v1/admin/farms/{farmId}/weather/daily',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getNdwiLatest',
			'url' => '/api/v1/admin/farms/{farmId}/ndwi/latest',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getNdwiTimeseries',
			'url' => '/api/v1/admin/farms/{farmId}/ndwi/timeseries',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getNdwiRasterPng',
			'url' => '/api/v1/admin/farms/{farmId}/ndwi/raster.png',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#queueNdwiRaster',
			'url' => '/api/v1/admin/farms/{farmId}/ndwi/raster/queue',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#refreshNdwi',
			'url' => '/api/v1/admin/farms/{farmId}/ndwi/refresh',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#getNdwiFarmState',
			'url' => '/api/v1/admin/farms/{farmId}/ndwi/state',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getNdmiLatest',
			'url' => '/api/v1/admin/farms/{farmId}/ndmi/latest',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getNdmiTimeseries',
			'url' => '/api/v1/admin/farms/{farmId}/ndmi/timeseries',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getNdmiRasterPng',
			'url' => '/api/v1/admin/farms/{farmId}/ndmi/raster.png',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#queueNdmiRaster',
			'url' => '/api/v1/admin/farms/{farmId}/ndmi/raster/queue',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#refreshNdmi',
			'url' => '/api/v1/admin/farms/{farmId}/ndmi/refresh',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#getNdmiFarmState',
			'url' => '/api/v1/admin/farms/{farmId}/ndmi/state',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getRviLatest',
			'url' => '/api/v1/admin/farms/{farmId}/rvi/latest',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getRviTimeseries',
			'url' => '/api/v1/admin/farms/{farmId}/rvi/timeseries',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getRviRasterPng',
			'url' => '/api/v1/admin/farms/{farmId}/rvi/raster.png',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#queueRviRaster',
			'url' => '/api/v1/admin/farms/{farmId}/rvi/raster/queue',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#refreshRvi',
			'url' => '/api/v1/admin/farms/{farmId}/rvi/refresh',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#getRviFarmState',
			'url' => '/api/v1/admin/farms/{farmId}/rvi/state',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getRviRasterTile',
			'url' => '/api/v1/admin/farms/{farmId}/rvi/tiles',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getRviRasterDates',
			'url' => '/api/v1/admin/farms/{farmId}/rvi/raster-dates',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getS1SmiLatest',
			'url' => '/api/v1/admin/farms/{farmId}/s1_smi/latest',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getS1SmiTimeseries',
			'url' => '/api/v1/admin/farms/{farmId}/s1_smi/timeseries',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getS1SmiRasterPng',
			'url' => '/api/v1/admin/farms/{farmId}/s1_smi/raster.png',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#queueS1SmiRaster',
			'url' => '/api/v1/admin/farms/{farmId}/s1_smi/raster/queue',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#refreshS1Smi',
			'url' => '/api/v1/admin/farms/{farmId}/s1_smi/refresh',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#getS1SmiFarmState',
			'url' => '/api/v1/admin/farms/{farmId}/s1_smi/state',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getS1SmiRasterTile',
			'url' => '/api/v1/admin/farms/{farmId}/s1_smi/tiles',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getS1SmiRasterDates',
			'url' => '/api/v1/admin/farms/{farmId}/s1_smi/raster-dates',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getNdviRasterTile',
			'url' => '/api/v1/admin/farms/{farmId}/ndvi/tiles',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getNdwiRasterTile',
			'url' => '/api/v1/admin/farms/{farmId}/ndwi/tiles',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getNdmiRasterTile',
			'url' => '/api/v1/admin/farms/{farmId}/ndmi/tiles',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getNdviRasterDates',
			'url' => '/api/v1/admin/farms/{farmId}/ndvi/raster-dates',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getNdwiRasterDates',
			'url' => '/api/v1/admin/farms/{farmId}/ndwi/raster-dates',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getNdmiRasterDates',
			'url' => '/api/v1/admin/farms/{farmId}/ndmi/raster-dates',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#getFarmState',
			'url' => '/api/v1/admin/farms/{farmId}/state',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#listFarmObservations',
			'url' => '/api/v1/admin/farms/{farmId}/observations',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#createFarmObservation',
			'url' => '/api/v1/admin/farms/{farmId}/observations',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#getFarmObservation',
			'url' => '/api/v1/admin/farms/{farmId}/observations/{observationId}',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#patchFarmObservation',
			'url' => '/api/v1/admin/farms/{farmId}/observations/{observationId}',
			'verb' => 'PATCH',
		],
		[
			'name' => 'adminFarms#deleteFarmObservation',
			'url' => '/api/v1/admin/farms/{farmId}/observations/{observationId}',
			'verb' => 'DELETE',
		],
		[
			'name' => 'adminFarms#listFarmActivities',
			'url' => '/api/v1/admin/farms/{farmId}/activities',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#createFarmActivity',
			'url' => '/api/v1/admin/farms/{farmId}/activities',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#getFarmActivity',
			'url' => '/api/v1/admin/farms/{farmId}/activities/{activityId}',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#patchFarmActivity',
			'url' => '/api/v1/admin/farms/{farmId}/activities/{activityId}',
			'verb' => 'PATCH',
		],
		[
			'name' => 'adminFarms#deleteFarmActivity',
			'url' => '/api/v1/admin/farms/{farmId}/activities/{activityId}',
			'verb' => 'DELETE',
		],
		// NDVI utility endpoints
		[
			'name' => 'adminFarms#getNdviJobStatus',
			'url' => '/api/v1/admin/ndvi/jobs/{jobId}',
			'verb' => 'GET',
		],
		[
			'name' => 'adminFarms#ndviIngest',
			'url' => '/api/v1/admin/ndvi/ingest',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#resetNdviCircuitBreaker',
			'url' => '/api/v1/admin/ndvi/circuit-breaker/reset',
			'verb' => 'POST',
		],
		[
			'name' => 'adminFarms#getNdviUpstreamHealth',
			'url' => '/api/v1/admin/ndvi/health/upstream',
			'verb' => 'GET',
		],
		// Activity endpoints
		[
			'name' => 'adminActivities#getSchema',
			'url' => '/api/v1/activities/schema',
			'verb' => 'GET',
		],
		[
			'name' => 'adminActivities#listActivities',
			'url' => '/api/v1/activities/list',
			'verb' => 'POST',
		],
		[
			'name' => 'adminActivities#createActivity',
			'url' => '/api/v1/activities/create',
			'verb' => 'POST',
		],
		[
			'name' => 'adminActivities#getActivity',
			'url' => '/api/v1/activities/{id}',
			'verb' => 'GET',
		],
		[
			'name' => 'adminActivities#updateActivity',
			'url' => '/api/v1/activities/{id}',
			'verb' => 'PUT',
		],
		[
			'name' => 'adminActivities#patchActivity',
			'url' => '/api/v1/activities/{id}',
			'verb' => 'PATCH',
		],
		[
			'name' => 'adminActivities#deleteActivity',
			'url' => '/api/v1/activities/{id}',
			'verb' => 'DELETE',
		],
		// Radio endpoints
		[
			'name' => 'adminRadio#listProviders',
			'url' => '/api/v1/admin/radio/providers',
			'verb' => 'GET',
		],
		[
			'name' => 'adminRadio#listStations',
			'url' => '/api/v1/admin/radio/stations',
			'verb' => 'GET',
		],
		[
			'name' => 'adminRadio#getStation',
			'url' => '/api/v1/admin/radio/stations/{stationId}',
			'verb' => 'GET',
		],
		[
			'name' => 'adminRadio#getStreamUrl',
			'url' => '/api/v1/admin/radio/stations/{stationId}/stream',
			'verb' => 'GET',
		],
		[
			'name' => 'adminRadio#getStationNowPlaying',
			'url' => '/api/v1/admin/radio/stations/{stationId}/now-playing',
			'verb' => 'GET',
		],
		[
			'name' => 'adminRadio#getStationAnalytics',
			'url' => '/api/v1/admin/radio/stations/{stationId}/analytics',
			'verb' => 'GET',
		],
		[
			'name' => 'adminRadio#getStationHealthHistory',
			'url' => '/api/v1/admin/radio/stations/{stationId}/health',
			'verb' => 'GET',
		],
		[
			'name' => 'adminRadio#getRadioHealth',
			'url' => '/api/v1/admin/radio/health',
			'verb' => 'GET',
		],
		[
			'name' => 'adminRadio#getCurrentEmergency',
			'url' => '/api/v1/admin/radio/emergency/current',
			'verb' => 'GET',
		],
		[
			'name' => 'adminRadio#getEmergencyHistory',
			'url' => '/api/v1/admin/radio/emergency/history',
			'verb' => 'GET',
		],
		[
			'name' => 'adminRadio#createEmergency',
			'url' => '/api/v1/admin/radio/emergency',
			'verb' => 'POST',
		],
		[
			'name' => 'adminRadio#updateEmergency',
			'url' => '/api/v1/admin/radio/emergency/{pk}',
			'verb' => 'PATCH',
		],
		[
			'name' => 'adminRadio#deleteEmergency',
			'url' => '/api/v1/admin/radio/emergency/{pk}',
			'verb' => 'DELETE',
		],
		[
			'name' => 'adminRadio#synthesizeTts',
			'url' => '/api/v1/admin/radio/tts',
			'verb' => 'POST',
		],
		// Radio user endpoints (authenticated, non-admin)
		[
			'name' => 'radioUser#listFavorites',
			'url' => '/api/v1/radio/favorites',
			'verb' => 'GET',
		],
		[
			'name' => 'radioUser#addFavorite',
			'url' => '/api/v1/radio/favorites',
			'verb' => 'POST',
		],
		[
			'name' => 'radioUser#removeFavorite',
			'url' => '/api/v1/radio/favorites/{stationId}',
			'verb' => 'DELETE',
		],
		[
			'name' => 'radioUser#listHistory',
			'url' => '/api/v1/radio/history',
			'verb' => 'GET',
		],
		[
			'name' => 'radioUser#getRecentHistory',
			'url' => '/api/v1/radio/history/recent',
			'verb' => 'GET',
		],
		[
			'name' => 'radioUser#stopSession',
			'url' => '/api/v1/radio/history/{sessionId}/stop',
			'verb' => 'POST',
		],
		[
			'name' => 'radioUser#getSignedStream',
			'url' => '/api/v1/radio/stations/{stationId}/stream/signed',
			'verb' => 'GET',
		],
		// Admin API keys endpoints
		[
			'name' => 'adminApiKeys#listKeys',
			'url' => '/api/v1/admin/keys',
			'verb' => 'GET',
		],
		[
			'name' => 'adminApiKeys#createKey',
			'url' => '/api/v1/admin/keys',
			'verb' => 'POST',
		],
		[
			'name' => 'adminApiKeys#revokeKey',
			'url' => '/api/v1/admin/keys/{pk}',
			'verb' => 'DELETE',
		],
		[
			'name' => 'adminApiKeys#rotateKey',
			'url' => '/api/v1/admin/keys/{pk}/rotate',
			'verb' => 'POST',
		],
		// Podcasts endpoints
		[
			'name' => 'podcasts#list',
			'url' => '/api/v1/podcasts',
			'verb' => 'GET',
		],
		[
			'name' => 'podcasts#get',
			'url' => '/api/v1/podcasts/{podcastId}',
			'verb' => 'GET',
		],
		[
			'name' => 'podcasts#listEpisodes',
			'url' => '/api/v1/podcasts/{podcastId}/episodes',
			'verb' => 'GET',
		],
		[
			'name' => 'podcasts#refresh',
			'url' => '/api/v1/podcasts/{podcastId}/refresh',
			'verb' => 'POST',
		],
		[
			'name' => 'podcasts#getStreamUrl',
			'url' => '/api/v1/podcasts/episodes/{episodeId}/stream',
			'verb' => 'GET',
		],
		// User alerts endpoints
		[
			'name' => 'userAlerts#listSubscriptions',
			'url' => '/api/v1/alerts/subscriptions',
			'verb' => 'GET',
		],
		[
			'name' => 'userAlerts#createSubscription',
			'url' => '/api/v1/alerts/subscriptions',
			'verb' => 'POST',
		],
		[
			'name' => 'userAlerts#getSubscription',
			'url' => '/api/v1/alerts/subscriptions/{subId}',
			'verb' => 'GET',
		],
		[
			'name' => 'userAlerts#updateSubscription',
			'url' => '/api/v1/alerts/subscriptions/{subId}',
			'verb' => 'PATCH',
		],
		[
			'name' => 'userAlerts#deleteSubscription',
			'url' => '/api/v1/alerts/subscriptions/{subId}',
			'verb' => 'DELETE',
		],
		[
			'name' => 'userAlerts#listAlerts',
			'url' => '/api/v1/alerts/alerts',
			'verb' => 'GET',
		],
		[
			'name' => 'userAlerts#getAlert',
			'url' => '/api/v1/alerts/alerts/{alertId}',
			'verb' => 'GET',
		],
		// Admin alerts endpoints
		[
			'name' => 'adminAlerts#broadcast',
			'url' => '/api/v1/admin/alerts/broadcast',
			'verb' => 'POST',
		],
		// Activities health endpoint
		[
			'name' => 'adminActivities#getHealth',
			'url' => '/api/v1/admin/activities/health',
			'verb' => 'GET',
		],

	],
	'ocs' => [
		[
			'name' => 'ocs_api#getIntegrationWhoami',
			'url' => '/api/v1/integration/whoami',
			'verb' => 'GET',
		],
		
	],
];
