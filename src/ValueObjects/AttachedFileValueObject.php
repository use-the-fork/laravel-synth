<?php

declare(strict_types=1);

namespace Blinq\Synth\ValueObjects;

use Highlight\Highlighter;
use Illuminate\Contracts\Support\Arrayable;

class AttachedFileValueObject implements Arrayable
{
    public function __construct(
        public string $file,
        public string $content,
        public bool $modified = false,
    ) {
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function isModified(): bool
    {
        return $this->modified;
    }

    public function getFormatted(): string
    {

        //TODO: Fix this to handle spans inside of spans
        $highlighter = new Highlighter();
        $highlighted = $highlighter->highlightAuto($this->content, ['php', 'javascript']);

        $highlighted = str($highlighted->value)
            ->replace('/<', '/ <')
            ->replace('span class="hljs-meta"', 'fg=#6c71c4')
            ->replace('span class="hljs-keyword"', 'fg=#6c71c4')
            ->replace('span class="hljs-template-tag"', 'fg=#6c71c4')
            ->replace('span class="hljs-type"', 'fg=#6c71c4')

            ->replace('span class="hljs-class"', 'fg=#b58900')
            ->replace('span class="hljs-title"', 'fg=#b58900')

            ->replace('span class="hljs-comment"', 'fg=#5a647e')

            ->replace('span class="hljs-attr"', 'fg=#cb4b16')
            ->replace('span class="hljs-link"', 'fg=#cb4b16')
            ->replace('span class="hljs-literal"', 'fg=#cb4b16')
            ->replace('span class="hljs-number"', 'fg=#cb4b16')
            ->replace('span class="hljs-symbol"', 'fg=#cb4b16')

            ->replace('span class="hljs-built_in"', 'fg=#2aa198')
            ->replace('span class="hljs-doctag"', 'fg=#2aa198')
            ->replace('span class="hljs-atrule"', 'fg=#2aa198')
            ->replace('span class="hljs-quote"', 'fg=#2aa198')
            ->replace('span class="hljs-regexp"', 'fg=#2aa198')

            ->replace('span class="hljs-params"', 'fg=#93a1a1')
            ->replace('span class="hljs-string"', 'fg=#93a1a1')
            ->replace('span class="hljs-keyword"', 'fg=#93a1a1')
            ->replace('span class="hljs-function"', 'fg=#93a1a1')

            ->replace('span class="hljs-bullet"', 'fg=#dc322f')
            ->replace('span class="hljs-deletion"', 'fg=#dc322f')
            ->replace('span class="hljs-name"', 'fg=#dc322f')
            ->replace('span class="hljs-selector-tag"', 'fg=#dc322f')
            ->replace('span class="hljs-template-variable"', 'fg=#dc322f')
            ->replace('span class="hljs-variable"', 'fg=#dc322f')

            ->replace('</span>', '</>')
            ->replace('&gt;', '>')
            ->replace('&lt;', '<')
            ->__toString();

        //the highlighter missed some title tags, so we'll add them here
        $pattern = '/<fg=#b58900>(.*?)(?=<fg=#b58900>|\s|$)/';
        $replacement = '<fg=#b58900>$1</>';

        return preg_replace($pattern, $replacement, $highlighted);

    }

    public static function make(string $file, string $content, bool $modified): self
    {
        return new self($file, $content, $modified);
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
