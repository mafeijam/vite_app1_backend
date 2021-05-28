<?php

namespace App\Notifications;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramFile;
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
        return TelegramFile::create()
            ->to('748333103')
            ->content($this->message)
            ->document(storage_path('/app/csl.pdf'), 'csl.pdf');
    }
}
