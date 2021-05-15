<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebPushController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/ping', fn () => ['pong' => true]);

    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/subscribe', [WebPushController::class, 'subscribe']);
    Route::post('/unsubscribe', [WebPushController::class, 'unsubscribe']);
});

Route::post('/login', [AuthController::class, 'login']);
