<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Request;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\Exceptions\InvalidFormatException;
use InvalidArgumentException;
use Szemul\NotSetValue\NotSetValue;
use Szemul\SlimErrorHandlerBridge\Enum\ParameterErrorReason;
use Szemul\SlimErrorHandlerBridge\Enum\RequestValueType;
use Szemul\SlimErrorHandlerBridge\ParameterError\ParameterErrorCollectingInterface;

class RequestArrayHandler
{
    /** @param array<string,mixed> $array */
    public function __construct(
        protected array $array,
        protected ?ParameterErrorCollectingInterface $errors,
        protected string $errorKeyPrefix,
    ) {
    }

    protected function addError(
        string $key,
        ParameterErrorReason $reason,
    ): void {
        if (null === $this->errors) {
            return;
        }

        $this->errors->addParameterError($this->errorKeyPrefix . $key, $reason);
    }

    public function getSingleValueFromArray(
        string $key,
        bool $isRequired,
        RequestValueType $type,
        callable $validationFunction = null,
        null|string|float|int|bool|NotSetValue $defaultValue = null,
    ): null|string|float|int|bool|NotSetValue {
        $exists = array_key_exists($key, $this->array);

        if ($isRequired && !$exists) {
            $this->addError($key, ParameterErrorReason::createMissing());

            return null;
        }

        if (!$exists) {
            return $this->getDefaultValue($type, func_num_args() < 5 ? new NotSetValue() : $defaultValue);
        }

        $value = $this->getTypedValue($type, $this->array[$key]);

        if (null !== $validationFunction && !$validationFunction($value)) {
            $this->addError($key, ParameterErrorReason::createInvalid());

            return null;
        }

        return $value;
    }

    /**
     * @param array<string|float|int|bool> $defaultValue
     *
     * @return NotSetValue|string[]|float[]|int[]|bool[]
     */
    public function getArrayValueFromArray(
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
            $this->addError($key, ParameterErrorReason::createMissing());

            return $result;
        }

        if (!$exists) {
            return func_num_args() < 5 ? new NotSetValue() : $defaultValue;
        }

        if (!is_array($this->array[$key])) {
            $this->addError($key, ParameterErrorReason::createInvalid());

            return $result;
        }

        foreach ($this->array[$key] as $index => $value) {
            $typedValue = $this->getTypedValue($elementType, $value);

            if (null !== $elementValidationFunction && !$elementValidationFunction($typedValue)) {
                $this->addError($key . '.' . $index, ParameterErrorReason::createInvalid());
                continue;
            }

            $result[$index] = $typedValue;
        }

        if (null !== $validationFunction && !$validationFunction($result)) {
            $this->addError($key, ParameterErrorReason::createInvalid());

            return [];
        }

        return $result;
    }

    public function getDateFromArray(string $key, bool $isRequired, bool $allowMicroseconds = false): ?CarbonInterface
    {
        if (empty($this->array[$key]) && $isRequired) {
            $this->addError($key, ParameterErrorReason::createMissing());

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
            $this->addError($key, ParameterErrorReason::createInvalid());
        }

        return null;
    }

    /**
     * @param array<string|int>   $validValues
     */
    public function getEnumFromArray(string $key, array $validValues, bool $isRequired): null|string|int
    {
        if (empty($this->array[$key]) && $isRequired) {
            $this->addError($key, ParameterErrorReason::createMissing());

            return null;
        }

        if (!empty($this->array[$key])) {
            if (in_array($this->array[$key], $validValues)) {
                return $this->array[$key];
            }

            $this->addError($key, ParameterErrorReason::createInvalid());
        }

        return null;
    }

    /**
     * Returns TRUE if the specified date string is a valid date that matches the specified format
     */
    public function validateDateString(string $dateString, string $format): bool
    {
        try {
            $instance = CarbonImmutable::createFromFormat($format, $dateString);

            return $instance instanceof CarbonImmutable;
        } catch (InvalidArgumentException) {
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
        return match ((string)$type) {
            RequestValueType::TYPE_INT    => (int)$value,
            RequestValueType::TYPE_FLOAT  => (float)$value,
            RequestValueType::TYPE_STRING => (string)$value,
            RequestValueType::TYPE_BOOL   => (bool)$value,
            default                       => throw new InvalidArgumentException('Invalid type given'),
        };
    }
}
