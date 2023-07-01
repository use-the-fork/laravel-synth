<?php

namespace Blinq\Synth\Modules;

/**
 * This file is a module in the Synth application, specifically for handling application architecture.
 * It provides functionality to brainstorm and generate a new application architecture using GPT.
 */
class Architect extends Module
{
    public function name(): string
    {
        return 'Architect';
    }

    public function register(): array
    {
        return [
            'architect' => 'Brainstorm with GPT to generate a new application architecture.',
        ];
    }

    public function onSelect(?string $key = null): void
    {
        $this->synthController->synth->loadSystemMessage('architect');
        // $schema = include __DIR__ . "/../Prompts/architect.schema.php";
        $currentQuestion = 'What do you want to create?';
        $hasAnswered = false;

        while (true) {
            $input = $this->synthController->cmd->ask($currentQuestion);

            if ($input == 'exit') {
                break;
            }

            if (! $input) {
                if ($hasAnswered) {
                    $this->getModule('Attachments')->addAttachmentFromMessage('architecture', $this->synthController->synth->ai->getLastMessage());
                }

                break;
            }

            $this->synthController->synth->chat($input);
            $hasAnswered = true;

            $this->synthController->cmd->newLine();
            $this->synthController->cmd->info("Press enter to accept and continue, type 'exit' to discard, or ask a follow up question.");
            $currentQuestion = 'You';
        }
    }
}
