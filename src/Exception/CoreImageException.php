<?php

namespace ApertureLabo\CoreBundle\Exception;

use Exception;
use Throwable;

class CoreImageException extends Exception
{
    public function __construct(string $message, string $additionalInfo = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}