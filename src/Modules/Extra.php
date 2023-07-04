<?php

namespace Blinq\Synth\Modules;

use Blinq\Synth\Controllers\SynthController;
use Blinq\Synth\Helpers\TokenService;
use Blinq\Synth\ValueObjects\ChatMessageValueObject;
use Illuminate\Support\Collection;
use League\HTMLToMarkdown\HtmlConverter;

/**
 * This file is a module in the Synth application, specifically for handling attachments.
 * It provides functionality to attach and view files, search and attach files, and manage attachments.
 */
final class Extra extends Module
{
    public const FILES_DELIMITER = '    => ';

    public Collection $availableFiles;

    public function name(): string
    {
        return 'Extras';
    }

    public function register(): array
    {

        $synthController = app(SynthController::class);

        return [
            'extra' => 'Attach documentation that the AI may need.',
        ];
    }

    public function onSelect(?string $key = null): void
    {
        $this->searchAndAttachFiles();
    }

    /**
     * Remove the <style> tag from the provided code while keeping the inside text.
     *
     * @param  string  $code The code to process.
     * @return string The processed code.
     */
    public function removeStyleTag($code)
    {
        // Find the opening and closing <style> tags.
        $startTag = '<style>';
        $endTag = '</style>';

        // Find the positions of the opening and closing tags.
        $startPos = strpos($code, $startTag);
        $endPos = strpos($code, $endTag);

        // If both tags are found, remove the <style> tag and its contents.
        if ($startPos !== false && $endPos !== false) {
            $startPos += strlen($startTag);

            return substr_replace($code, '', $startPos, $endPos - $startPos);
        }

        // If the <style> tag is not found, return the original code.
        return $code;
    }

    public function searchAndAttachFiles(): void
    {
        $this->synthController->cmd->info('Enter a URL to scrape the content and attach it to the chat.');
        $this->synthController->cmd->newLine();
        $this->synthController->cmd->line("exit   - Press enter or type 'exit' to discard");
        $this->synthController->cmd->line('view   - to view the current attachments');
        $this->synthController->cmd->line('clear  - to clear the current attachments');

        $converter = new HtmlConverter(['strip_tags' => true]);

        while (true) {

            $url = $this->synthController->cmd->ask('Enter a URL to Scrape:');
            $urlContent = file_get_contents($url);
            $markdown = $converter->convert($urlContent);

            dd($markdown);

            $confirm = $this->synthController->cmd->confirm('This would use '.TokenService::estimateTokenCount([ChatMessageValueObject::make('user', $markdown)]).' tokens. Would you like to strip down some of the information?', false);

            dd($markdown);

            $file = $this->synthController->cmd->anticipate('Search', function ($search) use (&$hasWildcard) {

                //Safety checks when clearing or viewing attachments
                if ($search === 'view') {
                    return ['view'];
                }

                if ($search === 'clear') {
                    return ['clear'];
                }

                if ($search === 'exit') {
                    return ['exit'];
                }

                if (! $search) {
                    return [];
                }
                if (str($search)->contains('=>')) {
                    return [];
                }

                $hasWildcard = str($search)->contains('*');

                $filesMatched = $this->availableFiles->filter(function ($file) use ($search) {
                    $pattern = str_replace('*', '.*', preg_quote($search, '/'));

                    return preg_match('/.*'.$pattern.'.*/i', $file);
                })->values();

                if ($hasWildcard) {
                    $file = $search.self::FILES_DELIMITER.$filesMatched->first().' | '.$filesMatched->count().' files found';
                } else {
                    $file = $search.self::FILES_DELIMITER.$filesMatched->first();
                }

                return [$file] ?? [];
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
                $file = (string) str($file)->afterLast(self::FILES_DELIMITER);
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
}
