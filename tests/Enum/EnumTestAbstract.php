<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Test\Enum;

use Emul\Enum\EnumAbstract;
use PHPUnit\Framework\TestCase;

abstract class EnumTestAbstract extends TestCase
{
    /**
     * @dataProvider getCreateValues
     */
    public function testCreate(string $methodName, string $expectedValue): void
    {
        /** @var EnumAbstract $sut */
        $sut = call_user_func($this->getTestClass() . '::' . $methodName);

        $this->assertSame($expectedValue, (string)$sut);
    }

    /** @return array[] */
    abstract public function getCreateValues(): array;

    abstract public function getTestClass(): string;
}
