<?php

namespace Blinq\Synth\Controllers;

use Blinq\Synth\Commands\SynthCommand;
use Blinq\Synth\MainMenu;
use Blinq\Synth\Modules;
use Blinq\Synth\Synth;
use Blinq\Synth\ValueObjects\AttachedFileValueObject;
use Blinq\Synth\ValueObjects\ChatMessageValueObject;

class SynthController
{
    public $cmd;

    /**
     * @param  ChatMessageValueObject[]  $chatHistory
     * @param  AttachedFileValueObject[]  $attachedFiles
     */
    public function __construct(
        public Synth $synth,
        public MainMenu $mainMenu,
        public Modules $modules,
        public array $chatHistory = [],
        public array $attachedFiles = [],
    ) {
    }

    public function setSynthCommand(SynthCommand $cmd): void
    {
        $this->cmd = $cmd;

        //Set Synth controller for all classes
        $this->synth->setSynthController();
        $this->mainMenu->setSynthController();
        $this->modules->setSynthController();
    }

    public function getChatHistory(): array
    {
        return $this->chatHistory;
    }

    //commands specific to adding and removing attachments

    public function getAttachedFiles(): array
    {
        return $this->attachedFiles;
    }

    public function addAttachedFile($key, $value): void
    {
        $base = basename($key);
        $this->cmd->comment("Attaching {$base}");
        $this->attachedFiles[$key] = AttachedFileValueObject::make($key, $value);
    }

    public function removeAttachedFile($key): void
    {
        $base = basename($key);
        $this->cmd->comment("Removed {$base}");
        unset($this->attachedFiles[$key]);
    }

    public function clearAttachedFiles(): void
    {
        $this->attachedFiles = [];

        $this->cmd->comment('Attachments cleared');
        $this->cmd->newLine();
    }

    public function setAttachedFiles($attachedFiles = []): array
    {
        return $this->attachedFiles;
    }
}
