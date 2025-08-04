<?php

namespace App\Exceptions;

use Exception;

class MethodNotAllowedException extends Exception
{
    public function __construct(string $message = "Method not allowed.", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}