<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class TwoFactorAuth extends Notification
{
    use Queueable;

    public function __construct(public $token, protected $user) {}

    public function via()
    {
        return [TelegramChannel::class];
    }

    public function toTelegram()
    {
        $expiry = now()->addMinutes(10);

        return TelegramMessage::create()
            ->content("Confirm Login *{$this->user->name}*!\nexpiry at *{$expiry->format('Y-m-d H:i:s')}*")
            ->options([
                'reply_markup' => json_encode([
                    'inline_keyboard' => [[[
                        'text' => 'OK',
                        'callback_data' => json_encode(['answer' => 'OK', 'token' => $this->token])
                    ],
                    [
                        'text' => 'DENY',
                        'callback_data' => json_encode(['answer' => 'DENY', 'token' => $this->token])
                    ]]]
                ])
            ]);
    }
}
