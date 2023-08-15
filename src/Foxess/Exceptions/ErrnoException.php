<?php declare(strict_types=1);

namespace Foxess\Exceptions;

/**
 * Exception thrown, if response returns an errno code other that 0
 */
class ErrnoException extends Exception
{
    public function __construct($message, $errno)
    {
        parent::__construct("Error in API: $message", $errno);
    }
}