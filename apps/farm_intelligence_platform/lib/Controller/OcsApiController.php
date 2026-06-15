<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Controller;

use OCA\FarmIntelligencePlatform\Service\WeatherApiClientInterface;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

final class OcsApiController extends OCSController {
	use WhoamiRequestHandlerTrait;

	public function __construct(
		string $appName,
		IRequest $request,
		private readonly WeatherApiClientInterface $weatherApiClient,
		private readonly IUserSession $userSession,
		private readonly IGroupManager $groupManager,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
	}

	public function getIntegrationWhoami(): DataResponse {
		return $this->handleIntegrationWhoamiRequest($this->request, $this->weatherApiClient, $this->userSession, $this->groupManager, $this->logger);
	}
}
