<?php

namespace Blinq\Synth\Commands;

use Blinq\Synth\Controllers\SynthController;
use Blinq\Synth\Functions;
use Illuminate\Console\Command;

/**
 * This file contains the main command for the Synth application.
 * It handles the execution of the command and manages the Synth, MainMenu, and Modules instances.
 */
class SynthCommand extends Command
{
    public $signature = 'synth';

    public $description = 'Synth is a Laravel tool that helps you generate code and perform various tasks in your Laravel application.';

    public function handle(): int
    {
        Functions::registerAll();

        $synthController = app(SynthController::class);
        $synthController->setSynthCommand($this);
        $synthController->modules->registerModules();

        return $synthController->mainMenu->handle();
    }
}
