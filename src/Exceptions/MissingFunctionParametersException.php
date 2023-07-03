<?php

declare(strict_types=1);

namespace Blinq\Synth\Exceptions;

use Exception;

class MissingFunctionParametersException extends Exception
{
    /**
     * Convenient method to create an Exception statically.
     */
    public static function make(string $class): static
    {
        return new static("Parmeters where missing in '{$class}' class.");
    }
}
