<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Controller;

use OCA\WeatherApis\Service\WeatherApiClient;
use OCA\WeatherApis\Service\WeatherApiException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

final class ApiController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private readonly WeatherApiClient $weatherApiClient,
		private readonly IUserSession $userSession,
		private readonly IGroupManager $groupManager,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
	}

	public function getWhoami(): DataResponse {
		$requestId = $this->resolveRequestId();

		$user = $this->userSession->getUser();
		if ($user === null) {
			return $this->buildErrorResponse('unauthorized', 'Authentication required.', $requestId, Http::STATUS_UNAUTHORIZED);
		}

		if (!$this->groupManager->isAdmin($user->getUID())) {
			return $this->buildErrorResponse('forbidden', 'Admin access required.', $requestId, Http::STATUS_FORBIDDEN);
		}

		try {
			$data = $this->weatherApiClient->whoami($requestId);

			return new DataResponse([
				'status' => 'ok',
				'data' => $data,
			]);
		} catch (WeatherApiException $exception) {
			$this->logger->error('Weather API call failed', [
				'errorCode' => $exception->getErrorCode(),
				'requestId' => $requestId,
			]);

			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
			);
		} catch (\Throwable $throwable) {
			$this->logger->error('Weather API call failed', [
				'error' => $throwable->getMessage(),
				'requestId' => $requestId,
			]);

			return $this->buildErrorResponse('backend_error', 'Unable to reach backend.', $requestId, Http::STATUS_SERVICE_UNAVAILABLE);
		}
	}

	private function buildErrorResponse(string $code, string $message, string $requestId, int $status): DataResponse {
		return new DataResponse([
			'status' => 'error',
			'error' => [
				'code' => $code,
				'message' => $message,
				'requestId' => $requestId,
				'details' => new \stdClass(),
			],
		], $status);
	}

	private function resolveRequestId(): string {
		$header = $this->request->getHeader('X-Request-Id');
		if ($header !== '') {
			return $header;
		}

		return $this->generateUuid();
	}

	private function generateUuid(): string {
		$bytes = random_bytes(16);
		$bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
		$bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
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
