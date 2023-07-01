<?php

use Blinq\Synth\ValueObjects\AttachedFileValueObject;

it('highlights value objects', function () {

    $attachedFileValueObjectResource = file_get_contents(__DIR__.'/../../resources/ValueObjects/AttachedFileValueObjectResource.txt');
    $architectPrompt = AttachedFileValueObject::make(base_path('/app/Http/Controllers/ExampleController.php'), $attachedFileValueObjectResource);

    dd($architectPrompt->getFormatted());

    expect($architectPrompt->getFunctions())->toBeArray()
        ->and($architectPrompt->getFunctions())->toHaveCount(1)
        ->and($architectPrompt->getSystem())->toBeArray(1)
        ->and($architectPrompt->getSystem()['role'])->toBe('system')
        ->and($architectPrompt->getSystem()['content'])->toBeString();
});
