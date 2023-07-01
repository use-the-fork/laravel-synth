<?php

declare(strict_types=1);

namespace Blinq\Synth\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

class ChatMessageValueObject implements Arrayable
{
    public function __construct(
        public string $role,
        public string $content,
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public static function make(string $role, string $content): self
    {
        return new self($role, $content);
    }

    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
    }
}
