<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Controller;

use OCA\FarmIntelligencePlatform\Service\HttpStatus;
use OCA\FarmIntelligencePlatform\Service\LogSanitizer;
use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCA\FarmIntelligencePlatform\Service\WeatherApiException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

trait WhoamiRequestHandlerTrait {
	private function handleIntegrationWhoamiRequest(
		IRequest $request,
		WeatherApiClientInterface $weatherApiClient,
		IUserSession $userSession,
		IGroupManager $groupManager,
		LoggerInterface $logger,
	): DataResponse {
		$requestId = $this->resolveRequestId($request);

		$user = $userSession->getUser();
		if ($user === null) {
			return $this->buildErrorResponse('unauthorized', 'Authentication required.', $requestId, Http::STATUS_UNAUTHORIZED);
		}

		if (!$groupManager->isAdmin($user->getUID())) {
			return $this->buildErrorResponse('forbidden', 'Admin access required.', $requestId, Http::STATUS_FORBIDDEN);
		}

		try {
			$data = $weatherApiClient->whoami($requestId);

			return new DataResponse([
				'status' => 'ok',
				'data' => $data,
			]);
		} catch (WeatherApiException $exception) {
			$logger->error(
				'Weather API call failed',
				LogSanitizer::sanitizeContext([
					'errorCode' => $exception->getErrorCode(),
					'reason' => $exception->getReason(),
					'requestId' => $requestId,
				]),
			);

			return $this->buildErrorResponse(
				$exception->getErrorCode(),
				$exception->getMessage(),
				$requestId,
				$this->httpStatusForCode($exception->getErrorCode()),
				$exception->getReason(),
			);
		} catch (\Throwable $throwable) {
			$logger->error(
				'Weather API call failed',
				LogSanitizer::sanitizeContext([
					'error' => $throwable->getMessage(),
					'requestId' => $requestId,
				]),
			);

			return $this->buildErrorResponse('backend_error', 'Unable to reach backend.', $requestId, Http::STATUS_SERVICE_UNAVAILABLE);
		}
	}

	private function buildErrorResponse(
		string $code,
		string $message,
		string $requestId,
		int $status,
		?string $reason = null,
	): DataResponse {
		$details = new \stdClass();
		if ($code === 'backend_unavailable' && $reason !== null && $reason !== '') {
			$details->reason = $reason;
		}

		return new DataResponse([
			'status' => 'error',
			'error' => [
				'code' => $code,
				'message' => $message,
				'requestId' => $requestId,
				'details' => $details,
			],
		], HttpStatus::normalize($status));
	}

	private function resolveRequestId(IRequest $request): string {
		$header = $request->getHeader('X-Request-Id');
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
