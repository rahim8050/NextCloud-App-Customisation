<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Controller;

use OCA\FarmIntelligencePlatform\Service\DrfSchemaService;
use OCA\FarmIntelligencePlatform\Service\FarmSyncServiceInterface;
use OCA\FarmIntelligencePlatform\Service\HttpStatus;
use OCA\FarmIntelligencePlatform\Service\LogSanitizer;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Response;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

final class AdminFarmsController extends Controller {
	private const RESERVED_PARAMS = [
		'requesttoken',
		'format',
		'id',
		'farm_id',
		'_route',
		'ocs-apirequest',
		'ocs_api_request',
		'ocsapirequest',
	];

	public function __construct(
		string $appName,
		IRequest $request,
		private readonly DrfSchemaService $schemaService,
		private readonly WeatherApiClientInterface $weatherApiClient,
		private readonly FarmSyncServiceInterface $farmSyncService,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
	}

	#[AdminRequired]
	public function getSchema(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('farm schema', $requestId);

		try {
			$result = $this->schemaService->getFarmSchemaSummary($requestId);
		} catch (WeatherApiException $exception) {
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			$this->logger->warning(
				'Weather API schema fetch failed',
				LogSanitizer::sanitizeContext([
					'requestId' => $requestId,
					'error' => $throwable->getMessage(),
				]),
			);
			return $this->buildErrorResponse(
				'backend_error',
				'Unable to load schema.',
				$requestId,
				Http::STATUS_BAD_REQUEST,
			);
		}

		$schema = is_array($result['schema'] ?? null) ? $result['schema'] : [];
		$fields = is_array($schema['fields'] ?? null) ? $schema['fields'] : [];
		$columns = $schema['columns'] ?? [];
		if (!is_array($columns)) {
			$columns = [];
		}
		$operationIds = [];
		$operations = $schema['operations'] ?? [];
		if (is_array($operations)) {
			foreach ($operations as $operation) {
				if (!is_array($operation)) {
					continue;
				}
				$operationId = $operation['operationId'] ?? null;
				if (is_string($operationId) && $operationId !== '') {
					$operationIds[] = $operationId;
				}
			}
		}
		$this->logger->debug(
			'Weather API farm schema summary',
			LogSanitizer::sanitizeContext([
				'requestId' => $requestId,
				'fieldsCount' => count($fields),
				'columnsCount' => count($columns),
				'operationIds' => $operationIds,
			]),
		);

		$payload = $schema;
		$payload['warning'] = $result['warning'];
		$payload['schema'] = $schema;

		return $this->buildSuccessResponse($payload, 'Schema loaded.');
	}


	#[AdminRequired]
	public function listFarms(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('list farms', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('list', $requestId);
			$queryDefs = $operation['queryParams'] ?? [];
			if (!is_array($queryDefs)) {
				$queryDefs = [];
			}
			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);
			$query = $this->buildListQueryParams($params, $queryDefs);
			$path = (string)($operation['path'] ?? '');
			$this->logProxyRequest('list farms', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('list farms', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'list farms');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'list farms');
		}

		return $this->buildSuccessResponse($payload, 'Farms loaded.');
	}


	#[AdminRequired]
	public function createFarm(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('create farm', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('create', $requestId);
			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], true);
			$path = (string)($operation['path'] ?? '');
			$this->logProxyRequest('create farm', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body,
				$requestId,
			);
			$this->logProxyResponse('create farm', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'create farm');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'create farm');
		}

		return $this->buildSuccessResponse($payload, 'Farm created.');
	}

	#[AdminRequired]
	public function syncFarm(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('sync farm', $requestId);

		$params = $this->request->getParams();
		$idempotencyKey = $this->resolveIdempotencyKey();

		try {
			$payload = $this->farmSyncService->sync($params, $requestId, $idempotencyKey);
		} catch (WeatherApiException $exception) {
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			$this->logger->warning(
				'Weather API farm sync failed',
				LogSanitizer::sanitizeContext([
					'requestId' => $requestId,
					'error' => $throwable->getMessage(),
				]),
			);
			return $this->buildErrorResponse(
				'backend_error',
				'Farm sync failed.',
				$requestId,
				Http::STATUS_BAD_REQUEST,
			);
		}

		return $this->buildSuccessResponse($payload, 'Farm synced.');
	}


	#[AdminRequired]
	public function getFarm(string $id): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get farm', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('retrieve', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['id' => $id]);
			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->logProxyRequest('get farm', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('get farm', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get farm');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get farm');
		}

		return $this->buildSuccessResponse($payload, 'Farm loaded.');
	}


	#[AdminRequired]
	public function updateFarm(string $id): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('update farm', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('update', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['id' => $id]);
			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], true);
			$this->logProxyRequest('update farm', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'PUT'),
				$path,
				[],
				$body,
				$requestId,
			);
			$this->logProxyResponse('update farm', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'update farm');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'update farm');
		}

		return $this->buildSuccessResponse($payload, 'Farm updated.');
	}


	#[AdminRequired]
	public function patchFarm(string $id): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('patch farm', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('partial_update', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['id' => $id]);
			
			$params = $this->stripPathParams(
				$this->request->getParams(),
				(string)($operation['path'] ?? ''),
			);
			
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('patch farm', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'PATCH'),
				$path,
				[],
				$body,
				$requestId,
			);
			$this->logProxyResponse('patch farm', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'patch farm');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'patch farm');
		}

		return $this->buildSuccessResponse($payload, 'Farm updated.');
	}


	#[AdminRequired]
	public function deleteFarm(string $id): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('delete farm', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('destroy', $requestId);
			$path = $this->applyPathParams((string)($operation['path'] ?? ''), ['id' => $id]);
			$this->logProxyRequest('delete farm', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'DELETE'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('delete farm', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'delete farm');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'delete farm');
		}

		return $this->buildSuccessResponse($payload, 'Farm deleted.');
	}


	#[AdminRequired]
	public function getNdviLatest(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndvi latest', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_latest', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('ndvi latest', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('ndvi latest', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndvi latest', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndvi latest');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndvi latest');
		}

		return $this->buildSuccessResponse($payload, 'NDVI latest loaded.');
	}


	#[AdminRequired]
	public function getNdviTimeseries(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndvi timeseries', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_timeseries', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$startName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'start');
			$endName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'end');
			$this->ensureDateOrder($query, $startName, $endName);
			$this->logProxyRequest('ndvi timeseries', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('ndvi timeseries', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndvi timeseries', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndvi timeseries');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndvi timeseries');
		}

		return $this->buildSuccessResponse($payload, 'NDVI timeseries loaded.');
	}


	#[AdminRequired]
	public function getNdviRasterPng(string $farmId): Response {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndvi raster', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_raster', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
			if ($dateField !== null && array_key_exists($dateField, $query)) {
				$this->parseIsoDateValue($query[$dateField], $dateField);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('ndvi raster', $operation, $path, $query, $requestId);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
			$this->logProxyResponse('ndvi raster', $operation, $path, $requestId, $binary['statusCode']);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndvi raster', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndvi raster');
		}

		$response = new DataDisplayResponse($binary['body'], Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}


	#[PublicPage]
	#[NoCSRFRequired]
	public function getNdviRasterTile(string $farmId): Response {
		$z = $this->request->getParam('z', '');
		$x = $this->request->getParam('x', '');
		$y = $this->request->getParam('y', '');
		return $this->proxyRasterTile($farmId, 'ndvi', $z, $x, $y);
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getNdviRasterDates(string $farmId): Response {
		return $this->proxyRasterDates($farmId, 'ndvi');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getNdviGeotiff(string $farmId): Response {
		return $this->proxyGeotiff($farmId, 'ndvi');
	}


	#[AdminRequired]
	public function queueNdviRaster(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndvi raster queue', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_raster_queue', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = [];
			$body = null;
			$bodyFields = $operation['bodyFields'] ?? [];
			if ($bodyFields !== []) {
				$body = $this->filterBodyParams($params, $bodyFields, true);
				$dateField = $this->resolveBodyFieldName($bodyFields, 'date');
				if ($dateField !== null && array_key_exists($dateField, $body)) {
					$this->parseIsoDateValue($body[$dateField], $dateField);
				}
			} else {
				$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
				$this->requireQueryParams($query, $operation['queryParams'] ?? []);
				$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
				if ($dateField !== null && array_key_exists($dateField, $query)) {
					$this->parseIsoDateValue($query[$dateField], $dateField);
				}
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('ndvi raster queue', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				$query,
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('ndvi raster queue', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndvi raster queue', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndvi raster queue');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndvi raster queue');
		}

		return $this->buildSuccessResponse($payload, 'NDVI raster queued.');
	}


	#[AdminRequired]
	public function refreshNdvi(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndvi refresh', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_refresh', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('ndvi refresh', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('ndvi refresh', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndvi refresh', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndvi refresh');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndvi refresh');
		}

		return $this->buildSuccessResponse($payload, 'NDVI refresh queued.');
	}


	#[AdminRequired]
	public function getWeatherCurrent(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('weather current', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('weather_current', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('weather current', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('weather current', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('weather current', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'weather current');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'weather current');
		}

		return $this->buildSuccessResponse($payload);
	}


	#[AdminRequired]
	public function getWeatherHourly(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('weather hourly', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('weather_hourly', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->logProxyRequest('weather hourly', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('weather hourly', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('weather hourly', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'weather hourly');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'weather hourly');
		}

		return $this->buildSuccessResponse($payload);
	}


	#[AdminRequired]
	public function getWeatherDaily(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('weather daily', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('weather_daily', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->logProxyRequest('weather daily', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('weather daily', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('weather daily', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'weather daily');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'weather daily');
		}

		return $this->buildSuccessResponse($payload);
	}


	#[AdminRequired]
	public function getFarmState(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('farm state', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('farm_state', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('farm state', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('farm state', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('farm state', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'farm state');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'farm state');
		}

		return $this->buildSuccessResponse($payload);
	}

	// ── NDWI endpoints ─────────────────────────────────────────

	#[AdminRequired]
	public function getNdwiLatest(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndwi latest', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndwi_latest', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('ndwi latest', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('ndwi latest', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndwi latest', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndwi latest');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndwi latest');
		}

		return $this->buildSuccessResponse($payload, 'NDWI latest loaded.');
	}

	#[AdminRequired]
	public function getNdwiTimeseries(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndwi timeseries', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndwi_timeseries', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$startName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'start');
			$endName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'end');
			$this->ensureDateOrder($query, $startName, $endName);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('ndwi timeseries', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('ndwi timeseries', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndwi timeseries', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndwi timeseries');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndwi timeseries');
		}

		return $this->buildSuccessResponse($payload, 'NDWI timeseries loaded.');
	}

	#[AdminRequired]
	public function refreshNdwi(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndwi refresh', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('ndwi_refresh', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('ndwi refresh', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('ndwi refresh', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndwi refresh', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndwi refresh');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndwi refresh');
		}

		return $this->buildSuccessResponse($payload, 'NDWI refresh queued.');
	}

	#[AdminRequired]
	public function getNdwiRasterPng(string $farmId): Response {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndwi raster', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndwi_raster', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
			if ($dateField !== null && array_key_exists($dateField, $query)) {
				$this->parseIsoDateValue($query[$dateField], $dateField);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('ndwi raster', $operation, $path, $query, $requestId);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
			$this->logProxyResponse('ndwi raster', $operation, $path, $requestId, $binary['statusCode']);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndwi raster', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndwi raster');
		}

		$response = new DataDisplayResponse($binary['body'], Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getNdwiRasterTile(string $farmId): Response {
		$z = $this->request->getParam('z', '');
		$x = $this->request->getParam('x', '');
		$y = $this->request->getParam('y', '');
		return $this->proxyRasterTile($farmId, 'ndwi', $z, $x, $y);
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getNdwiRasterDates(string $farmId): Response {
		return $this->proxyRasterDates($farmId, 'ndwi');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getNdwiGeotiff(string $farmId): Response {
		return $this->proxyGeotiff($farmId, 'ndwi');
	}


	#[AdminRequired]
	public function queueNdwiRaster(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndwi raster queue', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndwi_raster_queue', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$body = null;
			$bodyFields = $operation['bodyFields'] ?? [];
			if ($bodyFields !== []) {
				$body = $this->filterBodyParams($params, $bodyFields, true);
				$dateField = $this->resolveBodyFieldName($bodyFields, 'date');
				if ($dateField !== null && array_key_exists($dateField, $body)) {
					$this->parseIsoDateValue($body[$dateField], $dateField);
				}
			} else {
				$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
				$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('ndwi raster queue', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				$query,
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('ndwi raster queue', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndwi raster queue', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndwi raster queue');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndwi raster queue');
		}

		return $this->buildSuccessResponse($payload, 'NDWI raster queued.');
	}

	#[AdminRequired]
	public function getNdwiFarmState(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndwi farm state', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndwi_farm_state', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('ndwi farm state', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('ndwi farm state', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndwi farm state', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndwi farm state');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndwi farm state');
		}

		return $this->buildSuccessResponse($payload, 'NDWI farm state loaded.');
	}

	#[AdminRequired]
	public function getNdmiFarmState(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndmi farm state', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndmi_farm_state', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('ndmi farm state', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('ndmi farm state', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndmi farm state', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndmi farm state');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndmi farm state');
		}

		return $this->buildSuccessResponse($payload, 'NDMI farm state loaded.');
	}

	#[AdminRequired]
	public function getNdmiLatest(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndmi latest', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndmi_latest', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('ndmi latest', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('ndmi latest', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndmi latest', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndmi latest');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndmi latest');
		}

		return $this->buildSuccessResponse($payload, 'NDMI latest loaded.');
	}

	#[AdminRequired]
	public function getNdmiTimeseries(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndmi timeseries', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndmi_timeseries', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$startName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'start');
			$endName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'end');
			$this->ensureDateOrder($query, $startName, $endName);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('ndmi timeseries', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('ndmi timeseries', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndmi timeseries', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndmi timeseries');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndmi timeseries');
		}

		return $this->buildSuccessResponse($payload, 'NDMI timeseries loaded.');
	}

	#[AdminRequired]
	public function refreshNdmi(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndmi refresh', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('ndmi_refresh', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('ndmi refresh', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('ndmi refresh', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndmi refresh', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndmi refresh');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndmi refresh');
		}

		return $this->buildSuccessResponse($payload, 'NDMI refresh queued.');
	}

	#[AdminRequired]
	public function getNdmiRasterPng(string $farmId): Response {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndmi raster', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndmi_raster', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
			if ($dateField !== null && array_key_exists($dateField, $query)) {
				$this->parseIsoDateValue($query[$dateField], $dateField);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('ndmi raster', $operation, $path, $query, $requestId);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
			$this->logProxyResponse('ndmi raster', $operation, $path, $requestId, $binary['statusCode']);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndmi raster', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndmi raster');
		}

		$response = new DataDisplayResponse($binary['body'], Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getNdmiRasterTile(string $farmId): Response {
		$z = $this->request->getParam('z', '');
		$x = $this->request->getParam('x', '');
		$y = $this->request->getParam('y', '');
		return $this->proxyRasterTile($farmId, 'ndmi', $z, $x, $y);
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getNdmiRasterDates(string $farmId): Response {
		return $this->proxyRasterDates($farmId, 'ndmi');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getNdmiGeotiff(string $farmId): Response {
		return $this->proxyGeotiff($farmId, 'ndmi');
	}


	#[AdminRequired]
	public function queueNdmiRaster(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndmi raster queue', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('ndmi_raster_queue', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$body = null;
			$bodyFields = $operation['bodyFields'] ?? [];
			if ($bodyFields !== []) {
				$body = $this->filterBodyParams($params, $bodyFields, true);
				$dateField = $this->resolveBodyFieldName($bodyFields, 'date');
				if ($dateField !== null && array_key_exists($dateField, $body)) {
					$this->parseIsoDateValue($body[$dateField], $dateField);
				}
			} else {
				$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
				$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('ndmi raster queue', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				$query,
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('ndmi raster queue', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('ndmi raster queue', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'ndmi raster queue');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'ndmi raster queue');
		}

		return $this->buildSuccessResponse($payload, 'NDMI raster queued.');
	}

	// ── RVI endpoints ──────────────────────────────────────────

	#[AdminRequired]
	public function getRviLatest(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('rvi latest', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('rvi_latest', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('rvi latest', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('rvi latest', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('rvi latest', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'rvi latest');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'rvi latest');
		}

		return $this->buildSuccessResponse($payload, 'RVI latest loaded.');
	}

	#[AdminRequired]
	public function getRviTimeseries(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('rvi timeseries', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('rvi_timeseries', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$startName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'start');
			$endName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'end');
			$this->ensureDateOrder($query, $startName, $endName);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('rvi timeseries', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('rvi timeseries', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('rvi timeseries', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'rvi timeseries');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'rvi timeseries');
		}

		return $this->buildSuccessResponse($payload, 'RVI timeseries loaded.');
	}

	#[AdminRequired]
	public function refreshRvi(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('rvi refresh', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('rvi_refresh', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('rvi refresh', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('rvi refresh', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('rvi refresh', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'rvi refresh');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'rvi refresh');
		}

		return $this->buildSuccessResponse($payload, 'RVI refresh queued.');
	}

	#[AdminRequired]
	public function getRviRasterPng(string $farmId): Response {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('rvi raster', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('rvi_raster', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
			if ($dateField !== null && array_key_exists($dateField, $query)) {
				$this->parseIsoDateValue($query[$dateField], $dateField);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('rvi raster', $operation, $path, $query, $requestId);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
			$this->logProxyResponse('rvi raster', $operation, $path, $requestId, $binary['statusCode']);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('rvi raster', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'rvi raster');
		}

		$response = new DataDisplayResponse($binary['body'], Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getRviRasterTile(string $farmId): Response {
		$z = $this->request->getParam('z', '');
		$x = $this->request->getParam('x', '');
		$y = $this->request->getParam('y', '');
		return $this->proxyRasterTile($farmId, 'rvi', $z, $x, $y);
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getRviRasterDates(string $farmId): Response {
		return $this->proxyRasterDates($farmId, 'rvi');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getRviGeotiff(string $farmId): Response {
		return $this->proxyGeotiff($farmId, 'rvi');
	}


	#[AdminRequired]
	public function queueRviRaster(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('rvi raster queue', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('rvi_raster_queue', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$body = null;
			$bodyFields = $operation['bodyFields'] ?? [];
			if ($bodyFields !== []) {
				$body = $this->filterBodyParams($params, $bodyFields, true);
				$dateField = $this->resolveBodyFieldName($bodyFields, 'date');
				if ($dateField !== null && array_key_exists($dateField, $body)) {
					$this->parseIsoDateValue($body[$dateField], $dateField);
				}
			} else {
				$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
				$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('rvi raster queue', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				$query,
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('rvi raster queue', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('rvi raster queue', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'rvi raster queue');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'rvi raster queue');
		}

		return $this->buildSuccessResponse($payload, 'RVI raster queued.');
	}

	#[AdminRequired]
	public function getRviFarmState(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('rvi farm state', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('rvi_farm_state', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('rvi farm state', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('rvi farm state', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('rvi farm state', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'rvi farm state');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'rvi farm state');
		}

		return $this->buildSuccessResponse($payload, 'RVI farm state loaded.');
	}

	// ── S1_SMI endpoints ───────────────────────────────────────

	#[AdminRequired]
	public function getS1SmiLatest(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('s1_smi latest', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('s1_smi_latest', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('s1_smi latest', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('s1_smi latest', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('s1_smi latest', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 's1_smi latest');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 's1_smi latest');
		}

		return $this->buildSuccessResponse($payload, 'S1_SMI latest loaded.');
	}

	#[AdminRequired]
	public function getS1SmiTimeseries(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('s1_smi timeseries', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('s1_smi_timeseries', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$startName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'start');
			$endName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'end');
			$this->ensureDateOrder($query, $startName, $endName);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('s1_smi timeseries', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('s1_smi timeseries', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('s1_smi timeseries', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 's1_smi timeseries');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 's1_smi timeseries');
		}

		return $this->buildSuccessResponse($payload, 'S1_SMI timeseries loaded.');
	}

	#[AdminRequired]
	public function refreshS1Smi(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('s1_smi refresh', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('s1_smi_refresh', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('s1_smi refresh', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('s1_smi refresh', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('s1_smi refresh', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 's1_smi refresh');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 's1_smi refresh');
		}

		return $this->buildSuccessResponse($payload, 'S1_SMI refresh queued.');
	}

	#[AdminRequired]
	public function getS1SmiRasterPng(string $farmId): Response {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('s1_smi raster', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('s1_smi_raster', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
			if ($dateField !== null && array_key_exists($dateField, $query)) {
				$this->parseIsoDateValue($query[$dateField], $dateField);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('s1_smi raster', $operation, $path, $query, $requestId);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
			$this->logProxyResponse('s1_smi raster', $operation, $path, $requestId, $binary['statusCode']);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('s1_smi raster', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 's1_smi raster');
		}

		$response = new DataDisplayResponse($binary['body'], Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getS1SmiRasterTile(string $farmId): Response {
		$z = $this->request->getParam('z', '');
		$x = $this->request->getParam('x', '');
		$y = $this->request->getParam('y', '');
		return $this->proxyRasterTile($farmId, 's1_smi', $z, $x, $y);
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getS1SmiRasterDates(string $farmId): Response {
		return $this->proxyRasterDates($farmId, 's1_smi');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getS1SmiGeotiff(string $farmId): Response {
		return $this->proxyGeotiff($farmId, 's1_smi');
	}


	#[AdminRequired]
	public function queueS1SmiRaster(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('s1_smi raster queue', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('s1_smi_raster_queue', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$body = null;
			$bodyFields = $operation['bodyFields'] ?? [];
			if ($bodyFields !== []) {
				$body = $this->filterBodyParams($params, $bodyFields, true);
				$dateField = $this->resolveBodyFieldName($bodyFields, 'date');
				if ($dateField !== null && array_key_exists($dateField, $body)) {
					$this->parseIsoDateValue($body[$dateField], $dateField);
				}
			} else {
				$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
				$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('s1_smi raster queue', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				$query,
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('s1_smi raster queue', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('s1_smi raster queue', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 's1_smi raster queue');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 's1_smi raster queue');
		}

		return $this->buildSuccessResponse($payload, 'S1_SMI raster queued.');
	}

	#[AdminRequired]
	public function getS1SmiFarmState(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('s1_smi farm state', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('s1_smi_farm_state', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('s1_smi farm state', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('s1_smi farm state', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('s1_smi farm state', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 's1_smi farm state');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 's1_smi farm state');
		}

		return $this->buildSuccessResponse($payload, 'S1_SMI farm state loaded.');
	}


	#[AdminRequired]
	public function getS3LstLatest(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('s3_lst latest', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('s3_lst_latest', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('s3_lst latest', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('s3_lst latest', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('s3_lst latest', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 's3_lst latest');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 's3_lst latest');
		}

		return $this->buildSuccessResponse($payload, 'S3_LST latest loaded.');
	}


	#[AdminRequired]
	public function getS3LstTimeseries(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('s3_lst timeseries', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('s3_lst_timeseries', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('s3_lst timeseries', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('s3_lst timeseries', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('s3_lst timeseries', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 's3_lst timeseries');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 's3_lst timeseries');
		}

		return $this->buildSuccessResponse($payload, 'S3_LST timeseries loaded.');
	}


	#[AdminRequired]
	public function getS3LstRasterPng(string $farmId): Response {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('s3_lst raster', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('s3_lst_raster', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
			if ($dateField !== null && array_key_exists($dateField, $query)) {
				$this->parseIsoDateValue($query[$dateField], $dateField);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('s3_lst raster', $operation, $path, $query, $requestId);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
			$this->logProxyResponse('s3_lst raster', $operation, $path, $requestId, $binary['statusCode']);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('s3_lst raster', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 's3_lst raster');
		}

		$response = new DataDisplayResponse($binary['body'], Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}


	#[AdminRequired]
	public function queueS3LstRaster(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('s3_lst raster queue', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('s3_lst_raster_queue', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = [];
			$body = null;
			$bodyFields = $operation['bodyFields'] ?? [];
			if ($bodyFields !== []) {
				$body = $this->filterBodyParams($params, $bodyFields, true);
				$dateField = $this->resolveBodyFieldName($bodyFields, 'date');
				if ($dateField !== null && array_key_exists($dateField, $body)) {
					$this->parseIsoDateValue($body[$dateField], $dateField);
				}
			} else {
				$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
				$this->requireQueryParams($query, $operation['queryParams'] ?? []);
				$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
				if ($dateField !== null && array_key_exists($dateField, $query)) {
					$this->parseIsoDateValue($query[$dateField], $dateField);
				}
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('s3_lst raster queue', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				$query,
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('s3_lst raster queue', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('s3_lst raster queue', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 's3_lst raster queue');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 's3_lst raster queue');
		}

		return $this->buildSuccessResponse($payload, 'S3_LST raster queued.');
	}


	#[AdminRequired]
	public function refreshS3Lst(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('s3_lst refresh', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('s3_lst_refresh', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('s3_lst refresh', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('s3_lst refresh', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('s3_lst refresh', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 's3_lst refresh');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 's3_lst refresh');
		}

		return $this->buildSuccessResponse($payload, 'S3_LST refresh queued.');
	}


	#[AdminRequired]
	public function getS3LstFarmState(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('s3_lst farm state', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('s3_lst_farm_state', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('s3_lst farm state', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('s3_lst farm state', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('s3_lst farm state', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 's3_lst farm state');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 's3_lst farm state');
		}

		return $this->buildSuccessResponse($payload, 'S3_LST farm state loaded.');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getS3LstRasterTile(string $farmId): Response {
		$z = $this->request->getParam('z', '');
		$x = $this->request->getParam('x', '');
		$y = $this->request->getParam('y', '');
		return $this->proxyRasterTile($farmId, 's3_lst', $z, $x, $y);
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getS3LstRasterDates(string $farmId): Response {
		return $this->proxyRasterDates($farmId, 's3_lst');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getS3LstGeotiff(string $farmId): Response {
		return $this->proxyGeotiff($farmId, 's3_lst');
	}


	#[AdminRequired]
	public function getLandsatLstLatest(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('landsat_lst latest', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('landsat_lst_latest', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('landsat_lst latest', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('landsat_lst latest', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('landsat_lst latest', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'landsat_lst latest');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'landsat_lst latest');
		}

		return $this->buildSuccessResponse($payload, 'LANDSAT_LST latest loaded.');
	}


	#[AdminRequired]
	public function getLandsatLstTimeseries(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('landsat_lst timeseries', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('landsat_lst_timeseries', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('landsat_lst timeseries', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('landsat_lst timeseries', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('landsat_lst timeseries', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'landsat_lst timeseries');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'landsat_lst timeseries');
		}

		return $this->buildSuccessResponse($payload, 'LANDSAT_LST timeseries loaded.');
	}


	#[AdminRequired]
	public function getLandsatLstRasterPng(string $farmId): Response {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('landsat_lst raster', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('landsat_lst_raster', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
			if ($dateField !== null && array_key_exists($dateField, $query)) {
				$this->parseIsoDateValue($query[$dateField], $dateField);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('landsat_lst raster', $operation, $path, $query, $requestId);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
			$this->logProxyResponse('landsat_lst raster', $operation, $path, $requestId, $binary['statusCode']);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('landsat_lst raster', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'landsat_lst raster');
		}

		$response = new DataDisplayResponse($binary['body'], Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}


	#[AdminRequired]
	public function queueLandsatLstRaster(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('landsat_lst raster queue', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('landsat_lst_raster_queue', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = [];
			$body = null;
			$bodyFields = $operation['bodyFields'] ?? [];
			if ($bodyFields !== []) {
				$body = $this->filterBodyParams($params, $bodyFields, true);
				$dateField = $this->resolveBodyFieldName($bodyFields, 'date');
				if ($dateField !== null && array_key_exists($dateField, $body)) {
					$this->parseIsoDateValue($body[$dateField], $dateField);
				}
			} else {
				$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
				$this->requireQueryParams($query, $operation['queryParams'] ?? []);
				$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
				if ($dateField !== null && array_key_exists($dateField, $query)) {
					$this->parseIsoDateValue($query[$dateField], $dateField);
				}
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('landsat_lst raster queue', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				$query,
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('landsat_lst raster queue', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('landsat_lst raster queue', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'landsat_lst raster queue');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'landsat_lst raster queue');
		}

		return $this->buildSuccessResponse($payload, 'LANDSAT_LST raster queued.');
	}


	#[AdminRequired]
	public function refreshLandsatLst(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('landsat_lst refresh', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('landsat_lst_refresh', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('landsat_lst refresh', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('landsat_lst refresh', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('landsat_lst refresh', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'landsat_lst refresh');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'landsat_lst refresh');
		}

		return $this->buildSuccessResponse($payload, 'LANDSAT_LST refresh queued.');
	}


	#[AdminRequired]
	public function getLandsatLstFarmState(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('landsat_lst farm state', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('landsat_lst_farm_state', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('landsat_lst farm state', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('landsat_lst farm state', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('landsat_lst farm state', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'landsat_lst farm state');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'landsat_lst farm state');
		}

		return $this->buildSuccessResponse($payload, 'LANDSAT_LST farm state loaded.');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getLandsatLstRasterTile(string $farmId): Response {
		$z = $this->request->getParam('z', '');
		$x = $this->request->getParam('x', '');
		$y = $this->request->getParam('y', '');
		return $this->proxyRasterTile($farmId, 'landsat_lst', $z, $x, $y);
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getLandsatLstRasterDates(string $farmId): Response {
		return $this->proxyRasterDates($farmId, 'landsat_lst');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getLandsatLstGeotiff(string $farmId): Response {
		return $this->proxyGeotiff($farmId, 'landsat_lst');
	}


	#[AdminRequired]
	public function getIronOxideLatest(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('iron_oxide latest', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('iron_oxide_latest', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('iron_oxide latest', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('iron_oxide latest', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('iron_oxide latest', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'iron_oxide latest');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'iron_oxide latest');
		}

		return $this->buildSuccessResponse($payload, 'IRON_OXIDE latest loaded.');
	}


	#[AdminRequired]
	public function getIronOxideTimeseries(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('iron_oxide timeseries', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('iron_oxide_timeseries', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('iron_oxide timeseries', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('iron_oxide timeseries', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('iron_oxide timeseries', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'iron_oxide timeseries');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'iron_oxide timeseries');
		}

		return $this->buildSuccessResponse($payload, 'IRON_OXIDE timeseries loaded.');
	}


	#[AdminRequired]
	public function getIronOxideRasterPng(string $farmId): Response {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('iron_oxide raster', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('iron_oxide_raster', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
			if ($dateField !== null && array_key_exists($dateField, $query)) {
				$this->parseIsoDateValue($query[$dateField], $dateField);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('iron_oxide raster', $operation, $path, $query, $requestId);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
			$this->logProxyResponse('iron_oxide raster', $operation, $path, $requestId, $binary['statusCode']);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('iron_oxide raster', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'iron_oxide raster');
		}

		$response = new DataDisplayResponse($binary['body'], Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}


	#[AdminRequired]
	public function queueIronOxideRaster(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('iron_oxide raster queue', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('iron_oxide_raster_queue', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = [];
			$body = null;
			$bodyFields = $operation['bodyFields'] ?? [];
			if ($bodyFields !== []) {
				$body = $this->filterBodyParams($params, $bodyFields, true);
				$dateField = $this->resolveBodyFieldName($bodyFields, 'date');
				if ($dateField !== null && array_key_exists($dateField, $body)) {
					$this->parseIsoDateValue($body[$dateField], $dateField);
				}
			} else {
				$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
				$this->requireQueryParams($query, $operation['queryParams'] ?? []);
				$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
				if ($dateField !== null && array_key_exists($dateField, $query)) {
					$this->parseIsoDateValue($query[$dateField], $dateField);
				}
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('iron_oxide raster queue', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				$query,
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('iron_oxide raster queue', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('iron_oxide raster queue', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'iron_oxide raster queue');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'iron_oxide raster queue');
		}

		return $this->buildSuccessResponse($payload, 'IRON_OXIDE raster queued.');
	}


	#[AdminRequired]
	public function refreshIronOxide(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('iron_oxide refresh', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('iron_oxide_refresh', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('iron_oxide refresh', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('iron_oxide refresh', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('iron_oxide refresh', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'iron_oxide refresh');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'iron_oxide refresh');
		}

		return $this->buildSuccessResponse($payload, 'IRON_OXIDE refresh queued.');
	}


	#[AdminRequired]
	public function getIronOxideFarmState(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('iron_oxide farm state', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('iron_oxide_farm_state', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('iron_oxide farm state', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('iron_oxide farm state', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('iron_oxide farm state', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'iron_oxide farm state');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'iron_oxide farm state');
		}

		return $this->buildSuccessResponse($payload, 'IRON_OXIDE farm state loaded.');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getIronOxideRasterTile(string $farmId): Response {
		$z = $this->request->getParam('z', '');
		$x = $this->request->getParam('x', '');
		$y = $this->request->getParam('y', '');
		return $this->proxyRasterTile($farmId, 'iron_oxide', $z, $x, $y);
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getIronOxideRasterDates(string $farmId): Response {
		return $this->proxyRasterDates($farmId, 'iron_oxide');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getIronOxideGeotiff(string $farmId): Response {
		return $this->proxyGeotiff($farmId, 'iron_oxide');
	}


	#[AdminRequired]
	public function getEviLatest(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('evi latest', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('evi_latest', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('evi latest', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('evi latest', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('evi latest', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'evi latest');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'evi latest');
		}

		return $this->buildSuccessResponse($payload, 'EVI latest loaded.');
	}


	#[AdminRequired]
	public function getEviTimeseries(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('evi timeseries', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('evi_timeseries', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$startName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'start');
			$endName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'end');
			$this->ensureDateOrder($query, $startName, $endName);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('evi timeseries', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('evi timeseries', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('evi timeseries', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'evi timeseries');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'evi timeseries');
		}

		return $this->buildSuccessResponse($payload, 'EVI timeseries loaded.');
	}


	#[AdminRequired]
	public function getEviRasterPng(string $farmId): Response {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('evi raster', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('evi_raster', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
			if ($dateField !== null && array_key_exists($dateField, $query)) {
				$this->parseIsoDateValue($query[$dateField], $dateField);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('evi raster', $operation, $path, $query, $requestId);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
			$this->logProxyResponse('evi raster', $operation, $path, $requestId, $binary['statusCode']);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('evi raster', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'evi raster');
		}

		$response = new DataDisplayResponse($binary['body'], Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}


	#[AdminRequired]
	public function queueEviRaster(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('evi raster queue', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('evi_raster_queue', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = [];
			$body = null;
			$bodyFields = $operation['bodyFields'] ?? [];
			if ($bodyFields !== []) {
				$body = $this->filterBodyParams($params, $bodyFields, true);
				$dateField = $this->resolveBodyFieldName($bodyFields, 'date');
				if ($dateField !== null && array_key_exists($dateField, $body)) {
					$this->parseIsoDateValue($body[$dateField], $dateField);
				}
			} else {
				$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
				$this->requireQueryParams($query, $operation['queryParams'] ?? []);
				$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
				if ($dateField !== null && array_key_exists($dateField, $query)) {
					$this->parseIsoDateValue($query[$dateField], $dateField);
				}
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('evi raster queue', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				$query,
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('evi raster queue', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('evi raster queue', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'evi raster queue');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'evi raster queue');
		}

		return $this->buildSuccessResponse($payload, 'EVI raster queued.');
	}


	#[AdminRequired]
	public function refreshEvi(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('evi refresh', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('evi_refresh', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('evi refresh', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('evi refresh', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('evi refresh', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'evi refresh');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'evi refresh');
		}

		return $this->buildSuccessResponse($payload, 'EVI refresh queued.');
	}


	#[AdminRequired]
	public function getEviFarmState(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('evi farm state', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('evi_farm_state', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('evi farm state', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('evi farm state', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('evi farm state', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'evi farm state');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'evi farm state');
		}

		return $this->buildSuccessResponse($payload, 'EVI farm state loaded.');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getEviRasterTile(string $farmId): Response {
		$z = $this->request->getParam('z', '');
		$x = $this->request->getParam('x', '');
		$y = $this->request->getParam('y', '');
		return $this->proxyRasterTile($farmId, 'evi', $z, $x, $y);
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getEviRasterDates(string $farmId): Response {
		return $this->proxyRasterDates($farmId, 'evi');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getEviGeotiff(string $farmId): Response {
		return $this->proxyGeotiff($farmId, 'evi');
	}


	#[AdminRequired]
	public function getLRviLatest(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('l_rvi latest', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('l_rvi_latest', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('l_rvi latest', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('l_rvi latest', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('l_rvi latest', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'l_rvi latest');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'l_rvi latest');
		}

		return $this->buildSuccessResponse($payload, 'L_RVI latest loaded.');
	}


	#[AdminRequired]
	public function getLRviTimeseries(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('l_rvi timeseries', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('l_rvi_timeseries', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$startName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'start');
			$endName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'end');
			$this->ensureDateOrder($query, $startName, $endName);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('l_rvi timeseries', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('l_rvi timeseries', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('l_rvi timeseries', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'l_rvi timeseries');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'l_rvi timeseries');
		}

		return $this->buildSuccessResponse($payload, 'L_RVI timeseries loaded.');
	}


	#[AdminRequired]
	public function getLRviRasterPng(string $farmId): Response {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('l_rvi raster', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('l_rvi_raster', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
			if ($dateField !== null && array_key_exists($dateField, $query)) {
				$this->parseIsoDateValue($query[$dateField], $dateField);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('l_rvi raster', $operation, $path, $query, $requestId);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
			$this->logProxyResponse('l_rvi raster', $operation, $path, $requestId, $binary['statusCode']);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('l_rvi raster', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'l_rvi raster');
		}

		$response = new DataDisplayResponse($binary['body'], Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}


	#[AdminRequired]
	public function queueLRviRaster(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('l_rvi raster queue', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('l_rvi_raster_queue', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = [];
			$body = null;
			$bodyFields = $operation['bodyFields'] ?? [];
			if ($bodyFields !== []) {
				$body = $this->filterBodyParams($params, $bodyFields, true);
				$dateField = $this->resolveBodyFieldName($bodyFields, 'date');
				if ($dateField !== null && array_key_exists($dateField, $body)) {
					$this->parseIsoDateValue($body[$dateField], $dateField);
				}
			} else {
				$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
				$this->requireQueryParams($query, $operation['queryParams'] ?? []);
				$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
				if ($dateField !== null && array_key_exists($dateField, $query)) {
					$this->parseIsoDateValue($query[$dateField], $dateField);
				}
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('l_rvi raster queue', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				$query,
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('l_rvi raster queue', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('l_rvi raster queue', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'l_rvi raster queue');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'l_rvi raster queue');
		}

		return $this->buildSuccessResponse($payload, 'L_RVI raster queued.');
	}


	#[AdminRequired]
	public function refreshLRvi(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('l_rvi refresh', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('l_rvi_refresh', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('l_rvi refresh', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('l_rvi refresh', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('l_rvi refresh', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'l_rvi refresh');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'l_rvi refresh');
		}

		return $this->buildSuccessResponse($payload, 'L_RVI refresh queued.');
	}


	#[AdminRequired]
	public function getLRviFarmState(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('l_rvi farm state', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('l_rvi_farm_state', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('l_rvi farm state', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('l_rvi farm state', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('l_rvi farm state', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'l_rvi farm state');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'l_rvi farm state');
		}

		return $this->buildSuccessResponse($payload, 'L_RVI farm state loaded.');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getLRviRasterTile(string $farmId): Response {
		$z = $this->request->getParam('z', '');
		$x = $this->request->getParam('x', '');
		$y = $this->request->getParam('y', '');
		return $this->proxyRasterTile($farmId, 'l_rvi', $z, $x, $y);
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getLRviRasterDates(string $farmId): Response {
		return $this->proxyRasterDates($farmId, 'l_rvi');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getLRviGeotiff(string $farmId): Response {
		return $this->proxyGeotiff($farmId, 'l_rvi');
	}


	#[AdminRequired]
	public function getNisarSmiLatest(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('nisar_smi latest', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('nisar_smi_latest', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('nisar_smi latest', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('nisar_smi latest', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('nisar_smi latest', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'nisar_smi latest');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'nisar_smi latest');
		}

		return $this->buildSuccessResponse($payload, 'NISAR_SMI latest loaded.');
	}


	#[AdminRequired]
	public function getNisarSmiTimeseries(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('nisar_smi timeseries', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('nisar_smi_timeseries', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$startName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'start');
			$endName = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'end');
			$this->ensureDateOrder($query, $startName, $endName);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('nisar_smi timeseries', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('nisar_smi timeseries', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('nisar_smi timeseries', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'nisar_smi timeseries');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'nisar_smi timeseries');
		}

		return $this->buildSuccessResponse($payload, 'NISAR_SMI timeseries loaded.');
	}


	#[AdminRequired]
	public function getNisarSmiRasterPng(string $farmId): Response {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('nisar_smi raster', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('nisar_smi_raster', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
			if ($dateField !== null && array_key_exists($dateField, $query)) {
				$this->parseIsoDateValue($query[$dateField], $dateField);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('nisar_smi raster', $operation, $path, $query, $requestId);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
			$this->logProxyResponse('nisar_smi raster', $operation, $path, $requestId, $binary['statusCode']);
		} catch (WeatherApiException $exception) {
			$this->logProxyError('nisar_smi raster', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'nisar_smi raster');
		}

		$response = new DataDisplayResponse($binary['body'], Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}


	#[AdminRequired]
	public function queueNisarSmiRaster(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('nisar_smi raster queue', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('nisar_smi_raster_queue', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = [];
			$body = null;
			$bodyFields = $operation['bodyFields'] ?? [];
			if ($bodyFields !== []) {
				$body = $this->filterBodyParams($params, $bodyFields, true);
				$dateField = $this->resolveBodyFieldName($bodyFields, 'date');
				if ($dateField !== null && array_key_exists($dateField, $body)) {
					$this->parseIsoDateValue($body[$dateField], $dateField);
				}
			} else {
				$query = $this->filterQueryParams($params, $operation['queryParams'] ?? []);
				$this->requireQueryParams($query, $operation['queryParams'] ?? []);
				$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
				if ($dateField !== null && array_key_exists($dateField, $query)) {
					$this->parseIsoDateValue($query[$dateField], $dateField);
				}
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('nisar_smi raster queue', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				$query,
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('nisar_smi raster queue', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('nisar_smi raster queue', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'nisar_smi raster queue');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'nisar_smi raster queue');
		}

		return $this->buildSuccessResponse($payload, 'NISAR_SMI raster queued.');
	}


	#[AdminRequired]
	public function refreshNisarSmi(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('nisar_smi refresh', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('nisar_smi_refresh', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('nisar_smi refresh', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('nisar_smi refresh', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('nisar_smi refresh', $operation, $path, [], $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'nisar_smi refresh');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'nisar_smi refresh');
		}

		return $this->buildSuccessResponse($payload, 'NISAR_SMI refresh queued.');
	}


	#[AdminRequired]
	public function getNisarSmiFarmState(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('nisar_smi farm state', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('nisar_smi_farm_state', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('nisar_smi farm state', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('nisar_smi farm state', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('nisar_smi farm state', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'nisar_smi farm state');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'nisar_smi farm state');
		}

		return $this->buildSuccessResponse($payload, 'NISAR_SMI farm state loaded.');
	}


	#[AdminRequired]
	public function getFarmDecision(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('farm decision', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$operation = [];
		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('farm_decision', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest('farm decision', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('farm decision', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			$this->logProxyError('farm decision', $operation, $path, $query, $requestId, $exception);
			return $this->handleWeatherApiException($exception, $requestId, 'farm decision');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'farm decision');
		}

		return $this->buildSuccessResponse($payload, 'Decision loaded.');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getNisarSmiRasterTile(string $farmId): Response {
		$z = $this->request->getParam('z', '');
		$x = $this->request->getParam('x', '');
		$y = $this->request->getParam('y', '');
		return $this->proxyRasterTile($farmId, 'nisar_smi', $z, $x, $y);
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getNisarSmiRasterDates(string $farmId): Response {
		return $this->proxyRasterDates($farmId, 'nisar_smi');
	}


	#[NoCSRFRequired]
	#[AdminRequired]
	public function getNisarSmiGeotiff(string $farmId): Response {
		return $this->proxyGeotiff($farmId, 'nisar_smi');
	}


	#[AdminRequired]
	public function listFarmObservations(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('list farm observations', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('observations_list', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$queryDefs = $operation['queryParams'] ?? [];
			if (!is_array($queryDefs)) {
				$queryDefs = [];
			}
			$query = $this->buildListQueryParams($params, $queryDefs);
			$this->logProxyRequest('list farm observations', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('list farm observations', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'list farm observations');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'list farm observations');
		}

		return $this->buildSuccessResponse($payload, 'Farm observations loaded.');
	}

	#[AdminRequired]
	public function createFarmObservation(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('create farm observation', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('observations_create', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], true);
			$this->logProxyRequest('create farm observation', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body,
				$requestId,
			);
			$this->logProxyResponse('create farm observation', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'create farm observation');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'create farm observation');
		}

		return $this->buildSuccessResponse($payload, 'Farm observation created.');
	}

	#[AdminRequired]
	public function getFarmObservation(string $farmId, string $observationId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get farm observation', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('observations_retrieve', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams(
				$pathTemplate,
				['farm_id' => $farmId, 'observation_id' => $observationId],
			);
			$this->logProxyRequest('get farm observation', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('get farm observation', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get farm observation');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get farm observation');
		}

		return $this->buildSuccessResponse($payload, 'Farm observation loaded.');
	}

	#[AdminRequired]
	public function patchFarmObservation(string $farmId, string $observationId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('patch farm observation', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('observations_update', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams(
				$pathTemplate,
				['farm_id' => $farmId, 'observation_id' => $observationId],
			);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('patch farm observation', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'PATCH'),
				$path,
				[],
				$body,
				$requestId,
			);
			$this->logProxyResponse('patch farm observation', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'patch farm observation');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'patch farm observation');
		}

		return $this->buildSuccessResponse($payload, 'Farm observation updated.');
	}

	#[AdminRequired]
	public function deleteFarmObservation(string $farmId, string $observationId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('delete farm observation', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('observations_delete', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams(
				$pathTemplate,
				['farm_id' => $farmId, 'observation_id' => $observationId],
			);
			$this->logProxyRequest('delete farm observation', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'DELETE'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('delete farm observation', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'delete farm observation');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'delete farm observation');
		}

		return $this->buildSuccessResponse($payload, 'Farm observation deleted.');
	}

	#[AdminRequired]
	public function listFarmActivities(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('list farm activities', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('activities_list', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$queryDefs = $operation['queryParams'] ?? [];
			if (!is_array($queryDefs)) {
				$queryDefs = [];
			}
			$query = $this->buildListQueryParams($params, $queryDefs);
			$this->logProxyRequest('list farm activities', $operation, $path, $query, $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse('list farm activities', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'list farm activities');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'list farm activities');
		}

		return $this->buildSuccessResponse($payload, 'Farm activities loaded.');
	}

	#[AdminRequired]
	public function createFarmActivity(string $farmId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('create farm activity', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('activities_create', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['farm_id' => $farmId]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], true);
			$this->logProxyRequest('create farm activity', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body,
				$requestId,
			);
			$this->logProxyResponse('create farm activity', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'create farm activity');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'create farm activity');
		}

		return $this->buildSuccessResponse($payload, 'Farm activity created.');
	}

	#[AdminRequired]
	public function getFarmActivity(string $farmId, string $activityId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get farm activity', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('activities_retrieve', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams(
				$pathTemplate,
				['farm_id' => $farmId, 'activity_id' => $activityId],
			);
			$this->logProxyRequest('get farm activity', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('get farm activity', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get farm activity');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get farm activity');
		}

		return $this->buildSuccessResponse($payload, 'Farm activity loaded.');
	}

	#[AdminRequired]
	public function patchFarmActivity(string $farmId, string $activityId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('patch farm activity', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('activities_update', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams(
				$pathTemplate,
				['farm_id' => $farmId, 'activity_id' => $activityId],
			);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('patch farm activity', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'PATCH'),
				$path,
				[],
				$body,
				$requestId,
			);
			$this->logProxyResponse('patch farm activity', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'patch farm activity');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'patch farm activity');
		}

		return $this->buildSuccessResponse($payload, 'Farm activity updated.');
	}

	#[AdminRequired]
	public function deleteFarmActivity(string $farmId, string $activityId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('delete farm activity', $requestId);

		try {
			$operation = $this->schemaService->getFarmOperation('activities_delete', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams(
				$pathTemplate,
				['farm_id' => $farmId, 'activity_id' => $activityId],
			);
			$this->logProxyRequest('delete farm activity', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'DELETE'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('delete farm activity', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'delete farm activity');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'delete farm activity');
		}

		return $this->buildSuccessResponse($payload, 'Farm activity deleted.');
	}

	// ── NDVI utility endpoints (admin, no farmId) ──────────────

	#[AdminRequired]
	public function getNdviJobStatus(int $jobId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndvi job status', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_job_status', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, ['id' => (string)$jobId]);
			$this->logProxyRequest('ndvi job status', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('ndvi job status', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'ndvi job status');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'ndvi job status');
		}
		return $this->buildSuccessResponse($payload);
	}

	#[AdminRequired]
	public function ndviIngest(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndvi ingest', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_ingest', $requestId);
			$path = (string)($operation['path'] ?? '');
			$params = $this->stripPathParams($this->request->getParams(), $path);
			$body = $this->filterBodyParams($params, $operation['bodyFields'] ?? [], false);
			$this->logProxyRequest('ndvi ingest', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				$body === [] ? null : $body,
				$requestId,
			);
			$this->logProxyResponse('ndvi ingest', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'ndvi ingest');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'ndvi ingest');
		}
		return $this->buildSuccessResponse($payload);
	}

	#[AdminRequired]
	public function resetNdviCircuitBreaker(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndvi circuit breaker reset', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_circuit_breaker_reset', $requestId);
			$path = (string)($operation['path'] ?? '');
			$this->logProxyRequest('ndvi circuit breaker reset', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'POST'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('ndvi circuit breaker reset', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'ndvi circuit breaker reset');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'ndvi circuit breaker reset');
		}
		return $this->buildSuccessResponse($payload);
	}

	#[AdminRequired]
	public function getNdviUpstreamHealth(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('ndvi upstream health', $requestId);
		$operation = [];
		$path = '';

		try {
			$operation = $this->schemaService->getFarmOperation('ndvi_upstream_health', $requestId);
			$path = (string)($operation['path'] ?? '');
			$this->logProxyRequest('ndvi upstream health', $operation, $path, [], $requestId);
			$result = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				[],
				null,
				$requestId,
			);
			$this->logProxyResponse('ndvi upstream health', $operation, $path, $requestId, $result['statusCode']);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'ndvi upstream health');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'ndvi upstream health');
		}
		return $this->buildSuccessResponse($payload);
	}


	private function logEndpointEntry(string $action, string $requestId): void {
		$path = $this->request->getPathInfo();
		if ($path === '') {
			$path = $this->request->getRequestUri();
		}

		$this->logger->debug(
			'Weather API admin endpoint hit',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'method' => $this->request->getMethod(),
				'path' => $path,
			]),
		);
	}

	private function resolveIdempotencyKey(): ?string {
		$header = trim($this->request->getHeader('Idempotency-Key'));
		return $header === '' ? null : $header;
	}

	private function resolveRequestId(): string {
		$header = $this->request->getHeader('X-Request-Id');
		if ($header !== '') {
			return $header;
		}

		$bytes = random_bytes(16);
		$bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
		$bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
	}

	/**
	 * @param array<string, mixed> $params
	 * @param list<array{name: string, type: string, format: string|null, required: bool}> $definitions
	 * @return array<string, mixed>
	 */
	private function filterQueryParams(array $params, array $definitions): array {
		if ($definitions === []) {
			$unknown = $this->collectUnknownParams($params, []);
			if ($unknown !== []) {
				throw new WeatherApiException(
					'invalid_argument',
					'Unknown query parameters: ' . implode(', ', $unknown),
				);
			}
			return [];
		}

		$allowed = [];
		foreach ($definitions as $definition) {
			if (isset($definition['name']) && is_string($definition['name'])) {
				$allowed[$definition['name']] = true;
			}
		}

		$unknown = $this->collectUnknownParams($params, array_keys($allowed));
		if ($unknown !== []) {
			throw new WeatherApiException(
				'invalid_argument',
				'Unknown query parameters: ' . implode(', ', $unknown),
			);
		}

		$filtered = [];
		foreach ($params as $key => $value) {
			if (!$this->isParamAllowed($key, $allowed)) {
				continue;
			}
			if ($value === '' || $value === null) {
				continue;
			}
			$filtered[$key] = $value;
		}

		return $filtered;
	}

	/**
	 * @param array<string, mixed> $params
	 * @param list<array{name: string, type: string, format: string|null, required: bool}> $definitions
	 * @return array<string, mixed>
	 */
	private function buildListQueryParams(array $params, array $definitions): array {
		if ($definitions === []) {
			return $this->filterPassthroughQueryParams($params);
		}

		return $this->filterQueryParams($params, $definitions);
	}

	/**
	 * @param array<string, mixed> $params
	 * @return array<string, mixed>
	 */
	private function filterPassthroughQueryParams(array $params): array {
		$filtered = [];
		foreach ($params as $key => $value) {
			if (!is_string($key)) {
				continue;
			}
			if ($this->isReservedParam($key)) {
				continue;
			}
			if (is_array($value)) {
				$values = array_values(array_filter(
					$value,
					static fn ($entry) => $entry !== '' && $entry !== null,
				));
				if ($values === []) {
					continue;
				}
				$filtered[$key] = $values;
				continue;
			}
			if ($value === '' || $value === null) {
				continue;
			}
			$filtered[$key] = $value;
		}

		return $filtered;
	}

	/**
	 * @param array<string, mixed> $query
	 * @param list<array{name: string, type: string, format: string|null, required: bool}> $definitions
	 */
	private function requireQueryParams(array $query, array $definitions): void {
		if ($definitions === []) {
			return;
		}

		foreach ($definitions as $definition) {
			if (($definition['required'] ?? false) !== true) {
				continue;
			}
			$name = $definition['name'] ?? null;
			if (!is_string($name) || $name === '') {
				continue;
			}
			if (!array_key_exists($name, $query)) {
				throw new WeatherApiException('invalid_argument', 'Missing required query parameter: ' . $name);
			}
			$value = $query[$name];
			if ($value === null || (is_string($value) && trim($value) === '')) {
				throw new WeatherApiException('invalid_argument', 'Missing required query parameter: ' . $name);
			}
		}
	}

	/**
	 * @param list<array{name: string, type: string, format: string|null, required: bool}> $definitions
	 */
	private function resolveQueryParamName(array $definitions, string $desired): ?string {
		foreach ($definitions as $definition) {
			$name = $definition['name'] ?? null;
			if (is_string($name) && $name === $desired) {
				return $name;
			}
		}

		foreach ($definitions as $definition) {
			$name = $definition['name'] ?? null;
			if (is_string($name) && str_contains($name, $desired)) {
				return $name;
			}
		}

		return null;
	}

	/**
	 * @param array<string, array<string, mixed>> $definitions
	 */
	private function resolveBodyFieldName(array $definitions, string $desired): ?string {
		if (isset($definitions[$desired])) {
			return $desired;
		}

		foreach (array_keys($definitions) as $name) {
			if (is_string($name) && str_contains($name, $desired)) {
				return $name;
			}
		}

		return null;
	}

	/**
	 * @param array<string, mixed> $query
	 */
	private function ensureDateOrder(array $query, ?string $startKey, ?string $endKey): void {
		if ($startKey === null || $endKey === null) {
			return;
		}
		if (!array_key_exists($startKey, $query) || !array_key_exists($endKey, $query)) {
			return;
		}

		$startValue = $query[$startKey];
		$endValue = $query[$endKey];
		$startDate = $this->parseIsoDateValue($startValue, $startKey);
		$endDate = $this->parseIsoDateValue($endValue, $endKey);

		if ($startDate > $endDate) {
			throw new WeatherApiException('invalid_argument', 'Start date must be on or before end date.');
		}
	}

	private function parseIsoDateValue(mixed $value, string $field): \DateTimeImmutable {
		if (!is_string($value)) {
			throw new WeatherApiException('invalid_argument', 'Invalid date for ' . $field . '.');
		}
		$trimmed = trim($value);
		if ($trimmed === '') {
			throw new WeatherApiException('invalid_argument', 'Invalid date for ' . $field . '.');
		}

		$date = \DateTimeImmutable::createFromFormat('Y-m-d', $trimmed);
		$errors = \DateTimeImmutable::getLastErrors();
		if ($date === false) {
			throw new WeatherApiException('invalid_argument', 'Invalid date for ' . $field . '.');
		}
		if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
			throw new WeatherApiException('invalid_argument', 'Invalid date for ' . $field . '.');
		}

		if ($date->format('Y-m-d') !== $trimmed) {
			throw new WeatherApiException('invalid_argument', 'Invalid date for ' . $field . '.');
		}

		return $date;
	}

	/**
	 * @param array<string, mixed> $params
	 * @param array<string, array<string, mixed>> $definitions
	 * @return array<string, mixed>
	 */
	private function filterBodyParams(array $params, array $definitions, bool $requireAll): array {
		if ($definitions === []) {
			$unknown = $this->collectUnknownParams($params, []);
			if ($unknown !== []) {
				throw new WeatherApiException(
					'invalid_argument',
					'Unknown fields: ' . implode(', ', $unknown),
				);
			}
			return [];
		}

		$allowed = [];
		$required = [];
		$ignored = [];

		foreach ($definitions as $name => $definition) {
			if (!is_string($name)) {
				continue;
			}
			if (($definition['readOnly'] ?? false) === true) {
				$ignored[] = $name;
				continue;
			}
			$allowed[$name] = true;
			if (($definition['required'] ?? false) === true) {
				$required[] = $name;
			}
		}

		$unknown = $this->collectUnknownParams($params, array_keys($allowed), $ignored);
		if ($unknown !== []) {
			throw new WeatherApiException(
				'invalid_argument',
				'Unknown fields: ' . implode(', ', $unknown),
			);
		}

		$filtered = [];
		foreach ($params as $key => $value) {
			if (!$this->isParamAllowed($key, $allowed)) {
				continue;
			}
			$filtered[$key] = $value;
		}

		if ($requireAll) {
			foreach ($required as $field) {
				if (!array_key_exists($field, $filtered)) {
					throw new WeatherApiException('invalid_argument', 'Missing required field: ' . $field);
				}
				$value = $filtered[$field];
				if (is_string($value) && trim($value) === '') {
					throw new WeatherApiException('invalid_argument', 'Missing required field: ' . $field);
				}
			}
		}

		return $filtered;
	}

	/**
	 * @param array<string, mixed> $params
	 * @param list<string> $allowed
	 * @param list<string> $ignored
	 * @return list<string>
	 */
	private function collectUnknownParams(array $params, array $allowed, array $ignored = []): array {
		$allowedLookup = array_fill_keys($allowed, true);
		$ignoredLookup = array_fill_keys($ignored, true);
		$unknown = [];
		foreach ($params as $key => $_value) {
			if (!is_string($key)) {
				continue;
			}
			if ($this->isReservedParam($key)) {
				continue;
			}
			if (isset($ignoredLookup[$key])) {
				continue;
			}
			if (!isset($allowedLookup[$key])) {
				$unknown[] = $key;
			}
		}

		return $unknown;
	}

	/**
	 * @param array<string, bool> $allowed
	 */
	private function isParamAllowed(string $name, array $allowed): bool {
		return !$this->isReservedParam($name) && isset($allowed[$name]);
	}

	private function isReservedParam(string $name): bool {
		return in_array(strtolower($name), self::RESERVED_PARAMS, true);
	}

	private function applyPathParams(string $path, array $params): string {
		$names = $this->extractPathParamNames($path);
		if ($names === []) {
			return $path;
		}

		foreach ($names as $name) {
			if (!array_key_exists($name, $params)) {
				throw new WeatherApiException('invalid_argument', 'Missing path parameter: ' . $name);
			}
			$value = (string)$params[$name];
			if ($value === '') {
				throw new WeatherApiException('invalid_argument', 'Missing path parameter: ' . $name);
			}
			$path = str_replace('{' . $name . '}', rawurlencode($value), $path);
		}

		return $path;
	}

	/**
	 * @param array<string, mixed> $params
	 * @return array<string, mixed>
	 */
	private function stripPathParams(array $params, string $pathTemplate): array {
		$names = $this->extractPathParamNames($pathTemplate);
		if ($names === []) {
			return $params;
		}

		foreach ($names as $name) {
			unset($params[$name]);
			$camel = $this->snakeToCamel($name);
			if ($camel !== $name) {
				unset($params[$camel]);
			}
		}

		return $params;
	}

	/**
	 * @return list<string>
	 */
	private function extractPathParamNames(string $path): array {
		preg_match_all('/{([^}]+)}/', $path, $matches);
		if (!isset($matches[1])) {
			return [];
		}

		return array_map('strval', $matches[1]);
	}

	private function snakeToCamel(string $value): string {
		if (!str_contains($value, '_')) {
			return $value;
		}

		$parts = array_filter(explode('_', $value), static fn ($part) => $part !== '');
		if ($parts === []) {
			return $value;
		}

		$first = array_shift($parts);
		$parts = array_map(static fn ($part) => ucfirst(strtolower($part)), $parts);

		return $first . implode('', $parts);
	}

	/**
	 * Proxy a raster tile request to the Django backend.
	 */
	private function proxyRasterTile(
		string $farmId,
		string $index,
		string $z,
		string $x,
		string $y,
	): Response {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry($index . ' raster tile', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('raster_tiles', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, [
				'farm_id' => $farmId,
				'index' => $index,
				'z' => $z,
				'x' => $x,
				'y' => $y,
			]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
			if ($dateField !== null && array_key_exists($dateField, $query)) {
				$this->parseIsoDateValue($query[$dateField], $dateField);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest($index . ' raster tile', $operation, $path, $query, $requestId);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
			$this->logProxyResponse($index . ' raster tile', $operation, $path, $requestId, $binary['statusCode']);
		} catch (WeatherApiException $exception) {
			$this->logProxyError($index . ' raster tile', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, $index . ' raster tile');
		}

		$response = new DataDisplayResponse($binary['body'], Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}


	/**
	 * Proxy a raster-dates request to the Django backend.
	 */
	private function proxyRasterDates(string $farmId, string $index): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry($index . ' raster dates', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('raster_dates', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, [
				'farm_id' => $farmId,
				'index' => $index,
			]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest($index . ' raster dates', $operation, $path, $query, $requestId);
			$json = $this->weatherApiClient->requestJsonWithStatus(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				null,
				$requestId,
			);
			$this->logProxyResponse($index . ' raster dates', $operation, $path, $requestId, $json['statusCode']);
		} catch (WeatherApiException $exception) {
			$this->logProxyError($index . ' raster dates', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, $index . ' raster dates');
		}

		return $this->buildSuccessResponse($json['payload'] ?? null, $index . ' raster dates loaded.');
	}


	/**
	 * Proxy a geotiff download request to the Django backend.
	 */
	private function proxyGeotiff(string $farmId, string $index): Response {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry($index . ' geotiff', $requestId);
		$invalid = $this->validateFarmId($farmId, $requestId);
		if ($invalid !== null) {
			return $invalid;
		}

		$path = '';
		$query = [];

		try {
			$operation = $this->schemaService->getFarmOperation('geotiff_download', $requestId);
			$pathTemplate = (string)($operation['path'] ?? '');
			$path = $this->applyPathParams($pathTemplate, [
				'farm_id' => $farmId,
				'index' => $index,
			]);
			$params = $this->stripPathParams($this->request->getParams(), $pathTemplate);
			$externalFarmId = $this->pullExternalFarmId($params);
			$query = $this->filterQueryParams(
				$params,
				$operation['queryParams'] ?? [],
			);
			$this->requireQueryParams($query, $operation['queryParams'] ?? []);
			$dateField = $this->resolveQueryParamName($operation['queryParams'] ?? [], 'date');
			if ($dateField !== null && array_key_exists($dateField, $query)) {
				$this->parseIsoDateValue($query[$dateField], $dateField);
			}
			$query = $this->appendExternalFarmIdValue($externalFarmId, $query);
			$this->logProxyRequest($index . ' geotiff', $operation, $path, $query, $requestId);
			$binary = $this->weatherApiClient->requestBinary(
				(string)($operation['method'] ?? 'GET'),
				$path,
				$query,
				$requestId,
			);
			$this->logProxyResponse($index . ' geotiff', $operation, $path, $requestId, $binary['statusCode']);
		} catch (WeatherApiException $exception) {
			$this->logProxyError($index . ' geotiff', $operation, $path, $query, $requestId, $exception);
			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getDetails(),
			);
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, $index . ' geotiff');
		}

		$response = new DataDisplayResponse($binary['body'], Http::STATUS_OK, ['Content-Type' => 'image/tiff']);
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}


	private function validateFarmId(string $farmId, string $requestId): ?JSONResponse {
		$trimmed = trim($farmId);
		if ($trimmed === '' || !ctype_digit($trimmed)) {
			return $this->buildErrorResponse(
				'invalid_argument',
				'Invalid farmId.',
				$requestId,
				Http::STATUS_BAD_REQUEST,
				['farmId' => $farmId],
			);
		}

		return null;
	}

	/**
	 * @param array<string, mixed> $params
	 * @param array<string, mixed> $query
	 * @return null|string
	 */
	private function pullExternalFarmId(array &$params): ?string {
		if (!array_key_exists('external_farm_id', $params)) {
			return null;
		}

		$value = $params['external_farm_id'];
		unset($params['external_farm_id']);

		if (!is_string($value)) {
			throw new WeatherApiException('invalid_argument', 'Invalid external_farm_id.');
		}

		$trimmed = trim($value);
		if ($trimmed === '') {
			throw new WeatherApiException('invalid_argument', 'Invalid external_farm_id.');
		}

		return $trimmed;
	}

	/**
	 * @param array<string, mixed> $query
	 * @return array<string, mixed>
	 */
	private function appendExternalFarmIdValue(?string $externalFarmId, array $query): array {
		if ($externalFarmId === null) {
			return $query;
		}

		$query['external_farm_id'] = $externalFarmId;

		return $query;
	}


	/**
	 * @param array<string, mixed>|null $details
	 */
	private function buildErrorResponse(
		string $code,
		string $message,
		string $requestId,
		int $status,
		?array $details = null,
	): JSONResponse {
		$detailsPayload = $details === null || $details === [] ? new \stdClass() : $details;

		return new JSONResponse([
			'status' => 'error',
			'ok' => false,
			'message' => $message,
			'error' => [
				'code' => $code,
				'message' => $message,
				'requestId' => $requestId,
				'details' => $detailsPayload,
			],
		], HttpStatus::normalize($status));
	}

	/**
	 * @param array<array-key, mixed>|null $data
	 */
	private function buildSuccessResponse(?array $data, string $message = '', int $status = Http::STATUS_OK): JSONResponse {
		return new JSONResponse([
			'status' => 'ok',
			'ok' => true,
			'message' => $message,
			'data' => $data,
		], HttpStatus::normalize($status));
	}

	private function buildUpstreamErrorResponse(): JSONResponse {
		return new JSONResponse([
			'status' => 'error',
			'code' => 'upstream_error',
		], Http::STATUS_BAD_GATEWAY);
	}

	private function handleWeatherApiException(WeatherApiException $exception, string $requestId, string $action): JSONResponse {
		$this->logger->warning(
			'Weather API admin request failed',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'code' => $exception->getErrorCode(),
				'reason' => $exception->getReason() ?? '',
			]),
		);

		return $this->buildErrorResponse(
			$exception->getErrorCode(),
			$exception->getMessage(),
			$requestId,
			$this->httpStatusForCode($exception->getErrorCode()),
			$exception->getDetails(),
		);
	}

	/**
	 * @param array<string, mixed> $operation
	 * @param array<string, mixed> $query
	 */
	private function logProxyRequest(
		string $action,
		array $operation,
		string $path,
		array $query,
		string $requestId,
	): void {
		$this->logger->debug(
			'Weather API admin proxy request',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'method' => (string)($operation['method'] ?? ''),
				'pathTemplate' => (string)($operation['path'] ?? ''),
				'path' => $path,
				'queryKeys' => array_values(array_keys($query)),
			]),
		);
	}
	private function logProxyResponse(
		string $action,
		array $operation,
		string $path,
		string $requestId,
		int $statusCode,
	): void {
		$this->logger->debug(
			'Weather API admin proxy response',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'method' => (string)($operation['method'] ?? ''),
				'pathTemplate' => (string)($operation['path'] ?? ''),
				'path' => $path,
				'httpStatus' => $statusCode,
			]),
		);
	}


	/**
	 * @param array<string, mixed> $operation
	 * @param array<string, mixed> $query
	 */
	private function logProxyError(
		string $action,
		array $operation,
		string $path,
		array $query,
		string $requestId,
		WeatherApiException $exception,
	): void {
		$details = $exception->getDetails();
		$context = [
			'action' => $action,
			'requestId' => $requestId,
			'method' => (string)($operation['method'] ?? ''),
			'pathTemplate' => (string)($operation['path'] ?? ''),
			'path' => $path,
			'queryKeys' => array_values(array_keys($query)),
		];

		if (isset($details['httpStatus'])) {
			$context['httpStatus'] = $details['httpStatus'];
		}
		if (isset($details['responseContentType'])) {
			$context['responseContentType'] = $details['responseContentType'];
		}

		$this->logger->debug(
			'Weather API admin proxy error',
			LogSanitizer::sanitizeContext($context),
		);
	}

	private function handleUnexpectedError(\Throwable $throwable, string $requestId, string $action): JSONResponse {
		$this->logger->warning(
			'Weather API admin request failed',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'error' => $throwable->getMessage(),
			]),
		);

		return $this->buildErrorResponse(
			'backend_error',
			'Backend request failed.',
			$requestId,
			Http::STATUS_BAD_REQUEST,
		);
	}

	private function httpStatusForCode(string $code): int {
		return match ($code) {
			'invalid_argument' => Http::STATUS_BAD_REQUEST,
			'unauthorized' => Http::STATUS_UNAUTHORIZED,
			'forbidden' => Http::STATUS_FORBIDDEN,
			'backend_timeout' => Http::STATUS_GATEWAY_TIMEOUT,
			'backend_unavailable' => Http::STATUS_SERVICE_UNAVAILABLE,
			default => Http::STATUS_BAD_REQUEST,
		};
	}
}
