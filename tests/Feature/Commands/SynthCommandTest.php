<?php

it('starts synth', function () {
    $this->artisan('synth')
        ->expectsQuestion('What do you want to do?', 'exit')
        ->assertExitCode(0);
});
