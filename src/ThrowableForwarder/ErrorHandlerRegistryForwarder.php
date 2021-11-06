<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\ThrowableForwarder;

use Slim\Exception\HttpException;
use Szemul\ErrorHandler\ErrorHandlerRegistry;
use Throwable;

class ErrorHandlerRegistryForwarder implements ForwarderInterface
{
    public function __construct(protected ErrorHandlerRegistry $errorHandlerRegistry)
    {
    }

    public function forward(Throwable $exception): void
    {
        if (!($exception instanceof HttpException) || $exception->getCode() >= 500) {
            $this->errorHandlerRegistry->handleException($exception);
        }
    }
}
