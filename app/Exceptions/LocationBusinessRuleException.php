<?php

namespace App\Exceptions;

use Exception;

class LocationBusinessRuleException extends Exception
{
    /**
     * Create a new location business rule exception instance.
     */
    public function __construct(string $message = '', int $code = 422, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

