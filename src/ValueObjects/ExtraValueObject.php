<?php

declare(strict_types=1);

namespace Blinq\Synth\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

class ExtraValueObject implements Arrayable
{
    public function __construct(
        public string $file,
        public string $content,
    ) {
    }

    public function getEctra(): string
    {
        return $this->file;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public static function make(string $file, string $content): self
    {
        return new self($file, $content);
    }

    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'content' => $this->content,
        ];
    }
}
