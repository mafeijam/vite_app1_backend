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

class AuthController
{
    public function me(Request $request)
    {
        return response()->json(['success' => true, 'user' => $request->user()]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return response()->json(['success' => true, 'user' => $request->user()]);
        }

        return response()->json(['success' => false, 'message' => '帳號或密碼不符'], 401);
    }

    public function twoFA(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = Str::random(60);
            $request->session()->put('2fa_token', $token);
            $request->session()->put('2fa_pending_user', $user->id);

            Notification::route('telegram', config('services.telegram-bot-api.chat_id'))
                ->notify(new TwoFactorAuth($token, $user));

            return response()->json(['success' => true, 'token' => $token]);
        }

        return response()->json(['success' => false, 'message' => '帳號或密碼不符'], 401);
    }

    public function answer($answer, $token)
    {
        if (Cache::get($token) !== null) {
            return 'expiry';
        }

        event(new TwoFactorAnswered($answer, $token));
        Cache::put($token, $answer, now()->addMinutes(10));
        return 'done';
    }

    public function answered(Request $request, $answer)
    {
        $token = $request->session()->pull('2fa_token');
        $id = $request->session()->pull('2fa_pending_user');

        if ($id && $answer === 'ok' && Cache::get($token) === $answer) {
            Auth::loginUsingId($id);
            return response()->json(['success' => true, 'user' => Auth::user()]);
        }

        return response()->json(['success' => false, 'message' => '2fa failed'], 401);
    }

    public function beforeunload(Request $request)
    {
        $token = $request->session()->pull('2fa_token');
        $request->session()->forget('2fa_pending_user');
        if ($token) Cache::forget($token);
        info('onbeforeunload', [$token]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json(['success' => true]);
    }
}
