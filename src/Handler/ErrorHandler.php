<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Handler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Slim\Interfaces\CallableResolverInterface;
use Szemul\ErrorHandler\ErrorHandlerRegistry;
use Throwable;

class ErrorHandler extends SlimErrorHandler
{
    public function __construct(
        protected ErrorHandlerRegistry $errorHandlerRegistry,
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($callableResolver, $responseFactory, $logger);
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
    ): ResponseInterface {
        $this->errorHandlerRegistry->handleException($exception);

        return parent::__invoke(
            $request,
            $exception,
            $displayErrorDetails,
            $logErrors,
            $logErrorDetails,
        );
    }
}
