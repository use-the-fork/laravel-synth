<?php

declare(strict_types=1);

namespace Blinq\Synth\Livewire;

use Blinq\Synth\Helpers\FileService;
use Blinq\Synth\ValueObjects\AttachedFileValueObject;
use Livewire\Component;

class ApproveWrite extends Component
{
    public string $file = '';

    public string $prettyContent = '';

    public string $content = '';

    protected $listeners = ['loadApproveFile', 'doApproveFile'];

    public function loadApproveFile(array $file): void
    {

        $this->file = $file['file'];

        $hl = new \Highlight\Highlighter();
        $hl->setAutodetectLanguages(['php', 'js']);

        $this->content = str($file['content'])->replace('\n', PHP_EOL)
            ->replace('\t', '    ')
            ->replace('\\\\', '\\')
            ->__toString();

        $highlighted = $hl->highlightAuto($this->content);

        $this->prettyContent = "<pre><code class=\"hljs {$highlighted->language}\">" . $highlighted->value . '</code></pre>';
        $this->emit('doShowApproveFile');
    }

    public function doApproveFile(): void
    {
        FileService::writeFile(
            AttachedFileValueObject::make(
                file: $this->file,
                content: $this->content,
                modified: true
            )
        );

        $this->emit('doHideApproveFile');
    }

    public function render()
    {
        return view('synth::livewire.approve-write');
    }
}
