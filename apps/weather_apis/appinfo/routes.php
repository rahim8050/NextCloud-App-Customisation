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
		// Activity endpoints
		[
			'name' => 'adminActivities#getSchema',
			'url' => '/api/v1/admin/activities/schema',
			'verb' => 'GET',
		],
		[
			'name' => 'adminActivities#listActivities',
			'url' => '/api/v1/admin/activities/list',
			'verb' => 'POST',
		],
		[
			'name' => 'adminActivities#createActivity',
			'url' => '/api/v1/admin/activities/create',
			'verb' => 'POST',
		],
		[
			'name' => 'adminActivities#getActivity',
			'url' => '/api/v1/admin/activities/{id}',
			'verb' => 'GET',
		],
		[
			'name' => 'adminActivities#updateActivity',
			'url' => '/api/v1/admin/activities/{id}',
			'verb' => 'PUT',
		],
		[
			'name' => 'adminActivities#patchActivity',
			'url' => '/api/v1/admin/activities/{id}',
			'verb' => 'PATCH',
		],
		[
			'name' => 'adminActivities#deleteActivity',
			'url' => '/api/v1/admin/activities/{id}',
			'verb' => 'DELETE',
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
