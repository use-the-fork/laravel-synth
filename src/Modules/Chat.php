<?php

namespace Blinq\Synth\Modules;

use Blinq\Synth\Prompts\ChatPrompt;

/**
 * This file is a module in the Synth application, specifically for handling chat interactions.
 * It provides functionality to chat with GPT and create/update files using the chat interface.
 */
class Chat extends Module
{
    public function name(): string
    {
        return 'Chat';
    }

    public function register(): array
    {
        return [
            'chat' => 'Chat with GPT',
        ];
    }

    public function onSelect(?string $key = null): void
    {

        $currentQuestion = 'How can I help you?';

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
