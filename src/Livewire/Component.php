<?php

declare(strict_types=1);

namespace Blinq\Synth\Livewire;

use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public bool $isLoading = false;

    protected $listeners = ['doCommand'];

    public function setLoading(bool $loading): void
    {
        $this->isLoading = $loading;
    }

    public function doCommand(array $args): void
    {
        if ( ! $this->isLoading) {
            $this->setLoading(true);
            $this->emit($args['command'], $args['payload']);
        }
    }
}
