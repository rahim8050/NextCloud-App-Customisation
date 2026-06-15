<?php

declare(strict_types=1);

namespace OCA\FarmIntelligencePlatform\Listeners;

use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

/**
 * @template-implements IEventListener<AddContentSecurityPolicyEvent>
 */
final class CSPListener implements IEventListener {
	public function handle(Event $event): void {
		if (!$event instanceof AddContentSecurityPolicyEvent) {
			return;
		}

		$csp = new ContentSecurityPolicy();
		$csp->addAllowedMediaDomain('*');
		$csp->addAllowedConnectDomain('*');
		$csp->addAllowedImageDomain('*');
		$event->addPolicy($csp);
	}
}
