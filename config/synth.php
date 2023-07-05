<?php

declare(strict_types=1);

// config for Blinq/Synth
return [
    // OpenAI api key
    'openai_key' => env('OPENAI_API_KEY'),
    // models to use for chat and completions
    'models' => [
        'chat' => [
            [
                'name' => 'gpt-3.5-turbo-0613',
                'max_tokens' => 4096,
            ],
            [
                'name' => 'gpt-3.5-turbo-16k-0613',
                'max_tokens' => 16384,
            ],
        ],
        'completions' => [
            [
                'name' => 'code-davinci-002',
                'max_tokens' => 8001,
            ],
        ],
    ],
    // instructions that will be used on all chat completions
    'global_instructions' => [
        '* Adhere to DRY principles',
    ],
    // The base path to search from
    'file_base' => base_path(),
    // The pattern to exclude for the file search
    'search_exclude_pattern' => ['/vendor', '/storage', '/node_modules', '/build', '.git', '.env'],
    // Whether to exclude the .gitignore file to also exclude files
    'search_exclude_gitignore' => true,
];
