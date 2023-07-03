<?php

namespace Blinq\Synth\Modules;

use Blinq\Synth\Prompts\ChatPrompt;

/**
 * This file is a module in the Synth application, specifically for handling chat interactions.
 * It provides functionality to chat with GPT and create/update files using the chat interface.
 */
class Make extends Module
{
    public function name(): string
    {
        return 'Make';
    }

    public function register(): array
    {
        return [
            'make' => 'Create or update any file by asking',
        ];
    }

    public function onSelect(?string $key = null): void
    {

        $currentQuestion = 'What should I make?';

        $this->synthController->setPromptInterface(new ChatPrompt());

        while (true) {
            $answer = $this->synthController->cmd->ask($currentQuestion);

            if ($answer == 'exit' || ! $answer) {
                break;
            }

            $this->synthController->chat($answer);
            $this->synthController->synth->handleFunctionsForLastMessage();

            $this->synthController->cmd->newLine(2);
            $this->synthController->cmd->comment("Press enter to accept and continue, type 'exit' to discard, or ask a follow up question.");
            $currentQuestion = 'You';
        }
    }
}
