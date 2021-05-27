<?php

use App\Http\Controllers\TelegramController;

Route::post('/telegram', [TelegramController::class, 'webhook']);
