<?php

declare(strict_types=1);

namespace Blinq\Synth\Controllers;

use Illuminate\Routing\Controller;

class ChatController extends Controller
{
    public function __invoke()
    {
        return view('synth::chat');
    }
}
