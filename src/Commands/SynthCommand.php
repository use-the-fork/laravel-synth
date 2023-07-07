<?php

declare(strict_types=1);

namespace Blinq\Synth\Commands;

use Blinq\Synth\Controllers\ChatController;
use Blinq\Synth\Functions;
use Illuminate\Console\Command;

/**
 * This file contains the main command for the Chat application.
 * It handles the execution of the command and manages the Chat, MainMenu, and Modules instances.
 */
class SynthCommand extends Command
{
    public $signature = 'synth';

    public $description = 'Chat is a Laravel tool that helps you generate code and perform various tasks in your Laravel application.';

    public function handle(): int
    {
        Functions::registerAll();

        $synthController = app(ChatController::class);
        $synthController->setSynthCommand($this);
        $synthController->modules->registerModules();

        return $synthController->mainMenu->handle();
    }
}
