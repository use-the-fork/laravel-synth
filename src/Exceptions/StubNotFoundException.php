<?php

declare(strict_types=1);

namespace Blinq\Synth\Exceptions;

use Exception;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;

class StubNotFoundException extends Exception implements ProvidesSolution
{
    /**
     * Convenient method to create an Exception statically.
     */
    public static function make(string $stub): static
    {
        return new static("Stub file '{$stub}' not found.");
    }

    /**
     * Get the solution to the exception.
     */
    public function getSolution(): Solution
    {
        return BaseSolution::create('Stub File is missing')
            ->setSolutionDescription('You should add the requested stub file the base stub directory.');
    }
}
