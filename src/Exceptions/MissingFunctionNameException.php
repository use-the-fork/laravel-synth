<?php

declare(strict_types=1);

namespace Blinq\Synth\Exceptions;

use Exception;

class MissingFunctionNameException extends Exception
{
    /**
     * Convenient method to create an Exception statically.
     */
    public static function make(string $stub): static
    {
        return new static("Stub file '{$stub}' not found.");
    }
}
