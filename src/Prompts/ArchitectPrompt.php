<?php

namespace Blinq\Synth\Prompts;

class ArchitectPrompt extends Prompt
{
    public function __construct()
    {
        $this->loadFunctions();
        $this->loadSystem();
    }

    protected function loadFunctions(): void
    {
        // Load the functions from the data source (e.g., file, database, API)
        $this->functions = [

            'name' => 'save_migrations',
            'description' => 'Save the Laravel migrations',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'migrations' => [
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
                                    'description' => 'The name of the migration',
                                ],
                                'contents' => [
                                    'type' => 'string',
                                    'description' => 'The WHOLE contents of the Laravel migration file',
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
        $this->system = ['role' => 'system', 'content' => 'You are a laravel architect. Find out what the user wants to create. The output should be a ERD diagram with fields, and relations.'];
    }
}
