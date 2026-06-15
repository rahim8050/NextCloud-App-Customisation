<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Sections;

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
		return 'farm_intelligence_platform';
	}

	public function getName(): string {
		return $this->l10n->t('Farm Intelligence Platform');
	}

	public function getIcon(): string {
		return $this->urlGenerator->imagePath('core', 'actions/settings-dark.svg');
	}

	public function getPriority(): int {
		return 98;
	}
}
