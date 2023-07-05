<?php

declare(strict_types=1);

namespace Blinq\Synth\Modules;

use Blinq\Synth\Helpers\ChatTokenService;
use Blinq\Synth\ValueObjects\ChatMessageValueObject;
use Illuminate\Support\Collection;
use League\HTMLToMarkdown\HtmlConverter;
use PhpSchool\CliMenu\CliMenu;

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

    public function register(): string
    {
        return 'Extra: Attach documentation that the AI may need.';
    }

    public function onSelect(CliMenu $menu): void
    {
        $this->searchAndAttachFiles();
    }

    /**
     * Remove formatting tags from the provided code while keeping the inner text.
     *
     * @param  string  $code The code to process.
     * @return string The processed code.
     */
    public function removeFormatting($code)
    {
        // Remove span and div tags from the string using regular expressions
        //$modifiedString = preg_replace('/<(span|div)\b[^>]*>(.*?)<\/\1>/s', '', $code);
        $modifiedString = preg_replace('/<(span|div|a|img|)\b[^>]*>/', '', $code);
        $modifiedString = preg_replace('/<\/(span|div|a|img|)>/', '', $modifiedString);

        // Remove tabs from the modified string
        $modifiedString = str_replace("\t", '', $modifiedString);
        // Remove extra whitespace from the modified string

        // Return the modified string
        return $modifiedString;
    }

    public function searchAndAttachFiles(): void
    {
        $this->synthController->cmd->info('Enter a URL to scrape the content and attach it to the chat.');
        $this->synthController->cmd->newLine();
        $this->synthController->cmd->line("exit   - Press enter or type 'exit' to discard");
        $this->synthController->cmd->line('view   - to view the current attachments');
        $this->synthController->cmd->line('clear  - to clear the current attachments');

        $converter = new HtmlConverter([
            'strip_tags' => true,
            'hard_break' => true,
            'preserve_comments' => true,
        ]);

        while (true) {

            $url = $this->synthController->cmd->ask('Enter a URL to Scrape:');
            $urlContent = file_get_contents($url);
            $markdown = $converter->convert($this->removeFormatting($urlContent));

            $this->synthController->cmd->info('Stripping information via GPT');

            $command = [
                'You are a developer working on documentation.',
                'Instructions:',
                ' - 1: analyze the text (Figure 1) and remove any information that is not needed.',
                ' - 2: condense the text as much as possible. It is okay if grammar is not perfect as long as you understand what is being said.',
                ' - 3: Respond with ONLY the documentation that is needed. If there is no useful documentation then respond with ""',
                'Anything below this line is the documentation that needs to be condensed.--- (Figure 1)',
            ];

            //split the markdown into 2000 word chunks

            $toStore = [];
            foreach ($this->splitMarkdownIntoChunks($markdown) as $section) {
                $messages = [];
                $messages[] = ChatMessageValueObject::make('user', implode("\n", [...$command, $section]));
                $modal = ChatTokenService::getModalToUse($messages);
                $optimized = $this->synthController->openaiClient->chat()->create([
                    'model' => $modal['model'],
                    'messages' => $messages,
                ])->toArray();

                $toStore[] = $optimized['choices'][0]['message']['content'];
            }

            dd($toStore);

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

    /**
     * Splits the given Markdown content into sections no larger than 3000 words each.
     *
     * @param  string  $markdown The Markdown content to be split.
     * @return array An array of sections.
     */
    public function splitMarkdownIntoChunks($markdown)
    {
        // Split the Markdown into individual words
        $words = preg_split('/\s+/', $markdown);

        $chunks = [];
        $currentChunk = '';

        foreach ($words as $word) {
            // Append the current word to the current chunk
            $currentChunk .= $word . ' ';

            // Check if the current chunk has reached the word limit
            if (str_word_count($currentChunk) >= 3000) {
                // Add the current chunk to the list of chunks
                $chunks[] = trim($currentChunk);

                // Reset the current chunk
                $currentChunk = '';
            }
        }

        // Add the remaining chunk if it's not empty
        if ( ! empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }
}
