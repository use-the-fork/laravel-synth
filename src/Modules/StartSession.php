<?php

namespace Blinq\Synth\Modules;

use Blinq\Synth\Prompts\StartSessionPrompt;

/**
 * This file is a module in the Synth application, specifically for handling chat interactions.
 * It provides functionality to chat with GPT and create/update files using the chat interface.
 */
class StartSession extends Module
{
    public function name(): string
    {
        return 'Chat Session';
    }

    public function register(): array
    {
        return [
            'chat' => 'Start a new chat session.',
        ];
    }

    public function onSelect(?string $key = null): void
    {
        $currentQuestion = 'What should I make?';
        $this->synthController->setPromptInterface(new StartSessionPrompt());
        $this->synthController->chat($currentQuestion);
    }
}
