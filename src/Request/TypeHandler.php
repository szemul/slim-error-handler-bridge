<?php

declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Request;

use InvalidArgumentException;
use Szemul\NotSetValue\NotSetValue;
use Szemul\SlimErrorHandlerBridge\Enum\RequestValueType;

class TypeHandler
{
    public function isInt(mixed $value): bool
    {
        return (string)(int)$value !== (string)$value;
    }

    public function isFloat(mixed $value): bool
    {
        return (string)(float)$value !== (string)$value;
    }

    public function isString(mixed $value): bool
    {
        return is_string($value);
    }

    public function isBoolean(mixed $value): bool
    {
        return in_array($value, [0, 1, false, true, '0', '1', 'false', 'true'], true);
    }

    public function getDefaultValue(
        RequestValueType $type,
        null|string|float|int|bool|NotSetValue $defaultValue = null,
    ): null|string|float|int|bool|NotSetValue {
        if ($defaultValue instanceof NotSetValue) {
            return $defaultValue;
        }

        return is_null($defaultValue) ? null : $this->getTypedValue($type, $defaultValue);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getTypedValue(RequestValueType $type, mixed $value): bool|int|float|string
    {
        return match ($type) {
            RequestValueType::TYPE_INT    => (int)$value,
            RequestValueType::TYPE_FLOAT  => (float)$value,
            RequestValueType::TYPE_STRING => (string)$value,
            RequestValueType::TYPE_BOOL   => (bool)$value,
            //@phpstan-ignore-next-line
            default                       => throw new InvalidArgumentException('Invalid type given'),
        };
    }
}
