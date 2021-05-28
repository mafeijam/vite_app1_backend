<?php

use App\Http\Controllers\TelegramController;

Route::post('/telegram', [TelegramController::class, 'webhook']);

Route::post('/telegram/jw_mini', [TelegramController::class, 'webhookMini']);
