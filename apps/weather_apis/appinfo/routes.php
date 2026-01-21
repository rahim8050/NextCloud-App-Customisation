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
	],
	'ocs' => [
		[
			'name' => 'ocs_api#getIntegrationWhoami',
			'url' => '/api/v1/integration/whoami',
			'verb' => 'GET',
		],
		
	],
];
