<?php

declare(strict_types=1);

namespace Blinq\Synth\Modules;

use Blinq\LLM\Entities\ChatMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpSchool\CliMenu\CliMenu;
use function Termwind\render;
use Throwable;

/**
 * This file is a module in the Chat application, specifically for handling attachments.
 * It provides functionality to attach and view files, search and attach files, and manage attachments.
 */
final class Attachments extends Module
{
    public const FILES_DELIMITER = '    => ';

    public Collection $availableFiles;

    public function name(): string
    {
        return 'Attachments';
    }

    public function register(): string
    {
        return 'Attach: Attach one or more files to this conversation.';
    }

    public function onSelect(CliMenu $menu): void
    {

        $this->availableFiles = collect($this->search());
        $this->searchAndAttachFiles();
        $menu->open();
    }

    public function viewAttachments(): void
    {

        foreach ($this->synthController->getAttachedFiles() as $file) {
            render("
                <div>
                    <div class='px-1 bg-green-600'>{$file->getFile()}</div>
                    <code>
                        {$file->getFormatted()}
                    </code>
                </div>
            ");
        }

        if (0 === count($this->synthController->getAttachedFiles())) {
            $this->synthController->cmd->comment('No attachments');
        }

        $this->synthController->cmd->newLine();
    }

    public function notice(): void
    {
        if (count($this->synthController->getAttachedFiles()) > 0) {
            $count = count($this->synthController->getAttachedFiles());
            $this->synthController->cmd->info("You have {$count} attachments:");
            echo collect($this->synthController->getAttachedFiles())->keys()->map(fn ($x) => '- ' . basename($x))->implode(PHP_EOL);
            $this->synthController->cmd->newLine(2);
        }
    }

    public function searchAndAttachFiles(): void
    {
        $this->synthController->cmd->info('Type something to search for a file to attach');
        $this->synthController->cmd->line("Search and end with '*' to include all matching files");
        $this->synthController->cmd->newLine();
        $this->synthController->cmd->line("exit   - Press enter or type 'exit' to discard");
        $this->synthController->cmd->line('view   - to view the current attachments');
        $this->synthController->cmd->line('clear  - to clear the current attachments');

        while (true) {
            $hasWildcard = false;

            $file = $this->synthController->cmd->anticipate('Search', function ($search) use (&$hasWildcard) {

                //Safety checks when clearing or viewing attachments
                if ('view' === $search) {
                    return ['view'];
                }

                if ('clear' === $search) {
                    return ['clear'];
                }

                if ('exit' === $search) {
                    return ['exit'];
                }

                if ( ! $search) {
                    return [];
                }
                if (str($search)->contains('=>')) {
                    return [];
                }

                $hasWildcard = str($search)->contains('*');

                $filesMatched = $this->availableFiles->filter(function ($file) use ($search) {
                    $pattern = str_replace('*', '.*', preg_quote($search, '/'));

                    return preg_match('/.*' . $pattern . '.*/i', $file);
                })->values();

                if ($hasWildcard) {
                    $file = $search . self::FILES_DELIMITER . $filesMatched->first() . ' | ' . $filesMatched->count() . ' files found';
                } else {
                    $file = $search . self::FILES_DELIMITER . $filesMatched->first();
                }

                return [$file] ?? [];
            });

            if ('view' === $file) {
                $this->viewAttachments();

                continue;
            }
            if ('clear' === $file) {
                $this->synthController->clearAttachedFiles();

                continue;
            }

            if ( ! $hasWildcard) {
                $file = (string) str($file)->afterLast(self::FILES_DELIMITER);
                if ( ! $this->addAttachmentFromFile($file)) {
                    break;
                }
            } else {
                $query = (string) str($file)->before(self::FILES_DELIMITER);
                $files = $this->search($query);

                $addFilesChoice = $this->synthController->cmd->choice(
                    'Found ' . count($files) . ' files',
                    ['all' => 'Add All Files', 'choose' => 'Choose which files to add'],
                    'all'
                );

                if ('choose' == $addFilesChoice) {
                    foreach ($files as $count => $file) {

                        $fileCount = $count + 1;
                        $fileName = (string) str($file)->after(self::FILES_DELIMITER);

                        if ($this->synthController->cmd->confirm("File {$fileCount}: {$fileName}", true)) {
                            $this->addAttachmentFromFile($file);
                        }
                    }
                } else {
                    foreach ($files as $file) {
                        $this->addAttachmentFromFile($file);
                    }
                }
            }

        }
    }

    public function search(): array
    {
        $base = config('synth.file_base', base_path());
        $excludePattern = config('synth.search_exclude_pattern', ['/vendor', '/storage', '/node_modules', '/build', '.git', '.env']);

        return collect(glob("{$base}/{,*/,*/*/,*/*/*/,*/*/*/*/,*/*/*/*/*/,*/*/*/*/*/*/,*/*/*/*/*/*/*/,*/*/*/*/*/*/*/*/*/*/}*.*", GLOB_BRACE))->filter(fn ($file) => ! Str::contains($file, $excludePattern, true))->values()->toArray();
    }

    public function addAttachmentFromFile($file): bool
    {
        $query = (string) str($file)->before(self::FILES_DELIMITER);
        $filename = (string) str($file)->after(self::FILES_DELIMITER);

        if ('exit' === $query) {
            return false;
        }

        if ( ! $filename) {
            return false;
        }

        try {
            $contents = file_get_contents($filename);
        } catch (Throwable $th) {
            $this->synthController->cmd->error("Could not find file {$filename}");

            return true;
        }

        $this->synthController->addAttachedFile($filename, $contents);

        return true;
    }

    public function addAttachmentFromMessage($key, ChatMessage $message): void
    {
        $content = $message->content;
        $args = $message->function_call['arguments'] ?? '';

        $this->synthController->addAttachedFile($key, $content . $args);
    }
}
