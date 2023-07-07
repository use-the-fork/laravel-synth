<?php

declare(strict_types=1);

namespace Blinq\Synth\Modules;

use Blinq\Synth\Prompts\ChatPrompt;
use PhpSchool\CliMenu\CliMenu;

/**
 * This file is a module in the Chat application, specifically for handling chat interactions.
 * It provides functionality to chat with GPT and create/update files using the chat interface.
 */
class StartSession extends Module
{
    public function name(): string
    {
        return 'Chat Session';
    }

    public function register(): string
    {
        return '[Ch]at: Start a new chat session.';
    }

    public function onSelect(CliMenu $menu): void
    {
        $currentQuestion = 'How can I help?';
        $this->synthController->setPromptInterface(new ChatPrompt());
        $this->synthController->chat($currentQuestion);
    }
}
