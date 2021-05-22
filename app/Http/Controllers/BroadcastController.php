<?php

namespace App\Http\Controllers;

use App\Events\TestEvent;
use Illuminate\Http\Request;

class BroadcastController
{
    public function __invoke(Request $request)
    {
        broadcast(new TestEvent)->toOthers();

        return response()->json(['success' => true, 'message' => 'done']);
    }
}
