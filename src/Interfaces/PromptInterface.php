<?php

namespace Blinq\Synth\Interfaces;

interface PromptInterface
{
    public function getFunctions();

    public function getSystem();

    public function getUser();
}
