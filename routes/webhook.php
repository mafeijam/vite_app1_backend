<?php

use App\Http\Controllers\TelegramController;

Route::post('/telegram', [TelegramController::class, 'webhook']);

Route::post('/telegram/jw_mini', [TelegramController::class, 'webhookMini']);

Route::get('/mail', function() {
    return 'ok';
});

Route::post('mailgun', function () {
    if (preg_match('/freehunter|ecosa.com/i', request('from'))) {
        return;
    }

    Log::channel('debug')->info('mailgun', request()->only('sender', 'body-plain', 'from', 'subject'));

    $msg = sprintf(
        "[from %s - %s]\n%s",
        request('from'),
        request('subject'),
        request('body-plain')
    );

    Notification::route('telegram', config('services.telegram-bot-api.chat_id'))
        ->notify(new App\Notifications\NewEmailReceived($msg));
});
