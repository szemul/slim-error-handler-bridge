<?php

declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Request;

use BackedEnum;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\Exceptions\InvalidFormatException;
use InvalidArgumentException;
use Szemul\NotSetValue\NotSetValue;
use Szemul\SlimErrorHandlerBridge\Enum\ParameterErrorReason;
use Szemul\SlimErrorHandlerBridge\Enum\RequestValueType;
use Szemul\SlimErrorHandlerBridge\ParameterError\ParameterErrorCollectingInterface;
use ValueError;

class RequestArrayHandler
{
    /** @param array<string,mixed> $array */
    public function __construct(
        protected array $array,
        protected ?ParameterErrorCollectingInterface $errors,
        protected string $errorKeyPrefix,
    ) {
    }

    public function getSingleValue(
        string $key,
        bool $isRequired,
        RequestValueType $type,
        callable $validationFunction = null,
        null|string|float|int|bool|NotSetValue $defaultValue = null,
    ): null|string|float|int|bool|NotSetValue {
        $exists = array_key_exists($key, $this->array);

        if ($isRequired && !$exists) {
            $this->addError($key, ParameterErrorReason::MISSING);

            return null;
        }

        if (!$exists) {
            return $this->getDefaultValue($type, func_num_args() < 5 ? new NotSetValue() : $defaultValue);
        }

        $value = $this->getTypedValue($type, $this->array[$key]);

        if (null !== $validationFunction && !$validationFunction($value)) {
            $this->addError($key, ParameterErrorReason::INVALID);

            return null;
        }

        return $value;
    }

    /**
     * @param array<string|float|int|bool> $defaultValue
     *
     * @return NotSetValue|string[]|float[]|int[]|bool[]
     */
    public function getArrayValue(
        string $key,
        bool $isRequired,
        RequestValueType $elementType,
        callable $validationFunction = null,
        callable $elementValidationFunction = null,
        array $defaultValue = [],
    ): array|NotSetValue {
        $result = [];

        $exists = array_key_exists($key, $this->array);

        if ($isRequired && !$exists) {
            $this->addError($key, ParameterErrorReason::MISSING);

            return $result;
        }

        if (!$exists) {
            return func_num_args() < 5 ? new NotSetValue() : $defaultValue;
        }

        if (!is_array($this->array[$key])) {
            $this->addError($key, ParameterErrorReason::INVALID);

            return $result;
        }

        foreach ($this->array[$key] as $index => $value) {
            $typedValue = $this->getTypedValue($elementType, $value);

            if (null !== $elementValidationFunction && !$elementValidationFunction($typedValue)) {
                $this->addError($key . '.' . $index, ParameterErrorReason::INVALID);
                continue;
            }

            $result[$index] = $typedValue;
        }

        if (null !== $validationFunction && !$validationFunction($result)) {
            $this->addError($key, ParameterErrorReason::INVALID);

            return [];
        }

        return $result;
    }

    public function getDate(string $key, bool $isRequired, bool $allowMicroseconds = false): ?CarbonInterface
    {
        if (empty($this->array[$key]) && $isRequired) {
            $this->addError($key, ParameterErrorReason::MISSING);

            return null;
        }

        if (empty($this->array[$key])) {
            return null;
        }

        try {
            try {
                $date = CarbonImmutable::createFromFormat(CarbonInterface::ATOM, $this->array[$key])->setMicrosecond(0);
            } catch (InvalidFormatException $e) {
                if (!$allowMicroseconds) {
                    throw $e;
                }
                $date = CarbonImmutable::createFromFormat('Y-m-d\TH:i:s.uP', $this->array[$key]);
            }

            $date->setTimezone('UTC');

            return $date;
        } catch (InvalidArgumentException) {
            $this->addError($key, ParameterErrorReason::INVALID);
        }

        return null;
    }

    public function getEnum(
        string $key,
        string $enumClassName,
        bool $isRequired,
        ?NotSetValue $defaultValue = null,
    ): BackedEnum|NotSetValue|null {
        $result = func_num_args() < 4 ? new NotSetValue() : $defaultValue;

        if (empty($this->array[$key]) && $isRequired) {
            $this->addError($key, ParameterErrorReason::MISSING);
        } elseif (!empty($this->array[$key])) {
            try {
                $result = $enumClassName::from($this->array[$key]);
            } catch (ValueError $valueError) {
                $this->addError($key, ParameterErrorReason::INVALID);
            }
        }

        return $result;
    }

    /**
     * Returns TRUE if the specified date string is a valid date that matches the specified format
     */
    public function validateDateString(string $dateString, string $format): bool
    {
        try {
            $instance = CarbonImmutable::createFromFormat($format, $dateString);

            return $instance instanceof CarbonImmutable;
        } catch (InvalidArgumentException) { // @phpstan-ignore-line This exception can be thrown by carbon
            return false;
        }
    }

    public function getDefaultValue(
        RequestValueType $type,
        null|string|float|int|bool|NotSetValue $defaultValue = null,
    ): null|string|float|int|bool|NotSetValue {
        if ($defaultValue instanceof NotSetValue) {
            return $defaultValue;
        }

        return null === $defaultValue ? null : $this->getTypedValue($type, $defaultValue);
    }

    public function convertNotSetValue(mixed $value, mixed $defaultValue = null): mixed
    {
        return $value instanceof NotSetValue ? $defaultValue : $value;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function getTypedValue(RequestValueType $type, mixed $value): bool|int|float|string
    {
        return match ($type) {
            RequestValueType::TYPE_INT    => (int)$value,
            RequestValueType::TYPE_FLOAT  => (float)$value,
            RequestValueType::TYPE_STRING => (string)$value,
            RequestValueType::TYPE_BOOL   => (bool)$value,
            default                       => throw new InvalidArgumentException('Invalid type given'),
        };
    }

    protected function addError(string $key, ParameterErrorReason $reason): void
    {
        if (is_null($this->errors)) {
            return;
        }

        $this->errors->addParameterError($this->errorKeyPrefix . $key, $reason);
    }
}
