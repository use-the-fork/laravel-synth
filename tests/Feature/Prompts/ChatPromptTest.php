<?php

use Blinq\Synth\Prompts\ChatPrompt;

it('has Chat prompts', function () {
    $architectPrompt = new ChatPrompt();

    expect($architectPrompt->getFunctions())->toBeArray()
        ->and($architectPrompt->getFunctions())->toHaveCount(1)
        ->and($architectPrompt->getSystem())->toBeArray(1)
        ->and($architectPrompt->getSystem()['role'])->toBe('system')
        ->and($architectPrompt->getSystem()['content'])->toBeString();
});
