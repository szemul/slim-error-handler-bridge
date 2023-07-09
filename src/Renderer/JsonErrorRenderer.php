<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Renderer;

use Slim\Error\AbstractErrorRenderer;
use Slim\Exception\HttpSpecializedException;
use Szemul\RequestParameterErrorCollector\ParameterErrorCollectingInterface;
use Throwable;

class JsonErrorRenderer extends AbstractErrorRenderer
{
    public const RESPONSE_FIELD_ERROR_CODE        = 'errorCode';
    public const RESPONSE_FIELD_ERROR_DESCRIPTION = 'errorDescription';
    public const RESPONSE_FIELD_PARAMS            = 'params';

    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        if ($exception instanceof HttpSpecializedException) {
            $errorCode    = $exception->getTitle();
            $errorDetails = $exception->getDescription();
        } else {
            $errorCode    = '500 - Internal error';
            $errorDetails = 'Unexpected condition encountered preventing server from fulfilling request.';
        }

        if ($exception instanceof ParameterErrorCollectingInterface) {
            $params = $exception->getParameterErrors();
        }

        $error = [
            self::RESPONSE_FIELD_ERROR_CODE        => $errorCode,
            self::RESPONSE_FIELD_ERROR_DESCRIPTION => $errorDetails,
        ];

        if (!empty($params)) {
            $error[self::RESPONSE_FIELD_PARAMS] = $params;
        }

        return (string) json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
