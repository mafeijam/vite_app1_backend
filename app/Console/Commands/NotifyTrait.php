<?php

namespace App\Console\Commands;

use App\Notifications\Telegram;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

trait NotifyTrait
{
    protected function notifyMe($msg)
    {
        try {
            Notification::route('telegram', config('services.telegram-bot-api.chat_id'))
                ->notify(new Telegram($msg));
        } catch (Throwable $t) {
            Log::channel('debug')->info('failed to notify cx', [$t->__toString()]);
        }
    }
}
