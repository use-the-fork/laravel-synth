<?php

declare(strict_types=1);

namespace Blinq\Synth\Exceptions;

use Exception;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;

class MissingOpenAIKeyException extends Exception implements ProvidesSolution
{
    /**
     * Convenient method to create an Exception statically.
     */
    public static function make(): static
    {
        return new static('OPENAI_KEY not set, please set it in your .env or config/synth.php');
    }

    /**
     * Get the solution to the exception.
     */
    public function getSolution(): Solution
    {
        return BaseSolution::create('OpenAI Key not set')
            ->setSolutionDescription('You should register your OpenAI key in your .env (OPENAI_KEY) or config/synth.php');
    }
}
