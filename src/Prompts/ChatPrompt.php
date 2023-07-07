<?php

declare(strict_types=1);

namespace Blinq\Synth\Prompts;

use Blinq\Synth\ValueObjects\ChatMessageValueObject;

class ChatPrompt extends Prompt
{
    public function __construct()
    {
        $this->loadSystem();
    }

    protected function loadSystem(): void
    {
        $instructions = implode("\n", [
            'You are a Laravel Version ' . app()->version() . ' architect inside an existing laravel application.',
            'Find out what the user wants to create and help them create it.',
        ]);

        $this->system = ChatMessageValueObject::make('system', $instructions);
    }
}
