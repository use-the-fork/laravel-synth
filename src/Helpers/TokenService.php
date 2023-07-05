<?php

declare(strict_types=1);

namespace Blinq\Synth\Helpers;

use Blinq\Synth\ValueObjects\ChatMessageValueObject;

abstract class TokenService
{
    abstract public static function getModalToUse($messages): array;

    public static function estimateTokenCount($messages): int
    {
        $wordCount = collect($messages)
            ->reduce(fn ($carry, ChatMessageValueObject $item) => $carry + (mb_strlen($item->getContent()) / 4), 0);

        return (int) floor($wordCount * 1);
    }
}
