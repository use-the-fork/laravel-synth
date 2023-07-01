<?php

namespace Blinq\Synth\Interfaces;

interface ModuleInterface
{
    /**
     * Get the name of the module.
     */
    public function name(): string;

    /**
     * Register the module and return its options.
     */
    public function register(): array;

    /**
     * Perform actions when a specific option is selected.
     */
    public function onSelect(?string $key = null): void;
}
