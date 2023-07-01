<?php

namespace Blinq\Synth\Prompts;

final class ModelPrompt extends Prompt
{
    public function __construct()
    {

        $this->loadFunctions();
        $this->loadUser();
    }

    /**
     * Get the stub file for the generator.
     */
    private function getStub(): string
    {
        $stub = 'model.stub';

        return $this->resolveStubPath($stub);
    }

    protected function loadFunctions(): void
    {
        // Load the functions from the data source (e.g., file, database, API)
        $this->functions = [
            'name' => 'save_files',
            'description' => 'Save the files in laravel',
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
                                    'description' => 'The full filename of the file, starting from the laravel base path. Ex: app/Models/Model.php',
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

    protected function loadUser(): void
    {
        // Load the user prompt to create the Model
        $this->user =
            [
                'role' => 'user',
                'content' => "Use below laravel models template to create the models:\n{$this->getStub()}\n--\nOutput the WHOLE file",
            ];
    }
}
