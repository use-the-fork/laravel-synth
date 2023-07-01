<?php

use Blinq\Synth\Prompts\ModelPrompt;

it('has Model prompts', function () {
    $modelPrompt = new ModelPrompt();

    expect($modelPrompt->getFunctions())->toBeArray()
        ->and($modelPrompt->getFunctions())->toHaveCount(1)
        ->and($modelPrompt->getUser())->toBeArray(1)
        ->and($modelPrompt->getUser()['role'])->toBe('user')
        ->and($modelPrompt->getUser()['content'])->toBeString()
        ->and($modelPrompt->getUser()['content'])->toContain('class {{ class }} extends Model');
});
