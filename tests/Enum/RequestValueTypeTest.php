<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Test\Enum;

use Szemul\SlimErrorHandlerBridge\Enum\RequestValueType;

class RequestValueTypeTest extends EnumTestAbstract
{

    public function getCreateValues(): array
    {
        return [
            ['createBool', RequestValueType::TYPE_BOOL],
            ['createFloat', RequestValueType::TYPE_FLOAT],
            ['createInt', RequestValueType::TYPE_INT],
            ['createString', RequestValueType::TYPE_STRING],
        ];
    }

    public function getTestClass(): string
    {
        return RequestValueType::class;
    }

}
