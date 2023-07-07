<?php

declare(strict_types=1);

namespace Blinq\Synth\Prompts;

use Blinq\Synth\Functions\SaveFilesFunction;
use Blinq\Synth\ValueObjects\ChatMessageValueObject;

class OptimizePrompt extends Prompt
{
    public function __construct(public string $file)
    {
        $this->loadSystem();
        $this->loadUser();
        $this->loadFunctions();
    }

    protected function loadSystem(): void
    {
        $instructions = implode("\n", [
            'You are a Laravel Version ' . app()->version() . ' architect inside an existing laravel application.',
        ]);

        $this->system = ChatMessageValueObject::make('system', $instructions);
    }

    protected function loadUser(): void
    {

        $instructions = collect([])
            ->push("Optimize the code in my {$this->file}.")
            ->push('respond with the optimized file using the save_files function.')
            ->merge(config('synth.global_instructions'))
            ->map(fn ($hit) => " - {$hit}")
            ->prepend('Instructions:')
            ->implode("\n");

        $this->user = ChatMessageValueObject::make('user', $instructions);
    }

    protected function loadFunctions(): void
    {

        $this->functions = [
            new SaveFilesFunction(),
        ];
    }
}
