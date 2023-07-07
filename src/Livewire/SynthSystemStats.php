<?php

declare(strict_types=1);

namespace Blinq\Synth\Livewire;

use Livewire\Component;

class SynthSystemStats extends Component
{
    public string $model = '';

    public int $tokens = 0;

    public float $percent = 0;

    public int $files = 0;

    protected $listeners = ['doUpdateSystemStats'];

    public function doUpdateSystemStats(array $stats): void
    {
        if ( ! empty($stats['model'])) {
            $this->model = $stats['model'];
            $this->tokens = $stats['total_tokens'];

            $modal = collect(config('synth.models.chat'))->first(function ($hit) use ($stats) {
                if ($hit['name'] == $stats['model']) {
                    return true;
                }
            });

            if (
                ! empty($modal)
            ) {
                $this->percent = round($stats['total_tokens'] / $modal['max_tokens'] * 100, 2);
            }
        }
        if ( ! empty($stats['files'])) {
            $this->files = $stats['files'];
        }
    }

    public function render()
    {
        return view('synth::livewire.synth-system-stats');
    }
}
