<?php

namespace App\Notifications;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class Telegram extends Notification
{
    use Queueable;

    public function __construct(public $message) {}

    public function via()
    {
        return [TelegramChannel::class];
    }

    public function toTelegram()
    {
        $token = Str::random();

        return TelegramMessage::create()
            ->to(config('services.telegram-bot-api.chat_id'))
            ->content($this->message)
            ->options([
                'reply_markup' => json_encode([
                    'inline_keyboard' => [[[
                        'text' => 'OK',
                        'callback_data' => json_encode(['type' => 'OK', 'token' => $token])
                    ],
                    [
                        'text' => 'DENY',
                        'callback_data' => json_encode(['type' => 'DENY', 'token' => $token])
                    ]]]
                ])
            ]);
    }
}
