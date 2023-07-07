<?php

declare(strict_types=1);

namespace Blinq\Synth;

use Blinq\Synth\Controllers\ChatController;
use Blinq\Synth\Traits\WithHooks;
use PhpSchool\CliMenu\CliMenu;

class MainMenu
{
    use WithHooks;

    public $modules = [];

    protected ChatController $synthController;

    public function setSynthController(): void
    {
        $this->synthController = app(ChatController::class);
    }

    public function showTokenCount(): void
    {
        $tokens = $this->synthController->cmd->synth->estimateTokenCount();
        $history = $this->synthController->cmd->synth->ai->getHistory();

        if ($tokens > 0) {
            $this->synthController->cmd->info('Estimated token count: ' . $tokens);
            $this->synthController->cmd->info('Number of messages: ' . count($history));
            $this->synthController->cmd->newLine();
        }
    }

    public function logo(): string
    {
        $welcome = [
            '░░░░░░░ ░░    ░░ ░░░    ░░ ░░░░░░░░ ░░   ░░ ',
            '▒▒       ▒▒  ▒▒  ▒▒▒▒   ▒▒    ▒▒    ▒▒   ▒▒ ',
            '▒▒▒▒▒▒▒   ▒▒▒▒   ▒▒ ▒▒  ▒▒    ▒▒    ▒▒▒▒▒▒▒ ',
            '     ▓▓    ▓▓    ▓▓  ▓▓ ▓▓    ▓▓    ▓▓   ▓▓ ',
            '███████    ██    ██   ████    ██    ██   ██ ',
            '',
            'What do you want to do?',
        ];

        return implode(PHP_EOL, $welcome);
    }

    public function handle(): void
    {

        $synthController = app(ChatController::class);

        $menu = $this->synthController->cmd->menu($this->logo())
            ->setTitleSeparator('-')
            ->enableAutoShortcuts()
            ->setForegroundColour('green')
            ->setBackgroundColour('black');

        foreach ($synthController->modules->getOptions() as $module) {
            $menu->addItem($module['name'], function (CliMenu $menu) use ($module): void {
                $module['module']->doCallback($menu);
            });
        }

        // while (true) {
        $menu->open();
        //$synthController->getSessionInformation();

        //            if ('exit' === $option) {
        //                return Command::SUCCESS;
        //            }
        //}
    }
}
