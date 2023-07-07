<?php

declare(strict_types=1);

namespace Blinq\Synth\Modules;

/**
 * This file is a module in the Chat application, specifically for handling the generation of migrations.
 * It provides functionality to register, select, and refine migrations based on the architecture.
 */
class Migrations extends Module
{
    public function name(): string
    {
        return 'Migrations';
    }

    public function register(): array
    {
        return [
            'migrations' => 'Generate migrations for your application.',
        ];
    }

    public function onSelect(?string $key = null): void
    {
        $this->synthController->synth->loadSystemMessage('migrations');

        $schema = include __DIR__ . '/../Prompts/migrations.schema.php';

        if ( ! $this->synthController->modules->get('Attachments')->getAttachments('architecture')) {
            $this->synthController->cmd->error('You need to create an architecture first');

            return;
        }

        while (true) {
            $this->synthController->synth->chat('Please make migration(s)', [
                'temperature' => 0,
                'function_call' => ['name' => 'save_migrations'],
                ...$schema,
            ]);

            $this->synthController->cmd->newLine();
            $this->synthController->cmd->info("Press enter to accept and continue, type 'exit' to discard, or ask a follow up question.");
            $answer = $this->synthController->cmd->ask('You');

            if ('exit' == $answer) {
                break;
            }

            if ( ! $answer) {
                $this->synthController->synth->handleFunctionsForLastMessage();

                break;
            }
        }
    }
}
