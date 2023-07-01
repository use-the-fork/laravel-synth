<?php

namespace Blinq\Synth\Modules;

use Blinq\Synth\Controllers\SynthController;
use Blinq\Synth\Interfaces\ModuleInterface;

/**
 * This file defines the base class for modules in the Synth application.
 * It provides common functionality for modules, such as registering, selecting, and accessing other modules.
 */
abstract class Module implements ModuleInterface
{
    public function __construct(public SynthController $synthController)
    {

    }

    abstract public function name(): string;

    public function register(): array
    {
        return [];
    }

    public function onSelect(?string $key = null): void
    {

    }

    public function getModule($name): ModuleInterface
    {
        return $this->synthController->modules->get($name);
    }
}
