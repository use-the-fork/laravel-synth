<?php

declare(strict_types=1);

namespace Blinq\Synth\Modules;

use Blinq\LLM\Entities\ChatMessage;

/**
 * This file is a module in the Chat application, specifically for handling application architecture.
 * It provides functionality to brainstorm and generate a new application architecture using GPT.
 */
class History extends Module
{
    public function name(): string
    {
        return 'History';
    }

    public function register(): array
    {
        return [
            'history' => 'Show the chat history',
        ];
    }

    public function onSelect(?string $key = null): void
    {
        $history = $this->synthController->synth->ai->getHistory();

        /**
         * @var ChatMessage $item
         */
        foreach ($history as $item) {
            $this->synthController->cmd->comment($item->role);
            $this->synthController->cmd->comment('----');
            $this->synthController->cmd->line($item->content ?? $item->function_call['name'] ?? '');
            $this->synthController->cmd->newLine();
            $this->synthController->cmd->newLine();
        }
    }
}
