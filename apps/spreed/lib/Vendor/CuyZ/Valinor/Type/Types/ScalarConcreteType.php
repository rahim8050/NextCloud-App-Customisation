<?php

declare(strict_types=1);

namespace OCA\Talk\Vendor\CuyZ\Valinor\Type\Types;

use OCA\Talk\Vendor\CuyZ\Valinor\Compiler\Native\ComplianceNode;
use OCA\Talk\Vendor\CuyZ\Valinor\Compiler\Node;
use OCA\Talk\Vendor\CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use OCA\Talk\Vendor\CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\BooleanType;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\FloatType;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\IntegerType;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\ScalarType;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\StringType;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Type;
use OCA\Talk\Vendor\CuyZ\Valinor\Utility\IsSingleton;
use Stringable;

use function assert;
use function is_scalar;

/** @internal */
final class ScalarConcreteType implements ScalarType
{
    use IsSingleton;

    public function accepts(mixed $value): bool
    {
        return is_scalar($value);
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        return Node::functionCall('is_scalar', [$node]);
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        return $other instanceof self
            || $other instanceof IntegerType
            || $other instanceof FloatType
            || $other instanceof StringType
            || $other instanceof BooleanType
            || $other instanceof MixedType;
    }

    public function canCast(mixed $value): bool
    {
        return is_scalar($value) || $value instanceof Stringable;
    }

    public function cast(mixed $value): bool|string|int|float
    {
        assert($this->canCast($value));

        if ($value instanceof Stringable) {
            return (string)$value;
        }

        return $value; // @phpstan-ignore return.type (must be scalar)
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} is not a valid scalar.')->build();
    }

    public function nativeType(): UnionType
    {
        return new UnionType(
            new NativeIntegerType(),
            new NativeFloatType(),
            new NativeStringType(),
            new NativeBooleanType(),
        );
    }

    public function toString(): string
    {
        return 'scalar';
    }
}
