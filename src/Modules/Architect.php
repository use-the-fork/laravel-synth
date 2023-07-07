<?php

declare(strict_types=1);

namespace Blinq\Synth\Modules;

use PhpSchool\CliMenu\CliMenu;

/**
 * This file is a module in the Chat application, specifically for handling application architecture.
 * It provides functionality to brainstorm and generate a new application architecture using GPT.
 */
class Architect extends Module
{
    public function name(): string
    {
        return 'Architect';
    }

    public function register(): string
    {
        return '[A]rchitect: Brainstorm with GPT to generate a new application architecture.';
    }

    public function onSelect(CliMenu $menu): void
    {
        $this->synthController->synth->loadSystemMessage('architect');
        $currentQuestion = 'What do you want to create?';
        $hasAnswered = false;

        while (true) {
            $input = $this->synthController->cmd->ask($currentQuestion);

            if ('exit' == $input) {
                break;
            }

            if ( ! $input) {
                if ($hasAnswered) {
                    $this->getModule('Attachments')->addAttachmentFromMessage('architecture', $this->synthController->synth->ai->getLastMessage());
                }

                break;
            }

            $this->synthController->synth->chat($input);
            $hasAnswered = true;

            $this->synthController->cmd->newLine();
            $this->synthController->cmd->info("Press enter to accept and continue, type 'exit' to discard, or ask a follow up question.");
            $currentQuestion = 'You';
        }
    }
}
