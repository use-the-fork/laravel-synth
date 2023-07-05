<?php

declare(strict_types=1);

namespace Blinq\Synth\Interfaces;

use PhpSchool\CliMenu\CliMenu;

interface ModuleInterface
{
    /**
     * Get the name of the module.
     */
    public function name(): string;

    /**
     * Register the module and return its option for the menu.
     */
    public function register(): string;

    /**
     * Perform actions when a specific option is selected.
     */
    public function onSelect(CliMenu $menu): void;
}
