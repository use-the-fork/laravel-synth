<?php

declare(strict_types=1);

namespace Blinq\Synth\Interfaces;

interface FunctionInterface
{
    public function getName(): string;

    public function getDescription(): string;

    public function getFunctionJson(): array;

    public function doFunction(string $jsonString): array;
}
