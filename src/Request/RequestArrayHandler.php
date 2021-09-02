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
    protected function addError(
        ?ParameterErrorCollectingInterface $errors,
        string $key,
        ParameterErrorReason $reason,
        string $keyPrefix,
    ): void {
        if (null === $errors) {
            return;
        }

        $errors->addParameterError($keyPrefix . $key, $reason);
    }

    /** @param array<string,mixed> $array */
    public function getSingleValueFromArray(
        array $array,
        string $key,
        bool $isRequired,
        RequestValueType $type,
        ?ParameterErrorCollectingInterface $errors = null,
        string $errorKeyPrefix = '',
        callable $validationFunction = null,
        null|string|float|int|bool|NotSetValue $defaultValue = null,
    ): null|string|float|int|bool|NotSetValue {
        $exists = array_key_exists($key, $array);

        if ($isRequired && !$exists) {
            $this->addError($errors, $key, ParameterErrorReason::createMissing(), $errorKeyPrefix);

            return null;
        }

        if (!$exists) {
            return $this->getDefaultValue($type, func_num_args() < 8 ? new NotSetValue() : $defaultValue);
        }

        $value = $this->getTypedValue($type, $array[$key]);

        if (null !== $validationFunction && !$validationFunction($value)) {
            $this->addError($errors, $key, ParameterErrorReason::createInvalid(), $errorKeyPrefix);

            return null;
        }

        return $value;
    }

    /**
     * @param array<string,mixed>          $array
     * @param array<string|float|int|bool> $defaultValue
     *
     * @return NotSetValue|array<string|float|int|bool>
     */
    public function getArrayValueFromArray(
        array $array,
        string $key,
        bool $isRequired,
        RequestValueType $elementType,
        ?ParameterErrorCollectingInterface $errors = null,
        string $errorKeyPrefix = '',
        callable $validationFunction = null,
        array $defaultValue = [],
    ): array|NotSetValue {
        $result = [];

        $exists = array_key_exists($key, $array);

        if ($isRequired && !$exists) {
            $this->addError($errors, $key, ParameterErrorReason::createMissing(), $errorKeyPrefix);

            return $result;
        }

        if (!$exists) {
            return func_num_args() < 8 ? new NotSetValue() : $defaultValue;
        }

        if (!is_array($array[$key])) {
            $this->addError($errors, $key, ParameterErrorReason::createInvalid(), $errorKeyPrefix);

            return $result;
        }

        foreach ($array[$key] as $index => $value) {
            $typedValue = $this->getTypedValue($elementType, $value);

            if (null !== $validationFunction && !$validationFunction($typedValue)) {
                $this->addError($errors, $key, ParameterErrorReason::createInvalid(), $errorKeyPrefix);
            }

            $this->$key[$index] = $typedValue;
        }

        return $result;
    }

    /** @param array<string,mixed> $array */
    public function getDateFromArray(
        array $array,
        string $key,
        bool $isRequired,
        ?ParameterErrorCollectingInterface $errors = null,
        string $errorKeyPrefix = '',
        bool $allowMicroseconds = false,
    ): ?CarbonInterface {
        if (empty($array[$key]) && $isRequired) {
            $this->addError($errors, $key, ParameterErrorReason::createMissing(), $errorKeyPrefix);

            return null;
        }

        if (empty($array[$key])) {
            return null;
        }
        try {
            try {
                $date = CarbonImmutable::createFromFormat(CarbonInterface::ATOM, $array[$key])->setMicrosecond(0);
            } catch (InvalidFormatException $e) {
                if (!$allowMicroseconds) {
                    throw $e;
                }
                $date = CarbonImmutable::createFromFormat('Y-m-d\TH:i:s.uP', $array[$key]);
            }

            $date->setTimezone('UTC');
            return $date;
        } catch (InvalidArgumentException $e) {
            $this->addError($errors, $key, ParameterErrorReason::createInvalid(), $errorKeyPrefix);
        }

        return null;
    }

    /**
     * @param array<string,mixed> $array
     * @param array<string|int>   $validValues
     */
    public function getEnumFromArray(
        array $array,
        string $key,
        array $validValues,
        bool $isRequired,
        ?ParameterErrorCollectingInterface $errors = null,
        string $errorKeyPrefix = '',
    ): null|string|int {
        if (empty($array[$key]) && $isRequired) {
            $this->addError($errors, $key, ParameterErrorReason::createMissing(), $errorKeyPrefix);

            return null;
        }

        if (!empty($array[$key])) {
            if (in_array($array[$key], $validValues)) {
                return $array[$key];
            }

            $this->addError($errors, $key, ParameterErrorReason::createInvalid(), $errorKeyPrefix);
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
        switch ((string)$type) {
            case RequestValueType::TYPE_INT:
                return (int)$value;

            case RequestValueType::TYPE_FLOAT:
                return (float)$value;

            case RequestValueType::TYPE_STRING:
                return (string)$value;

            case RequestValueType::TYPE_BOOL:
                return (bool)$value;

            default:
                throw new InvalidArgumentException('Invalid type given');
        }
    }
}
