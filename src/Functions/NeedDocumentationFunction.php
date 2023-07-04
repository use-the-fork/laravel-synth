<?php

namespace Blinq\Synth\Functions;

use Blinq\Synth\Exceptions\MissingFunctionParametersException;
use Blinq\Synth\Interfaces\FunctionInterface;
use Blinq\Synth\ValueObjects\AttachedFileValueObject;

class NeedDocumentationFunction extends BaseFunction implements FunctionInterface
{
    public function getName(): string
    {
        return 'need_documentation';
    }

    public function getDescription(): string
    {
        return 'Asks the user for additional information about a third party class or method. Being implemented in the laravel application.';
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
                        'detail_for' => [
                            'type' => 'string',
                            'description' => 'The name of the class or method that needs documentation. Ex: OpenAI::client($yourApiKey);',
                        ],
                    ],
                    'required' => ['detail_for'],
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
