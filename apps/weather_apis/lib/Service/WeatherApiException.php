<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

final class WeatherApiException extends \RuntimeException {
	private readonly string $errorCode;
	private readonly ?string $reason;
	/** @var array<string, mixed> */
	private readonly array $details;

	/**
	 * @param array<string, mixed> $details
	 */
	public function __construct(
		string $errorCode,
		string $message,
		?\Throwable $previous = null,
		?string $reason = null,
		?array $details = null,
	) {
		parent::__construct($message, 0, $previous);
		$this->errorCode = $errorCode;
		$this->reason = $reason;
		$this->details = $details ?? [];
	}

	public function getErrorCode(): string {
		return $this->errorCode;
	}

	public function getReason(): ?string {
		return $this->reason;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getDetails(): array {
		return $this->details;
	}
}
