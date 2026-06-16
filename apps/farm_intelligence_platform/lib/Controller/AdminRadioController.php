<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Controller;

use OCA\FarmIntelligencePlatform\Service\LogSanitizer;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

final class AdminRadioController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private readonly WeatherApiClientInterface $weatherApiClient,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
	}

	#[AdminRequired]
	public function listProviders(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('list radio providers', $requestId);

		try {
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				'/api/v1/radio/providers/',
				[],
				null,
				$requestId,
			);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'list radio providers');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'list radio providers');
		}

		return $this->buildSuccessResponse($payload, 'Radio providers loaded.');
	}

	#[AdminRequired]
	public function listStations(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('list radio stations', $requestId);

		try {
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				'/api/v1/radio/stations/',
				[],
				null,
				$requestId,
			);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'list radio stations');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'list radio stations');
		}

		return $this->buildSuccessResponse($payload, 'Radio stations loaded.');
	}

	#[AdminRequired]
	public function getStation(string $stationId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get radio station', $requestId);

		try {
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				'/api/v1/radio/stations/' . rawurlencode($stationId) . '/',
				[],
				null,
				$requestId,
			);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get radio station');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get radio station');
		}

		return $this->buildSuccessResponse($payload, 'Radio station loaded.');
	}

	#[AdminRequired]
	public function getStreamUrl(string $stationId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get radio stream', $requestId);

		try {
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				'/api/v1/radio/stations/' . rawurlencode($stationId) . '/stream/',
				[],
				null,
				$requestId,
			);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get radio stream');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get radio stream');
		}

		return $this->buildSuccessResponse($payload, 'Stream URL loaded.');
	}

	#[AdminRequired]
	public function getStationNowPlaying(string $stationId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get radio station now playing', $requestId);

		try {
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				'/api/v1/radio/stations/' . rawurlencode($stationId) . '/now-playing/',
				[],
				null,
				$requestId,
			);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get radio station now playing');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get radio station now playing');
		}

		return $this->buildSuccessResponse($payload, 'Now playing loaded.');
	}

	#[AdminRequired]
	public function getStationAnalytics(string $stationId, ?int $days = null): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get radio station analytics', $requestId);

		$queryParams = [];
		if ($days !== null) {
			$queryParams['days'] = $days;
		}

		try {
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				'/api/v1/radio/stations/' . rawurlencode($stationId) . '/analytics/',
				$queryParams,
				null,
				$requestId,
			);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get radio station analytics');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get radio station analytics');
		}

		return $this->buildSuccessResponse($payload, 'Analytics loaded.');
	}

	#[AdminRequired]
	public function getStationHealthHistory(string $stationId, ?int $limit = null): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get radio station health history', $requestId);

		$queryParams = [];
		if ($limit !== null) {
			$queryParams['limit'] = $limit;
		}

		try {
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				'/api/v1/radio/stations/' . rawurlencode($stationId) . '/health/',
				$queryParams,
				null,
				$requestId,
			);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get radio station health history');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get radio station health history');
		}

		return $this->buildSuccessResponse($payload, 'Station health history loaded.');
	}

	#[AdminRequired]
	public function getRadioHealth(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get radio health', $requestId);

		try {
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				'/api/v1/radio/health/',
				[],
				null,
				$requestId,
			);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get radio health');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get radio health');
		}

		return $this->buildSuccessResponse($payload, 'Radio health loaded.');
	}

	#[AdminRequired]
	public function getCurrentEmergency(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get current emergency', $requestId);

		try {
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				'/api/v1/radio/emergency/current/',
				[],
				null,
				$requestId,
			);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get current emergency');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get current emergency');
		}

		return $this->buildSuccessResponse($payload, 'Current emergency loaded.');
	}

	#[AdminRequired]
	public function getEmergencyHistory(?int $limit = null): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get emergency history', $requestId);

		$queryParams = [];
		if ($limit !== null) {
			$queryParams['limit'] = $limit;
		}

		try {
			$result = $this->weatherApiClient->requestJsonWithStatus(
				'GET',
				'/api/v1/radio/emergency/history/',
				$queryParams,
				null,
				$requestId,
			);
			$payload = $result['payload'];
		} catch (WeatherApiException $exception) {
			return $this->handleWeatherApiException($exception, $requestId, 'get emergency history');
		} catch (\Throwable $throwable) {
			return $this->handleUnexpectedError($throwable, $requestId, 'get emergency history');
		}

		return $this->buildSuccessResponse($payload, 'Emergency history loaded.');
	}

	#[AdminRequired]
	public function createEmergency(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('create emergency', $requestId);
		$body = $this->request->getParams();
		try {
			$result = $this->weatherApiClient->requestJsonWithStatus('POST', '/api/v1/radio/emergency/', [], $body, $requestId);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'create emergency');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'create emergency');
		}
		return $this->buildSuccessResponse($payload, 'Emergency broadcast created.', Http::STATUS_CREATED);
	}

	#[AdminRequired]
	public function updateEmergency(int $pk): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('update emergency', $requestId);
		$body = $this->request->getParams();
		try {
			$result = $this->weatherApiClient->requestJsonWithStatus('PATCH', '/api/v1/radio/emergency/' . $pk . '/', [], $body, $requestId);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'update emergency');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'update emergency');
		}
		return $this->buildSuccessResponse($payload, 'Emergency broadcast updated.');
	}

	#[AdminRequired]
	public function deleteEmergency(int $pk): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('delete emergency', $requestId);
		try {
			$result = $this->weatherApiClient->requestJsonWithStatus('DELETE', '/api/v1/radio/emergency/' . $pk . '/', [], null, $requestId);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'delete emergency');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'delete emergency');
		}
		return $this->buildSuccessResponse($payload, 'Emergency broadcast removed.');
	}

	#[AdminRequired]
	public function synthesizeTts(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('synthesize TTS', $requestId);
		$body = $this->request->getParams();
		try {
			$result = $this->weatherApiClient->requestJsonWithStatus('POST', '/api/v1/radio/tts/', [], $body, $requestId);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'synthesize TTS');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'synthesize TTS');
		}
		return $this->buildSuccessResponse($payload, 'TTS synthesis complete.');
	}

	private function logEndpointEntry(string $action, string $requestId): void {
		$path = $this->request->getPathInfo();
		if ($path === '') {
			$path = $this->request->getRequestUri();
		}

		$this->logger->debug(
			'Weather API radio endpoint hit',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'method' => $this->request->getMethod(),
				'path' => $path,
			]),
		);
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

	private function buildSuccessResponse(?array $data, string $message, int $status = Http::STATUS_OK): JSONResponse {
		return new JSONResponse([
			'status' => 'ok',
			'ok' => true,
			'message' => $message,
			'data' => $data,
		], $status);
	}

	private function handleWeatherApiException(WeatherApiException $exception, string $requestId, string $action): JSONResponse {
		$this->logger->warning(
			'Weather API radio request failed',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'code' => $exception->getErrorCode(),
			]),
		);

		return new JSONResponse([
			'status' => 'error',
			'ok' => false,
			'message' => $exception->getMessage(),
			'error' => [
				'code' => $exception->getErrorCode(),
				'message' => $exception->getMessage(),
				'requestId' => $requestId,
				'details' => $exception->getDetails(),
			],
		], Http::STATUS_BAD_REQUEST);
	}

	private function handleUnexpectedError(\Throwable $throwable, string $requestId, string $action): JSONResponse {
		$this->logger->warning(
			'Weather API radio request failed',
			LogSanitizer::sanitizeContext([
				'action' => $action,
				'requestId' => $requestId,
				'error' => $throwable->getMessage(),
			]),
		);

		return new JSONResponse([
			'status' => 'error',
			'ok' => false,
			'message' => 'Backend request failed.',
			'error' => [
				'code' => 'backend_error',
				'message' => 'Backend request failed.',
				'requestId' => $requestId,
				'details' => new \stdClass(),
			],
		], Http::STATUS_BAD_REQUEST);
	}
}
