<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class NewEmailReceived extends Notification
{
    use Queueable;

    public $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function via()
    {
        return [TelegramChannel::class];
    }

    public function toTelegram()
    {
        return TelegramMessage::create()
            ->content($this->email);
    }
}
