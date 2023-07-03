<?php

use Blinq\Synth\Exceptions\MissingFunctionParametersException;
use Blinq\Synth\Functions\SaveFilesFunction;
use Blinq\Synth\ValueObjects\AttachedFileValueObject;

it('saves and validates processed files', function () {

    $files = [];
    $saveFilesFunctionTest = file_get_contents(__DIR__.'/../../resources/Functions/SaveFilesFunctionTest.txt');
    $architectPrompt = new SaveFilesFunction();

    $files = $architectPrompt->doFunction($saveFilesFunctionTest, $files);

    expect($files)->toBeArray()
        ->and($files)->toHaveCount(1)
        ->and($files['app/Domains/Email/Http/Controllers/Api/EmailController.php'])->toBeInstanceOf(AttachedFileValueObject::class)
        ->and($files['app/Domains/Email/Http/Controllers/Api/EmailController.php']->getFile())->toBe('app/Domains/Email/Http/Controllers/Api/EmailController.php');
});

it('validates Ai return', function () {

    $files = [];
    $saveFilesFunctionTest = '{}';
    $architectPrompt = new SaveFilesFunction();
    $files = $architectPrompt->doFunction($saveFilesFunctionTest, $files);
})->throws(MissingFunctionParametersException::class);
