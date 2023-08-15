<?php declare(strict_types=1);

namespace Foxess\Exceptions;

/**
 * Exception thrown, if API response has unexpected structure
 */
class ResponseException extends Exception
{
    public function __construct($message = null)
    {
        parent::__construct("Error in Response" . (isset($message) ?? ": $message"));
    }
}