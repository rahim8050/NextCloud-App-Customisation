<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Controller;

use OCA\FarmIntelligencePlatform\Service\LogSanitizer;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\UseSession;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

#[UseSession]
final class RadioUserController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private readonly WeatherApiClientInterface $weatherApiClient,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
	}

	public function listFavorites(?int $page = null, ?int $pageSize = null): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('list favorites', $requestId);
		$queryParams = [];
		if ($page !== null) {
			$queryParams['page'] = $page;
		}
		if ($pageSize !== null) {
			$queryParams['page_size'] = $pageSize;
		}
		try {
			$result = $this->weatherApiClient->requestJsonWithStatus('GET', '/api/v1/radio/favorites/', $queryParams, null, $requestId);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'list favorites');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'list favorites');
		}
		return $this->buildSuccessResponse($payload, 'Favorites loaded.');
	}

	public function addFavorite(): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('add favorite', $requestId);
		$body = $this->request->getParams();
		try {
			$result = $this->weatherApiClient->requestJsonWithStatus('POST', '/api/v1/radio/favorites/', [], $body, $requestId);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'add favorite');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'add favorite');
		}
		return $this->buildSuccessResponse($payload, 'Favorite added.', Http::STATUS_CREATED);
	}

	public function removeFavorite(string $stationId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('remove favorite', $requestId);
		try {
			$result = $this->weatherApiClient->requestJsonWithStatus('DELETE', '/api/v1/radio/favorites/' . rawurlencode($stationId) . '/', [], null, $requestId);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'remove favorite');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'remove favorite');
		}
		return $this->buildSuccessResponse($payload, 'Favorite removed.');
	}

	public function listHistory(?int $page = null, ?int $pageSize = null): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('list history', $requestId);
		$queryParams = [];
		if ($page !== null) {
			$queryParams['page'] = $page;
		}
		if ($pageSize !== null) {
			$queryParams['page_size'] = $pageSize;
		}
		try {
			$result = $this->weatherApiClient->requestJsonWithStatus('GET', '/api/v1/radio/history/', $queryParams, null, $requestId);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'list history');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'list history');
		}
		return $this->buildSuccessResponse($payload, 'History loaded.');
	}

	public function getRecentHistory(?int $limit = null): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get recent history', $requestId);
		$queryParams = [];
		if ($limit !== null) {
			$queryParams['limit'] = $limit;
		}
		try {
			$result = $this->weatherApiClient->requestJsonWithStatus('GET', '/api/v1/radio/history/recent/', $queryParams, null, $requestId);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'get recent history');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'get recent history');
		}
		return $this->buildSuccessResponse($payload, 'Recent history loaded.');
	}

	public function stopSession(int $sessionId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('stop session', $requestId);
		try {
			$result = $this->weatherApiClient->requestJsonWithStatus('POST', '/api/v1/radio/history/' . $sessionId . '/stop/', [], null, $requestId);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'stop session');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'stop session');
		}
		return $this->buildSuccessResponse($payload, 'Session stopped.');
	}

	public function getSignedStream(string $stationId): JSONResponse {
		$requestId = $this->resolveRequestId();
		$this->logEndpointEntry('get signed stream', $requestId);
		try {
			$result = $this->weatherApiClient->requestJsonWithStatus('GET', '/api/v1/radio/stations/' . rawurlencode($stationId) . '/stream/signed/', [], null, $requestId);
			$payload = $result['payload'];
		} catch (WeatherApiException $e) {
			return $this->handleWeatherApiException($e, $requestId, 'get signed stream');
		} catch (\Throwable $t) {
			return $this->handleUnexpectedError($t, $requestId, 'get signed stream');
		}
		$response = $this->buildSuccessResponse($payload, 'Signed stream URL generated.');
		$response->addHeader('Cache-Control', 'no-store');
		return $response;
	}

	private function logEndpointEntry(string $action, string $requestId): void {
		$path = $this->request->getPathInfo();
		if ($path === '') {
			$path = $this->request->getRequestUri();
		}
		$this->logger->debug(
			'Weather API radio user endpoint hit',
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
			'Weather API radio user request failed',
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
			'Weather API radio user request failed',
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
