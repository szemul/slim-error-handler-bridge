<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Exception;

class HttpPaymentRequiredException
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
