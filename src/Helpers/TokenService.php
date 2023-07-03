<?php

namespace Blinq\Synth\Helpers;

use Blinq\Synth\ValueObjects\ChatMessageValueObject;

class TokenService
{
    public const SMALL_MODEL = 'gpt-3.5-turbo-0613';

    public const LARGE_MODEL = 'gpt-3.5-turbo-0613';

    public static function estimateTokenCount($messages): int
    {
        $wordCount = collect($messages)
            ->reduce(function ($carry, ChatMessageValueObject $item) {
                return $carry + strlen($item->getContent());
            }, 0);

        return (int) floor($wordCount * 0.75);
    }

    public static function getModalToUse($messages): array
    {
        $estimatedCount = self::estimateTokenCount($messages);
        if (
            $estimatedCount > 8000
        ) {
            return [
                'model' => self::LARGE_MODEL,
                'estimatedCount' => $estimatedCount,
            ];
        }

        return [
            'model' => self::SMALL_MODEL,
            'estimatedCount' => $estimatedCount,
        ];
    }
}
