<?php

declare(strict_types=1);

namespace Blinq\Synth\Helpers;

use Blinq\Synth\ValueObjects\AttachedFileValueObject;

class FileService
{
    public static function writeFile(AttachedFileValueObject $file): int
    {
        $filePath = base_path($file->getFile());
        $directory = dirname($filePath);
        if ( ! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return file_put_contents($filePath, $file->getContent());
    }
}
