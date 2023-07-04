<?php

namespace Blinq\Synth;

use Blinq\Synth\Controllers\SynthController;
use Blinq\Synth\Exceptions\NotAModuleInterfaceException;
use Blinq\Synth\Interfaces\ModuleInterface;
use Blinq\Synth\Modules\Architect;
use Blinq\Synth\Modules\Attachments;
use Blinq\Synth\Modules\Files;
use Blinq\Synth\Modules\History;
use Blinq\Synth\Modules\Migrations;
use Blinq\Synth\Modules\Models;
use Blinq\Synth\Modules\Schema;
use Blinq\Synth\Modules\StartSession;

/**
 * This file is responsible for managing the modules in the Synth application.
 * It includes functionality to register, retrieve, and interact with modules.
 */
final class Modules
{
    public static array $moduleInstances = [
        Architect::class,
        Attachments::class,
        StartSession::class,
        Files::class,
        History::class,
        Migrations::class,
        Models::class,
        Schema::class,
    ];

    protected array $modules = [];

    protected SynthController $synthController;

    public function setSynthController()
    {
        $this->synthController = app(SynthController::class);
    }

    /**
     * Register modules by accepting their instances.
     */
    public function registerModules(array $moduleInstances = []): void
    {
        $moduleInstances = [...self::$moduleInstances, ...$moduleInstances];
        foreach ($moduleInstances as $moduleInstance) {
            if (is_string($moduleInstance) && class_exists($moduleInstance)) {
                $module = new $moduleInstance($this->synthController);
                if ($module instanceof ModuleInterface) {
                    $this->registerModule($module);
                } else {
                    throw NotAModuleInterfaceException::make($moduleInstance);
                }
            } else {
                throw NotAModuleInterfaceException::make($moduleInstance);
            }
        }
    }

    /**
     * Register a module instance.
     */
    public function registerModule(ModuleInterface $module): void
    {
        $this->modules[$module->name()] = [
            'name' => $module->name(),
            'module' => $module,
            'options' => $module->register(),
        ];
    }

    /**
     * Get a module instance by its name.
     */
    public function get(string $name): ?ModuleInterface
    {
        return $this->modules[$name]['module'] ?? null;
    }

    /**
     * Get all the options provided by registered modules.
     */
    public function getOptions(): array
    {
        return collect($this->modules)->flatMap(function ($module) {
            return $module['options'];
        })->toArray();
    }

    /**
     * Select an option for registered modules that support it.
     */
    public function select(?string $option = null): void
    {
        foreach ($this->modules as $module) {
            if ($module['options'][$option] ?? null) {
                $module['module']->onSelect($option);
            }
        }
    }
}
