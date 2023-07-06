<?php

declare(strict_types=1);

namespace Blinq\Synth\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

class FileSystemValueObject implements Arrayable
{
    public function __construct(
        public array $files,
    ) {
    }

    public static function make(array $files): self
    {
        return new self($files);
    }

    public function toArray(): array
    {
        return $this->files;
    }
}
