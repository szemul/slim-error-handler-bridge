<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\ThrowableForwarder;

use Throwable;

interface ForwarderInterface
{
    public function forward(Throwable $exception): void;
}
