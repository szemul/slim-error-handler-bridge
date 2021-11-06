<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Enum;

use Emul\Enum\EnumAbstract;
use JetBrains\PhpStorm\Immutable;

#[Immutable]
class ParameterErrorReason extends EnumAbstract
{
    public const MISSING       = 'missing';
    public const DUPLICATE     = 'duplicate';
    public const INVALID       = 'invalid';
    public const MUST_BE_EMPTY = 'mustBeEmpty';

    /** @return string[] */
    protected static function getPossibleValues(): array
    {
        return [
            self::MISSING,
            self::DUPLICATE,
            self::INVALID,
            self::MUST_BE_EMPTY,
        ];
    }

    public static function createMissing(): static
    {
        return static::createFromString(self::MISSING);
    }

    public static function createDuplicate(): static
    {
        return static::createFromString(self::DUPLICATE);
    }

    public static function createInvalid(): static
    {
        return static::createFromString(self::INVALID);
    }

    public static function createMustBeEmpty(): static
    {
        return static::createFromString(self::MUST_BE_EMPTY);
    }
}
