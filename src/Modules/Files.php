<?php

declare(strict_types=1);

namespace Blinq\Synth\Modules;

use PhpSchool\CliMenu\CliMenu;
use function Termwind\{render};

/**
 * This file is a module in the Synth application, specifically for handling file operations.
 * It provides functionality to write files to the filesystem, manage unwritten files, and clear files.
 */
final class Files extends Module
{
    public function name(): string
    {
        return 'Files';
    }

    public function register(): string
    {
        return 'Write: Write files to the filesystem.';
    }

    public function onSelect(CliMenu $menu): void
    {
        $this->write();
    }

    public function write(): void
    {
        $this->synthController->cmd->info('Writing files to the filesystem...');
        $this->synthController->cmd->newLine();

        $base = config('synth.file_base', base_path());

        foreach ($this->synthController->attachedFiles as $file) {

            $basename = basename($file->getFile());

            render("
                <div>
                    <div class='px-1 bg-green-600'>{$file->getFile()}</div>
                    <code>
                        {$file->getFormatted()}
                    </code>
                </div>
            ");

            if (file_exists($file->getFile())) {
                $fullFile = $base . '/' . $file->getFile();
            } else {
                $fullFile = $base . '/' . $file->getFile();
            }

            $fileExists = file_exists($fullFile);

            if ($this->synthController->cmd->confirm("Write {$basename}?" . ($fileExists ? ' (File already exists)' : ''), ! $fileExists)) {
                $filePath = $this->synthController->cmd->askWithCompletion('Write path', [$fullFile], $fullFile);

                if ($filePath) {
                    $filePath = $base . '/' . $filePath;
                    $directory = dirname($filePath);

                    if ( ! is_dir($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    file_put_contents($filePath, $file->getContent());

                    $this->synthController->cmd->info("Written {$filePath}");
                }
            }
        }

        $this->synthController->cmd->info('Done!');
        $this->synthController->cmd->newLine();
    }
}
