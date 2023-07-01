<?php

namespace Blinq\Synth;

use Blinq\Synth\Controllers\SynthController;
use Blinq\Synth\Traits\WithHooks;
use Illuminate\Console\Command;

class MainMenu
{
    use WithHooks;

    protected SynthController $synthController;

    public $modules = [];

    public function setSynthController()
    {
        $this->synthController = app(SynthController::class);
    }

    public function showTokenCount()
    {
        $tokens = $this->synthController->cmd->synth->estimateTokenCount();
        $history = $this->synthController->cmd->synth->ai->getHistory();

        if ($tokens > 0) {
            $this->synthController->cmd->info('Estimated token count: '.$tokens);
            $this->synthController->cmd->info('Number of messages: '.count($history));
            $this->synthController->cmd->newLine();
        }
    }

    public function welcome(): void
    {
        $this->synthController->cmd->info('--------------------------------------------');
        $this->synthController->cmd->info('░░░░░░░ ░░    ░░ ░░░    ░░ ░░░░░░░░ ░░   ░░ ');
        $this->synthController->cmd->info('▒▒       ▒▒  ▒▒  ▒▒▒▒   ▒▒    ▒▒    ▒▒   ▒▒ ');
        $this->synthController->cmd->info('▒▒▒▒▒▒▒   ▒▒▒▒   ▒▒ ▒▒  ▒▒    ▒▒    ▒▒▒▒▒▒▒ ');
        $this->synthController->cmd->info('     ▓▓    ▓▓    ▓▓  ▓▓ ▓▓    ▓▓    ▓▓   ▓▓ ');
        $this->synthController->cmd->info('███████    ██    ██   ████    ██    ██   ██ ');
        $this->synthController->cmd->info('--------------------------------------------');
    }

    public function handle()
    {
        $this->welcome();

        $synthController = app(SynthController::class);
        $moduleOptions = $synthController->modules->getOptions();

        $options = [
            ...$moduleOptions,
            'exit' => 'Exit',
        ];

        while (true) {
            $this->dispatch('show');

            $option = $this->synthController->cmd->choice('What do you want to do?', $options);
            $synthController->modules->select($option);

            if ($option === 'exit') {
                return Command::SUCCESS;
            }
        }
    }
}
