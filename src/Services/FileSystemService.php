<?php

declare(strict_types=1);

namespace Blinq\Synth\Services;

use Illuminate\Support\Str;

final class FileSystemService
{
    public static function getFiles(): array
    {
        $base = config('synth.file_base', base_path());
        $excludePattern = config('synth.search_exclude_pattern', ['/vendor', '/storage', '/node_modules', '/build', '.git', '.env']);

        $files = collect(glob("{$base}/{,*/,*/*/,*/*/*/,*/*/*/*/,*/*/*/*/*/,*/*/*/*/*/*/,*/*/*/*/*/*/*/,*/*/*/*/*/*/*/*/*/*/}*.*", GLOB_BRACE))
            ->filter(fn ($file) => ! Str::contains($file, $excludePattern, true))
            ->map(fn ($file) => Str::replace("{$base}/", '', $file))
            ->values()
            ->toArray();

        return $files;
    }
}
