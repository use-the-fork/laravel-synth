<?php

namespace Blinq\Synth\Functions;

class BaseFunction
{
    /**
     * Fix some common errors in the output
     *
     * @param  string  $args The input string to fix syntax errors
     * @return string The fixed string
     */
    public function fixSyntax(string $args): string
    {
        $search = [
            '\\\\\\\\',
            '\\\\n',
            '\\\\\\"',
            '\\r\\n',
            '\r\n',
            PHP_EOL,
        ];

        $replace = [
            '\\\\',
            '\n',
            '\\"',
            '\n',
            '\n',
            '',
        ];

        $args = str_replace($search, $replace, $args);

        return $args;
    }
}
