<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Service;

final class OpenApiOperation {
	/**
	 * @param list<array<string, list<string>>> $security
	 * @param list<array<string, mixed>> $parameters
	 * @param list<string> $produces
	 */
	public function __construct(
		public readonly string $operationId,
		public readonly string $method,
		public readonly string $pathTemplate,
		public readonly array $security,
		public readonly array $parameters,
		public readonly array $produces,
	) {
	}
}
