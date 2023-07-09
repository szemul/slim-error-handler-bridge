<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Bootstrap;

use Psr\Container\ContainerInterface;
use Szemul\Bootstrap\BootstrapInterface;
use Szemul\ErrorHandler\ErrorHandlerRegistry;
use Szemul\ErrorHandler\Handler\ErrorHandlerInterface;
use Szemul\ErrorHandler\ShutdownHandlerRegistry;

class ErrorHandlingBootstrap implements BootstrapInterface
{
    /** @var ErrorHandlerInterface[] */
    protected array $errorHandlers;

    public function __construct(ErrorHandlerInterface ...$errorHandlers)
    {
        $this->errorHandlers = $errorHandlers;
    }

    public function __invoke(ContainerInterface $container): void
    {
        /** @var ErrorHandlerRegistry $errorHandlerRegistry */
        $errorHandlerRegistry = $container->get(ErrorHandlerRegistry::class);
        /** @var ShutdownHandlerRegistry $shutdownHandlerRegistry */
        $shutdownHandlerRegistry = $container->get(ShutdownHandlerRegistry::class);

        $errorHandlerRegistry->register();
        $shutdownHandlerRegistry->register();
        $shutdownHandlerRegistry->addShutdownHandler($errorHandlerRegistry);

        foreach ($this->errorHandlers as $errorHandler) {
            $errorHandlerRegistry->addErrorHandler($errorHandler);
        }
    }
}
