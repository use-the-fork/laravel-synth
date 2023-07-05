<?php

declare(strict_types=1);

namespace Blinq\Synth\Exceptions;

use Exception;

class NoModelFoundException extends Exception
{
    /**
     * Convenient method to create an Exception statically.
     */
    public static function make(): static
    {
        return new static('Could not find a Modal that fits the context Length.');
    }
}
