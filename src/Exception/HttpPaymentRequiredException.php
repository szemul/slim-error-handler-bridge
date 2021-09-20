<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Exception;

use Slim\Exception\HttpSpecializedException;

class HttpPaymentRequiredException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 402;

    /**
     * @var string
     */
    protected $message = 'Payment required.';

    protected $title       = '402 Payment required';
    protected $description = 'Payment is required to use this functionality.';
}
