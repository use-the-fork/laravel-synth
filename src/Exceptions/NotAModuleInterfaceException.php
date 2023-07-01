<?php

declare(strict_types=1);

namespace Blinq\Synth\Exceptions;

use Exception;

class NotAModuleInterfaceException extends Exception
{
    /**
     * Convenient method to create an Exception statically.
     */
    public static function make(string $moduleInstance): static
    {
        return new static("'{$moduleInstance}' does not exsits or does not extend the ModuleInterface.");
    }
}
