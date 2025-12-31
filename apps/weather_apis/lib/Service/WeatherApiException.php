<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

final class WeatherApiException extends \RuntimeException {
	private readonly string $errorCode;
	private readonly ?string $reason;

	public function __construct(string $errorCode, string $message, ?\Throwable $previous = null, ?string $reason = null) {
		parent::__construct($message, 0, $previous);
		$this->errorCode = $errorCode;
		$this->reason = $reason;
	}

	public function getErrorCode(): string {
		return $this->errorCode;
	}

	public function getReason(): ?string {
		return $this->reason;
	}
}
