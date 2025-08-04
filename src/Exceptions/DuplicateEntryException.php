<?php

namespace App\Exceptions;

use Exception;

class DuplicateEntryException extends Exception
{
    public function __construct(string $message = "A record with this name already exists.", int $code = 409, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}