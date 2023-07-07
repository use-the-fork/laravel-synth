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

    public array $system = [
        'model' => '',
        'tokens' => 0,
        'percent' => 0,
        'files' => 0,
    ];

    public array $attachedFiles = [];

    public array $chatHistory = [];

    public bool $isRewind = false;

    public bool $isLoading = false;

    protected $listeners = ['doAttachFile', 'doRemoveFile', 'doChat', 'doChatEdit', 'doChatCommand'];

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

        $this->system['model'] = $response['model'];
        $this->system['tokens'] = $response['total_tokens'];

        $modal = collect(config('synth.models.chat'))->first(function ($hit) use ($response) {
            if ($hit['name'] == $response['model']) {
                return true;
            }
        });

        if (
            ! empty($modal)
        ) {
            $this->system['percent'] = round($response['total_tokens'] / $modal['max_tokens'] * 100, 2);
        }

        $this->system['files'] = count($this->attachedFiles);

        $this->chatText = '';
        $this->setLoading(false);
    }

    public function doChatCommand(string $command): void
    {
        if ( ! $this->isLoading) {
            $this->setLoading(true);
            $this->emit($command);
        }
    }

    public function setLoading(bool $loading): void
    {
        $this->isLoading = $loading;
    }

    public function doChatEdit(): void
    {
        $result = collect($this->chatHistory)->last(fn ($value) => 'user' == $value['role']);
        $this->chatText = $result['content'] ?? '';
        $this->isRewind = true;
        $this->setLoading(false);
    }

    public function doCommit(): void
    {
        $result = collect($this->chatHistory)->last(fn ($value) => 'user' == $value['role']);
        $this->chatText = $result['content'] ?? '';
        $this->isRewind = true;
        $this->setLoading(false);
    }

    public function doAttachFile(string $file): void
    {
        $this->attachedFiles[$file] = $file;
        $this->system['files'] = count($this->attachedFiles);
    }

    public function doRemoveFile(string $file): void
    {
        unset($this->attachedFiles[$file]);
        $this->system['files'] = count($this->attachedFiles);
    }

    public function render()
    {

        return view('synth::livewire.chat');
    }
}
