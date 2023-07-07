<?php

declare(strict_types=1);

namespace Blinq\Synth\Modules;

use Blinq\Synth\Controllers\ChatController;
use Blinq\Synth\Interfaces\ModuleInterface;
use PhpSchool\CliMenu\CliMenu;

/**
 * This file defines the base class for modules in the Chat application.
 * It provides common functionality for modules, such as registering, selecting, and accessing other modules.
 */
abstract class Module implements ModuleInterface
{
    public function __construct(public ChatController $synthController)
    {

    }

    abstract public function name(): string;

    abstract public function register(): string;

    abstract public function onSelect(CliMenu $menu): void;

    public function doCallback(CliMenu $menu): void
    {
        $menu->close();
        $this->onSelect($menu);
    }

    public function getModule($name): ModuleInterface
    {
        return $this->synthController->modules->get($name);
    }
}
