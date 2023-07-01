<?php

namespace Blinq\Synth\Modules;

use Blinq\Synth\Controllers\SynthController;

/**
 * This file is a module in the Synth application, specifically for handling file operations.
 * It provides functionality to write files to the filesystem, manage unwritten files, and clear files.
 */
class Files extends Module
{
    public array $files = [];

    public function name(): string
    {
        return 'Files';
    }

    public function register(): array
    {
        $synthController = app(SynthController::class);
        $synthController->mainMenu->on('show', function () {
            $this->notice();
        });

        return [
            'write' => 'Write files to the filesystem',
        ];
    }

    public function notice()
    {
        if (count($this->files) > 0) {
            $count = count($this->files);
            $this->synthController->cmd->info("You have $count unwritten files:");
            echo collect($this->files)->keys()->map(fn ($x) => '- '.$x)->implode(PHP_EOL);
            $this->synthController->cmd->newLine(2);
        }
    }

    public function onSelect(?string $key = null): void
    {
        $this->write();
    }

    public function write()
    {
        $this->synthController->cmd->info('Writing files to the filesystem...');
        $this->synthController->cmd->newLine();

        $base = config('synth.file_base', base_path());

        foreach ($this->files as $file => $contents) {
            $basename = basename($file);

            $this->synthController->cmd->comment($file);
            $this->synthController->cmd->comment('----');
            $this->synthController->cmd->line($contents);

            $fullFile = $base.'/'.$file;

            $fileExists = file_exists($fullFile);

            if ($this->synthController->cmd->confirm("Write $basename?".($fileExists ? ' (File already exists)' : ''), ! $fileExists)) {
                $file = $this->synthController->cmd->askWithCompletion('Write path', [$file], $file);

                if ($file) {
                    $file = $base.'/'.$file;
                    $directory = dirname($file);

                    if (! is_dir($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    file_put_contents($file, $contents);

                    $this->synthController->cmd->info("Written $file");
                }
            }
        }

        $this->clearFiles();
        $this->synthController->cmd->info('Done!');
        $this->synthController->cmd->newLine();
    }

    public function addFile($name, $contents)
    {
        $this->files[$name] = $contents;
    }

    public function removeFile($name)
    {
        unset($this->files[$name]);
    }

    public function clearFiles()
    {
        $this->files = [];
    }
}
