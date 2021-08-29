<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Renderer;

use Slim\Error\AbstractErrorRenderer;
use Throwable;

class JsonErrorRenderer extends AbstractErrorRenderer
{

    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {

    }

}
