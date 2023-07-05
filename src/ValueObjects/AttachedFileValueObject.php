<?php

declare(strict_types=1);

namespace Blinq\Synth\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

class AttachedFileValueObject implements Arrayable
{
    public function __construct(
        public string $file,
        public string $content,
        public bool $modified = false,
    ) {
    }

    public static function make(string $file, string $content, bool $modified): self
    {
        return new self($file, $content, $modified);
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getMinifiedContent(): string
    {
        $file = tmpfile();
        $path = stream_get_meta_data($file)['uri'];
        file_put_contents($path, $this->content);

        return php_strip_whitespace($path);
    }

    public function isModified(): bool
    {
        return $this->modified;
    }

    public function getFormatted(): string
    {

        return $this->content;
    }

    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'content' => $this->content,
            'is_modified' => $this->modified,
        ];
    }
}
