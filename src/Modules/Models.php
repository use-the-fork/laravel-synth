<?php

namespace Blinq\Synth\Modules;

class Models extends Module
{
    public $attachments = [];

    public function name(): string
    {
        return 'Models';
    }

    public function register(): array
    {
        return [
            'models' => 'Generate models for your application.',
        ];
    }

    public function onSelect(?string $key = null): void
    {
        $this->synthController->synth->loadSystemMessage('models');

        $schema = include __DIR__.'/../Prompts/models.schema.php';

        if (! $this->synthController->modules->get('Attachments')->getAttachments('architecture')) {
            $this->synthController->cmd->error('You need to create an architecture first');

            return;
        }

        while (true) {
            $this->synthController->synth->chat('Please make model(s) about the architecture', [
                'stream' => true,
                'temperature' => 0,
                'function_call' => ['name' => 'save_files'],
                ...$schema,
            ]);

            $this->synthController->cmd->newLine(2);
            $this->synthController->cmd->comment("Press enter to accept and continue, type 'exit' to discard, or ask a follow up question.");
            $answer = $this->synthController->cmd->ask('You');

            if ($answer == 'exit') {
                break;
            }

            if (! $answer) {
                $this->synthController->synth->handleFunctionsForLastMessage();

                break;
            }
        }
    }
}
