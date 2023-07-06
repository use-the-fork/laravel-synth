<?php

declare(strict_types=1);

use Blinq\Synth\Controllers\SynthController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('pretty-routes.middlewares', 'web'))
    ->name('synth.show')
    ->group(function (): void {
        Route::get('synth', [SynthController::class, 'show']);
    });
