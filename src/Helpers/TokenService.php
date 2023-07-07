<?php

declare(strict_types=1);

namespace Blinq\Synth\Helpers;

use Blinq\Synth\ValueObjects\ChatMessageValueObject;

abstract class TokenService
{
    abstract public static function getModalToUse(array $message): array;

    public static function estimateTokenCount(array $message): int
    {
        $wordCount = collect($message['messages'])
            ->reduce(fn ($carry, ChatMessageValueObject $item) => $carry + (mb_strlen($item->getContent()) / 4), 0) +
            collect($message['functions'])
                ->reduce(fn ($carry, array $item) => $carry + (mb_strlen(json_encode($item)) / 4), 0);

        return (int) floor($wordCount * 1);
    }
}
