<?php

declare(strict_types=1);

namespace Blinq\Synth\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

class FunctionCallValueObject implements Arrayable
{
    public function __construct(
        public string $name,
        public string $parameters,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParameters(): string
    {
        return $this->parameters;
    }

    public static function make(string $name, string $parameters): self
    {
        return new self($name, $parameters);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'parameters' => $this->parameters,
        ];
    }
}
