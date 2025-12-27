<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

use OCP\App\IAppManager;
use Symfony\Component\Yaml\Yaml;

final class OpenApiRegistry {
	/** @var array<string, OpenApiOperation> */
	private array $ops = [];

	private bool $loaded = false;

	public function __construct(
		private readonly IAppManager $appManager,
		private readonly string $appId,
	) {
	}

	/** @return array<string, OpenApiOperation> */
	public function all(): array {
		$this->load();
		return $this->ops;
	}

	public function get(string $operationId): OpenApiOperation {
		$this->load();
		if (!isset($this->ops[$operationId])) {
			throw new \InvalidArgumentException("Unknown operationId: {$operationId}");
		}
		return $this->ops[$operationId];
	}

	private function load(): void {
		if ($this->loaded) {
			return;
		}

		$appPath = $this->appManager->getAppPath($this->appId);
		if ($appPath === '') {
			throw new \RuntimeException("Cannot resolve app path for {$this->appId}");
		}

		$schemaPath = rtrim($appPath, '/') . '/resources/openapi.yml';
		if (!is_file($schemaPath)) {
			throw new \RuntimeException("OpenAPI schema not found: {$schemaPath}");
		}

		$spec = Yaml::parseFile($schemaPath);
		if (!is_array($spec) || !isset($spec['paths']) || !is_array($spec['paths'])) {
			throw new \RuntimeException('Invalid OpenAPI schema: missing paths');
		}

		/** @var array<string, mixed> $paths */
		$paths = $spec['paths'];

		foreach ($paths as $pathTemplate => $methods) {
			if (!is_array($methods)) {
				continue;
			}
			foreach ($methods as $method => $meta) {
				$m = strtoupper((string)$method);
				if (!in_array($m, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
					continue;
				}
				if (!is_array($meta) || !isset($meta['operationId'])) {
					continue;
				}

				$operationId = (string)$meta['operationId'];
				$security = isset($meta['security']) && is_array($meta['security']) ? $meta['security'] : [];
				$parameters = isset($meta['parameters']) && is_array($meta['parameters']) ? $meta['parameters'] : [];
				$produces = $this->extractProduces($meta);

				$this->ops[$operationId] = new OpenApiOperation(
					$operationId,
					$m,
					(string)$pathTemplate,
					$security,
					$parameters,
					$produces,
				);
			}
		}

		$this->loaded = true;
	}

	/**
	 * @param array<string, mixed> $meta
	 * @return list<string>
	 */
	private function extractProduces(array $meta): array {
		$out = [];
		if (!isset($meta['responses']) || !is_array($meta['responses'])) {
			return $out;
		}
		foreach ($meta['responses'] as $r) {
			if (!is_array($r) || !isset($r['content']) || !is_array($r['content'])) {
				continue;
			}
			foreach ($r['content'] as $contentType => $_schema) {
				$out[] = (string)$contentType;
			}
		}
		return array_values(array_unique($out));
	}
}
