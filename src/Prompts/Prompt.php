<?php

namespace Blinq\Synth\Prompts;

use Blinq\Synth\Exceptions\StubNotFoundException;
use Blinq\Synth\Interfaces\PromptInterface;
use Blinq\Synth\ValueObjects\ChatMessageValueObject;

class Prompt implements PromptInterface
{
    protected array $functions = [];

    protected ChatMessageValueObject $system;

    protected ChatMessageValueObject $user;

    public function getFunctions(): array
    {
        return $this->functions;
    }

    public function getSystem(): ChatMessageValueObject
    {
        return $this->system;
    }

    public function getUser(): ChatMessageValueObject
    {
        return $this->user;
    }

    /**
     * Resolve the fully-qualified path to the stub.
     */
    protected function resolveStubPath(string $stub): string
    {
        $stubDirectory = __DIR__.'/../../stubs/';

        $stubFilePath = $stubDirectory.$stub;

        if (file_exists($stubFilePath)) {
            return file_get_contents($stubFilePath);
        }

        throw StubNotFoundException::make("Stub file '{$stub}' not found.");
    }
}
