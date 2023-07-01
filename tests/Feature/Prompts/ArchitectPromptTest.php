<?php

use Blinq\Synth\Prompts\ArchitectPrompt;

it('has Architect prompts', function () {
    $architectPrompt = new ArchitectPrompt();

    expect($architectPrompt->getFunctions())->toBeArray()
        ->and($architectPrompt->getFunctions())->toHaveCount(1)
        ->and($architectPrompt->getSystem())->toBeArray(1)
        ->and($architectPrompt->getSystem()['role'])->toBe('system')
        ->and($architectPrompt->getSystem()['content'])->toBeString();
});
