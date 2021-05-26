<?php

namespace App\Notifications;

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
        return TelegramMessage::create()
            ->content($this->message);
    }
}
