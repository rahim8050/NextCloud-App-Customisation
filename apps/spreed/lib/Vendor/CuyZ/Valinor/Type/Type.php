<?php

declare(strict_types=1);

namespace OCA\Talk\Vendor\CuyZ\Valinor\Type;

use OCA\Talk\Vendor\CuyZ\Valinor\Compiler\Native\ComplianceNode;

/** @internal */
interface Type
{
    public function accepts(mixed $value): bool;

    public function compiledAccept(ComplianceNode $node): ComplianceNode;

    public function matches(self $other): bool;

    public function nativeType(): Type;

    public function toString(): string;
}
