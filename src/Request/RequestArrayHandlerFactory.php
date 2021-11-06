<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Request;

use JetBrains\PhpStorm\Pure;
use Szemul\SlimErrorHandlerBridge\ParameterError\ParameterErrorCollectingInterface;

class RequestArrayHandlerFactory
{

    /**
     * @param array<string|mixed> $data
     *
     * @codeCoverageIgnore
     */
    #[Pure]
    public function getHandler(
        array $data,
        ?ParameterErrorCollectingInterface $errors = null,
        string $errorKeyPrefix = '',
    ): RequestArrayHandler {
        return new RequestArrayHandler($data, $errors, $errorKeyPrefix);
    }
}
