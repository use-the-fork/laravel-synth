<?php

namespace Blinq\Synth\Prompts;

use Blinq\Synth\ValueObjects\ChatMessageValueObject;

class ChatPrompt extends Prompt
{
    public function __construct()
    {
        $this->loadFunctions();
        $this->loadSystem();
    }

    protected function loadFunctions(): void
    {
        $this->functions =
            [
                'name' => 'save_files',
                'description' => 'Save the files in laravel. Use this method any time you create or update files.',
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
                                        'description' => 'The full path/filename of the file, starting from the laravel base path. Ex: app/Models/Note.php',
                                    ],
                                    'contents' => [
                                        'type' => 'string',
                                        'description' => 'The WHOLE contents of the file.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
    }

    protected function loadSystem(): void
    {
        $instructions = implode("\n", [
            'You are a laravel architect inside an existing laravel application.',
            'Instructions:',
            ' * Find out what the user wants to create and help them create it.',
            ' * If you are creating or updating a file use the save_files function (with FULL filename from the base_path)',
            ' * Do NOT truncate code',
        ]);

        $this->system = ChatMessageValueObject::make('system', $instructions);
    }
}
