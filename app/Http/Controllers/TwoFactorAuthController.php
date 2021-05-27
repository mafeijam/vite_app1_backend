<?php

namespace App\Http\Controllers;

use App\Events\TwoFactorAnswered;
use App\Models\User;
use App\Notifications\TwoFactorAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class TwoFactorAuthController
{
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = Str::random(30);
            $request->session()->put('2fa_token', $token);
            $request->session()->put('2fa_pending_user', $user->id);
            $request->session()->put('2fa_remember', $request->boolean('remember'));

            Notification::route('telegram', config('services.telegram-bot-api.chat_id'))
                ->notify(new TwoFactorAuth($token, $user));

            return response()->json(['success' => true, 'token' => $token]);
        }

        return response()->json(['success' => false, 'message' => '帳號或密碼不符'], 401);
    }

    public function answered(Request $request)
    {
        $token = $request->session()->pull('2fa_token');
        $id = $request->session()->pull('2fa_pending_user');

        if ($id && $request->answer === 'OK' && Cache::get($token) === $request->answer) {
            Auth::loginUsingId($id, $request->session()->pull('2fa_remember'));
            return response()->json(['success' => true, 'user' => Auth::user()]);
        }

        return response()->json(['success' => false, 'message' => '2fa failed'], 401);
    }

    public function cancel(Request $request)
    {
        $token = $request->session()->pull('2fa_token');
        $request->session()->forget('2fa_pending_user');
        if ($token) Cache::forget($token);
        info('cancel 2fa', [$token]);
    }
}
