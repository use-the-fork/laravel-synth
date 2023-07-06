<?php

declare(strict_types=1);

namespace Blinq\Synth\Controllers;

use Illuminate\Routing\Controller;

class SynthController extends Controller
{
    public function show()
    {
        return view('synth::synthBase');
    }
}
