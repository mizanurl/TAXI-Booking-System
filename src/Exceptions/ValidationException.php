<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected array $errors;

    /**
     * Constructor for the ValidationException.
     *
     * @param string $message The exception message.
     * @param array $errors An associative array of validation errors, where keys are field names and values are arrays of error messages.
     * @param int $code The exception code.
     * @param \Throwable|null $previous The previous throwable used for the exception chaining.
     */
    public function __construct(string $message = "Validation failed.", array $errors = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * Get the validation errors.
     *
     * @return array An associative array of validation errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}