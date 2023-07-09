<?php

declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\ErrorHandlerFactory;

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Szemul\SlimAppBootstrap\ErrorHandlerFactory\ErrorHandlerFactoryInterface;
use Szemul\SlimErrorHandlerBridge\Handler\ErrorHandler;
use Szemul\SlimErrorHandlerBridge\Renderer\JsonErrorRenderer;
use Szemul\SlimErrorHandlerBridge\ThrowableForwarder\ForwarderInterface;

class ErrorHandlerFactory implements ErrorHandlerFactoryInterface
{
    public function getErrorHandler(ContainerInterface $container, App $app): SlimErrorHandler
    {
        $errorHandler = new ErrorHandler(
            $container->get(ForwarderInterface::class),
            $app->getCallableResolver(),
            $app->getResponseFactory(),
        );

        $jsonRenderer = $container->get(JsonErrorRenderer::class);
        $errorHandler->registerErrorRenderer('application/json', $jsonRenderer);

        return $errorHandler;
    }
}
