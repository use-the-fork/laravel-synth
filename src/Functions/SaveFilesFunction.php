<?php

declare(strict_types=1);

namespace Blinq\Synth\Functions;

use Blinq\Synth\Exceptions\MissingFunctionParametersException;
use Blinq\Synth\Interfaces\FunctionInterface;
use Blinq\Synth\ValueObjects\AttachedFileValueObject;

class SaveFilesFunction extends BaseFunction implements FunctionInterface
{
    public function getName(): string
    {
        return 'save_files';
    }

    public function getDescription(): string
    {
        return 'Save the files in laravel. Use this method any time you create or update files.';
    }

    public function getFunctionJson(): array
    {
        return
            [
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'files' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'required' => [
                                    'name',
                                    'contents',
                                ],
                                'properties' => [
                                    'name' => [
                                        'type' => 'string',
                                        'description' => 'The FULL path/filename of the file, starting from the laravel base path. Ex: app/Models/Note.php',
                                    ],
                                    'contents' => [
                                        'type' => 'string',
                                        'description' => 'The WHOLE contents of the file with everything escaped for decoding and nothing truncated.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
    }

    public function doFunction(string $message): array
    {

        $attachedFiles = [];
        $jsonString = json_decode($this->fixSyntax($message), true);
        if (empty($jsonString['files'])) {
            dump(json_decode($this->fixSyntax($message), true));
            dump($this->fixSyntax($message));
            dd($message);
            throw MissingFunctionParametersException::make($this::class);
        }

        foreach ($jsonString['files'] as $file) {
            $name = $file['name'] ?? null;
            $contents = $file['contents'] ?? null;

            if ( ! $name && ! $contents) {
                continue;
            }

            // Normalize the file
            // Check if it has <?php at the start (add it)
            if (str($name)->endsWith('php') && ! str($contents)->startsWith('<?php')) {
                $contents = "<?php\n" . $contents;
            }

            if (str($name)->contains('blade.php')) {
                // Remove <?php
                $contents = str_replace('<?php', '', $contents);
            }

            $attachedFiles[$name] = AttachedFileValueObject::make($name, $contents, true);
        }

        return $attachedFiles;
    }
}
