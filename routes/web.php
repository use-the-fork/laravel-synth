<?php

declare(strict_types=1);

use Blinq\Synth\Controllers\ChatController;
use Blinq\Synth\Controllers\ModifyController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'synth',
    'name' => 'synth.',
    'middleware' => config('synth.middlewares', ['web']),
], function (): void {
    Route::get('chat', ChatController::class)->name('synth.chat');
    Route::get('modify', ModifyController::class)->name('synth.modify');
});
