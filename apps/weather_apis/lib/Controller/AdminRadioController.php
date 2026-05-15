<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Controller;

use OCA\WeatherApis\Service\LogSanitizer;
use OCA\WeatherApis\Service\WeatherApiClientInterface;
use OCA\WeatherApis\Service\WeatherApiException;
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
				'details' => $exception->getDetails() ?? new \stdClass(),
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
