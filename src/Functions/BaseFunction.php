<?php

declare(strict_types=1);

namespace Blinq\Synth\Functions;

class BaseFunction
{
    /**
     * Fix some common errors in the output
     *
     * @param  string  $args The input string to fix syntax errors
     * @return string The fixed string
     */
    public function fixSyntax(string $jsonString): string
    {
        // Find the "contents" value and fix the unescaped double quotes within it
        $fixedString = preg_replace_callback('/"contents":\s*(".*?")/', function ($matches) {
            $escapedContents = str($matches[1])
                //replace new lines with Pipes
                ->replace('\\', '\\\\')
                ->__toString();

            return '"contents": ' . $escapedContents;
        }, $jsonString);

        return $fixedString;
    }
}
