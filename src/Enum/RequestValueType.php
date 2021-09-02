<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Enum;

use Emul\Enum\EnumAbstract;

class RequestValueType extends EnumAbstract
{
    public const TYPE_STRING = 'string';
    public const TYPE_INT    = 'int';
    public const TYPE_FLOAT  = 'float';
    public const TYPE_BOOL   = 'bool';

    /** @return string[] */
    protected static function getPossibleValues(): array
    {
        return [
            self::TYPE_STRING,
            self::TYPE_INT,
            self::TYPE_FLOAT,
            self::TYPE_BOOL,
        ];
    }

    public static function createString(): static
    {
        return static::createFromString(self::TYPE_STRING);
    }

    public static function createInt(): static
    {
        return static::createFromString(self::TYPE_INT);
    }

    public static function createFloat(): static
    {
        return static::createFromString(self::TYPE_FLOAT);
    }

    public static function createBool(): static
    {
        return static::createFromString(self::TYPE_BOOL);
    }
}
