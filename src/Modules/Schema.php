<?php

declare(strict_types=1);

namespace Blinq\Synth\Modules;

use Blinq\LLM\Entities\ChatMessage;
use Illuminate\Support\Facades\DB;

/**
 * This file is a module in the Chat application, specifically for handling chat interactions.
 * It provides functionality to chat with GPT and create/update files using the chat interface.
 */
class Schema extends Module
{
    public $schema = '';

    public function name(): string
    {
        return 'Schema';
    }

    public function register(): array
    {
        $this->synthController->mainMenu->on('show', function (): void {
            $this->notice();
        });

        return [
            'schema' => 'Drop the database schema into the conversation',
        ];
    }

    public function onSelect(?string $key = null): void
    {
        // Get all tables in the database
        $tables = collect(DB::select('SHOW TABLES'))
            ->reduce(function ($carry, $item) {
                $item = (array) $item;
                // First key
                $carry[] = $item[array_key_first($item)];

                return $carry;
            }, []);

        $output = "[table_schema]\n\n";

        foreach ($tables as $table) {
            $output .= "Table: {$table}\n\n";
            $columns = DB::select('SHOW COLUMNS FROM ' . $table);
            foreach ($columns as $column) {
                $column = (array) $column;
                $output .= '* ' . $column['Field'] . ' (' . $column['Type'] . ")\n";
            }
        }

        $output .= "[/table_schema]\n\n";

        $this->addToChatHistory($output);
    }

    public function addToChatHistory(string $string): void
    {
        $history = $this->synthController->synth->ai->getHistory();

        $found = false;
        /**
         * @var ChatMessage $message
         */
        foreach ($history as &$message) {
            if ('user' == $message->role && str($message->content)->contains('[table_schema]')) {
                $message->content = $string;
                $found = true;
            }
        }

        if ( ! $found) {
            $this->synthController->synth->ai->addHistory(new ChatMessage('user', $string));
            // $history = $this->cmd->synth->ai->getHistory();
        }

        $this->schema = $string;
    }

    public function notice(): void
    {
        if ($this->schema) {
            $this->synthController->cmd->info('Schema is attached to this conversation.');
            $this->synthController->cmd->newLine(2);
        }
    }
}
