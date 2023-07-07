<?php

declare(strict_types=1);

namespace Blinq\Synth\Helpers;

use Blinq\Synth\Exceptions\NoModelFoundException;

class ChatTokenService extends TokenService
{
    public static function getModalToUse($message, array $modalToExclude = []): array
    {
        $estimatedCount = self::estimateTokenCount($message);

        $modals = collect(config('synth.models.chat'))->sortBy('max_tokens')->toArray();
        //iterate through the modals and choose the one that fits the estimated count
        foreach ($modals as $modal) {
            if (
                ! in_array($modal['name'], $modalToExclude) &&
                $estimatedCount <= $modal['max_tokens']
            ) {
                return [
                    'model' => $modal['name'],
                    'estimatedCount' => $estimatedCount,
                    'percent_used' => round(($estimatedCount / $modal['max_tokens']) * 100, 2),
                ];
            }
        }

        throw NoModelFoundException::make();
    }
}
