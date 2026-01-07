<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

final class IntegrationConfigException extends \InvalidArgumentException {
	private string $errorCode;

	public function __construct(string $errorCode, string $message, ?\Throwable $previous = null) {
		parent::__construct($message, 0, $previous);
		$this->errorCode = $errorCode;
	}

	public function getErrorCode(): string {
		return $this->errorCode;
	}
}
