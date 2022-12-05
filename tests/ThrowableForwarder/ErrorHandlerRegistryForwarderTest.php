<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Test\ThrowableForwarder;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Szemul\ErrorHandler\ErrorHandlerRegistry;
use Szemul\SlimErrorHandlerBridge\ThrowableForwarder\ErrorHandlerRegistryForwarder;
use PHPUnit\Framework\TestCase;
use Throwable;

class ErrorHandlerRegistryForwarderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ErrorHandlerRegistry|MockInterface|LegacyMockInterface $errorHandlerRegistry;
    private ErrorHandlerRegistryForwarder                          $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->errorHandlerRegistry = Mockery::mock(ErrorHandlerRegistry::class);

        //@phpstan-ignore-next-line
        $this->sut = new ErrorHandlerRegistryForwarder($this->errorHandlerRegistry);
    }

    public function testForwardWithHttpException(): void
    {
        $exception = new HttpBadRequestException($this->getServerRequest());
        $this->sut->forward($exception);

        // Dummy assert
        $this->assertTrue(true);
    }

    public function testForwardWithServerSideHttpException(): void
    {
        $exception = new HttpInternalServerErrorException($this->getServerRequest());
        $this->expectExceptionForwarded($exception);
        $this->sut->forward($exception);
    }

    public function testForwardWithOtherException(): void
    {
        $exception = new RuntimeException();
        $this->expectExceptionForwarded($exception);
        $this->sut->forward($exception);
    }

    private function expectExceptionForwarded(Throwable $exception): void
    {
        //@phpstan-ignore-next-line
        $this->errorHandlerRegistry->shouldReceive('handleException')
            ->once()
            ->with($exception);
    }

    private function getServerRequest(): ServerRequestInterface|MockInterface|LegacyMockInterface
    {
        return Mockery::mock(ServerRequestInterface::class);
    }
}
