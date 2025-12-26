<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Sections;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

final class AdminSection implements IIconSection {
	public function __construct(
		private readonly string $appName,
		private readonly IURLGenerator $urlGenerator,
		private readonly IL10N $l10n,
	) {
	}

	public function getID(): string {
		return 'weather_apis';
	}

	public function getName(): string {
		return $this->l10n->t('Weather APIs');
	}

	public function getIcon(): string {
		return $this->urlGenerator->imagePath('core', 'actions/settings-dark.svg');
	}

	public function getPriority(): int {
		return 98;
	}
}
