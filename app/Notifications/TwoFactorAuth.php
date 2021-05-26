<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
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
            ->content("Confirm Login *{$this->user->name}*!\nexpiry: {$expiry->format('Y-m-d H:i:s')}")
            ->button('OK', $this->getUrl($expiry, 'ok'))
            ->button('DENY', $this->getUrl($expiry, 'deny'));
    }

    protected function getUrl($expiry, $answer)
    {
        return URL::temporarySignedRoute('2fa', $expiry, [
            'answer' => $answer,
            'token' => $this->token
        ]);
    }
}
