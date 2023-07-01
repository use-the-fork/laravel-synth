<?php

namespace Blinq\Synth\Modules;

use Blinq\LLM\Entities\ChatMessage;
use Blinq\Synth\Controllers\SynthController;

/**
 * This file is a module in the Synth application, specifically for handling attachments.
 * It provides functionality to attach and view files, search and attach files, and manage attachments.
 */
final class Attachments extends Module
{
    public const FILES_DELIMITER = '    => ';

    public function name(): string
    {
        return 'Attachments';
    }

    public function register(): array
    {

        $synthController = app(SynthController::class);

        $synthController->mainMenu->on('show', function () {
            $this->notice();
        });

        return [
            'attach' => 'Attach one or more files to this conversation.',
        ];
    }

    public function onSelect(?string $key = null): void
    {
        if ($key === 'attach') {
            $this->searchAndAttachFiles();
        }
        if ($key === 'attach:view') {
            $this->viewAttachments();
        }
    }

    public function viewAttachments(): void
    {

        foreach ($this->synthController->getAttachedFiles() as $key => $x) {
            $this->synthController->cmd->comment($key);
            $this->synthController->cmd->comment('----');
            $this->synthController->cmd->getOutput()->writeln($x->getFormatted());
            $this->synthController->cmd->newLine();
        }

        if (count($this->synthController->getAttachedFiles()) === 0) {
            $this->synthController->cmd->comment('No attachments');
        }

        $this->synthController->cmd->newLine();
    }

    public function notice(): void
    {
        if (count($this->synthController->getAttachedFiles()) > 0) {
            $count = count($this->synthController->getAttachedFiles());
            $this->synthController->cmd->info("You have $count attachments:");
            echo collect($this->synthController->getAttachedFiles())->keys()->map(fn ($x) => '- '.basename($x))->implode(PHP_EOL);
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

            $file = $this->synthController->cmd->askWithCompletion('Search', function ($x) use (&$hasWildcard) {

                //Safety checks when clearing or viewing attachments
                if ($x === 'view') {
                    return ['view'];
                }

                if ($x === 'clear') {
                    return ['clear'];
                }

                if (! $x) {
                    return [];
                }
                if (str($x)->contains('=>')) {
                    return [];
                }

                $hasWildcard = str($x)->contains('*');
                $files = $this->search($x);

                return $files ?? [];
            });

            if ($file === 'view') {
                $this->viewAttachments();

                continue;
            }
            if ($file === 'clear') {
                $this->synthController->clearAttachedFiles();

                continue;
            }

            if (! $hasWildcard) {
                if (! $this->addAttachmentFromFile($file)) {
                    break;
                }
            } else {
                $query = (string) str($file)->before(self::FILES_DELIMITER);
                $files = $this->search($query);

                $addFilesChoice = $this->synthController->cmd->choice(
                    'Found '.count($files).' files',
                    ['all' => 'Add All Files', 'choose' => 'Choose which files to add'],
                    'all'
                );

                if ($addFilesChoice == 'choose') {
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

    public function search($search): array
    {
        $files = [];
        $limit = config('synth.search_limit', 10);
        $base = config('synth.file_base', base_path());
        $excludePattern = config('synth.search_exclude_pattern', '/vendor|storage|node_modules|build|.git|.env/i');
        $count = 0;

        /**
         * @var \SplFileInfo $file
         */
        foreach (files_in($base, $search, excludePattern: $excludePattern) as $file) {
            if ($file->isDir()) {
                continue;
            }
            $path = $file->getRealPath();
            // Make it relative to base_path
            $path = str_replace($base.'/', '', $path);

            $files[] = $search.self::FILES_DELIMITER.$path;

            $count++;

            if ($count >= $limit) {
                break;
            }
        }

        return $files;
    }

    public function addAttachmentFromFile($file): bool
    {
        $query = (string) str($file)->before(self::FILES_DELIMITER);
        $filename = (string) str($file)->after(self::FILES_DELIMITER);

        if ($query === 'exit') {
            return false;
        }

        if (! $filename) {
            return false;
        }

        try {
            $base = config('synth.file_base', base_path());
            $contents = file_get_contents($base.'/'.$filename);
        } catch (\Throwable $th) {
            $this->synthController->cmd->error("Could not find file $filename");

            return true;
        }

        $this->synthController->addAttachedFile($filename, $contents);

        return true;
    }

    public function addAttachmentFromMessage($key, ChatMessage $message)
    {
        $content = $message->content;
        $args = $message->function_call['arguments'] ?? '';

        $this->synthController->addAttachedFile($key, $content.$args);
    }
}
