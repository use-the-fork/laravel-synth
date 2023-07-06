<?php

declare(strict_types=1);

namespace Blinq\Synth\Livewire;

use Blinq\Synth\Services\FileSystemService;
use Illuminate\Support\Str;
use Livewire\Component;

class AttachFiles extends Component
{
    public string $searchTerm = '';

    public array $foundFiles = [];

    public array $fileSystem = [];

    public array $attachedFiles = [];

    protected $listeners = ['doAttachFile', 'doRemoveFile'];

    public function mount(): void
    {
        $this->fileSystem = FileSystemService::getFiles();
    }

    public function render()
    {
        $this->foundFiles = collect($this->fileSystem)->filter(fn ($file) => Str::contains($file, $this->searchTerm, true))->toArray();

        return view('synth::livewire.attach-files');
    }

    public function doAttachFile(string $file): void
    {
        $this->attachedFiles[$file] = $file;
    }

    public function doRemoveFile(string $file): void
    {
        unset($this->attachedFiles[$file]);
    }
}
