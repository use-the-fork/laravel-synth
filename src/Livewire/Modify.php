<?php

declare(strict_types=1);

namespace Blinq\Synth\Livewire;

use Blinq\Synth\Prompts\OptimizePrompt;
use Blinq\Synth\Services\AiService;
use Blinq\Synth\Services\FileSystemService;
use Illuminate\Support\Str;

class Modify extends Component
{
    public string $searchTerm = '';

    public array $foundFiles = [];

    public array $fileSystem = [];

    protected $listeners = ['doCommand', 'doOptimize'];

    public function doOptimize(array $payload): void
    {
        $attachedFile = [];
        $prompt = new OptimizePrompt($payload['file']);

        $aiService = new AiService(
            promptInterface: $prompt,
            attachedFiles: $payload['file']
        );

        $response = $aiService->chat("Optimize the '{$payload['file']}' file. Use the save_files function to respond.");

        //write the files to the system
        $result = $prompt->doFunction($response['message']['function_call']);
        if ( ! empty($result)) {
            foreach ($result as $file) {
                $attachedFile = (array) $file;
            }
        }

        $this->emit('doUpdateSystemStats', [
            'model' => $response['model'],
            'total_tokens' => $response['total_tokens'],
            'files' => 0,
        ]);

        $this->emit('loadApproveFile', $attachedFile);

        $this->setLoading(false);
    }

    public function mount(): void
    {
        $this->fileSystem = FileSystemService::getFiles();
    }

    public function render()
    {
        if ( ! empty($this->searchTerm)) {
            $this->foundFiles = collect($this->fileSystem)->filter(fn ($file) => Str::contains($file, $this->searchTerm, true))->toArray();
        } else {
            $this->foundFiles = collect($this->fileSystem)->toArray();
        }

        return view('synth::livewire.modify');
    }
}
