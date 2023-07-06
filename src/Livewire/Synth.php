<?php

declare(strict_types=1);

namespace Blinq\Synth\Livewire;

use Blinq\Synth\Prompts\StartSessionPrompt;
use Blinq\Synth\Services\AiService;
use Blinq\Synth\ValueObjects\ChatMessageValueObject;
use Livewire\Component;

class Synth extends Component
{
    public string $chatText = '';

    public string $currentChat = '';

    public array $attachedFiles = [];

    public array $chatHistory = [];

    public bool $isRewind = false;

    protected $listeners = ['doAttachFile', 'doRemoveFile', 'doChat', 'doInputChange', 'doChatEdit'];

    public function doChat(): void
    {

        //pops the last 2 elements
        if (
            true === $this->isRewind
        ) {
            array_pop($this->chatHistory);
            array_pop($this->chatHistory);
        }

        $this->chatHistory[] = (array) ChatMessageValueObject::make(
            role: 'user',
            content: $this->chatText,
        );

        $aiService = new AiService(
            systemMessage: new StartSessionPrompt(),
            attachedFiles: $this->attachedFiles,
        );

        $response = $aiService->chat($this->chatText);

        $this->chatHistory[] = (array) ChatMessageValueObject::make(
            role: $response['message']['role'],
            content: $response['message']['content'],
        );

        $this->chatText = '';
    }

    public function doChatEdit(): void
    {
        $result = collect($this->chatHistory)->last(fn ($value) => 'user' == $value['role']);
        $this->chatText = $result['content'] ?? '';
        $this->isRewind = true;
    }

    public function doCommit(): void
    {
        $result = collect($this->chatHistory)->last(fn ($value) => 'user' == $value['role']);
        $this->chatText = $result['content'] ?? '';
        $this->isRewind = true;
    }

    public function doAttachFile(string $file): void
    {
        $this->attachedFiles[$file] = $file;
    }

    public function doRemoveFile(string $file): void
    {
        unset($this->attachedFiles[$file]);
    }

    public function render()
    {
        return view('synth::livewire.chat');
    }
}
