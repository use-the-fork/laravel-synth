<?php

namespace Blinq\Synth\Functions;

use Blinq\Synth\Exceptions\MissingFunctionParametersException;
use Blinq\Synth\Interfaces\FunctionInterface;
use Blinq\Synth\ValueObjects\AttachedFileValueObject;

class NeedClassFunction extends BaseFunction implements FunctionInterface
{
    public function getName(): string
    {
        return 'need_class';
    }

    public function getDescription(): string
    {
        return 'Retrives the code of the specified classes.';
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
                                    'class' => [
                                        'type' => 'string',
                                        'description' => 'The full class of the file. Ex: app/Models/Note',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
    }

    public function doFunction(string $jsonString, array $attachedFiles): array
    {

        $jsonString = $this->fixSyntax($jsonString);
        $jsonString = json_decode($jsonString, true);
        if (empty($jsonString['files'])) {
            throw MissingFunctionParametersException::make($this::class);
        }

        $chatContent = [];
        foreach ($jsonString['files'] as $file) {
            $name = $file['name'] ?? null;
            $contents = $file['contents'] ?? null;

            if (! $name && ! $contents) {
                continue;
            }

            // Normalize the file
            // Check if it has <?php at the start (add it)
            if (str($name)->endsWith('php') && ! str($contents)->startsWith('<?php')) {
                $contents = "<?php\n".$contents;
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
