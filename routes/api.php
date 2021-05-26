<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\WebPushController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/ping', fn () => ['pong' => true]);

    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/subscribe', [WebPushController::class, 'subscribe']);
    Route::post('/unsubscribe', [WebPushController::class, 'unsubscribe']);

    Route::post('/broadcast', BroadcastController::class);
});

Route::post('/login', [AuthController::class, 'login']);

Route::post('/2fa', [AuthController::class, 'twoFA']);
Route::get('/2fa/answered/{answer}', [AuthController::class, 'answered']);
Route::get('/2fa/{answer}/{token}', [AuthController::class, 'answer'])->name('2fa');
Route::get('/2fa/beforeunload', [AuthController::class, 'beforeunload']);
