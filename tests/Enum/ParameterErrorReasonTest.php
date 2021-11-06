<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Test\Enum;

use Szemul\SlimErrorHandlerBridge\Enum\ParameterErrorReason;

class ParameterErrorReasonTest extends EnumTestAbstract
{
    public function getCreateValues(): array
    {
        return [
            ['createMissing', ParameterErrorReason::MISSING],
            ['createDuplicate', ParameterErrorReason::DUPLICATE],
            ['createInvalid', ParameterErrorReason::INVALID],
            ['createMustBeEmpty', ParameterErrorReason::MUST_BE_EMPTY],
        ];
    }

    public function getTestClass(): string
    {
        return ParameterErrorReason::class;
    }
}
