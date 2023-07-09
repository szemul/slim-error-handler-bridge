<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Test\Exception;

use Mockery;
use Psr\Http\Message\ServerRequestInterface;
use Szemul\RequestParameterErrorCollector\Enum\ParameterErrorReason;
use Szemul\SlimErrorHandlerBridge\Exception\HttpUnprocessableEntityException;
use PHPUnit\Framework\TestCase;

class HttpUnprocessableEntityExceptionTest extends TestCase
{
    public function testDebugInfo(): void
    {
        /** @var ServerRequestInterface $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $sut     = new HttpUnprocessableEntityException($request);

        $result = $sut->__debugInfo();

        $this->assertArrayHasKey('request', $result);
        $this->assertIsString($result['request']);
        $this->assertEquals('** Instance of ' . get_class($request), $result['request']);
    }

    public function testParameterHandling(): void
    {
        /** @var ServerRequestInterface $request */
        $request = Mockery::mock(ServerRequestInterface::class);
        $sut     = new HttpUnprocessableEntityException($request);

        $this->assertEmpty($sut->getParameterErrors());
        $this->assertFalse($sut->hasParameterErrors());

        $duplicate = ParameterErrorReason::DUPLICATE;
        $sut->addParameterError('test', $duplicate);

        $this->assertTrue($sut->hasParameterErrors());
        $this->assertSame(['test' => $duplicate], $sut->getParameterErrors());
    }
}
