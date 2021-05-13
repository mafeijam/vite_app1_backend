<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebPushController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Auth::loginUsingId(1);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/ping', fn () => ['pong' => true]);

    Route::get('/me', fn (Request $request) => $request->user());

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/subscribe', [WebPushController::class, 'subscribe']);
    Route::post('/unsubscribe', [WebPushController::class, 'unsubscribe']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::get('/t', fn () => request()->headers->all());
