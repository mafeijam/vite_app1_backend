<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebPushController
{
    public function subscribe(Request $request)
    {
        $request->validate(['endpoint' => 'required']);
        $request->user()->updatePushSubscription($request->endpoint, $request->keys['p256dh'], $request->keys['auth']);
        return response()->json(['message' => 'done']);
    }

    public function unsubscribe(Request $request)
    {
        $request->validate(['endpoint' => 'required']);
        $request->user()->deletePushSubscription($request->endpoint);
        return response()->json(['message' => 'done']);
    }

    public function test()
    {
        App\User::find(1)->notify(new App\Notifications\TestNotification);
        return 'ok';
    }
}
