<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class TestNotification extends Notification
{
    use Queueable;

    public function via()
    {
        return ['broadcast', WebPushChannel::class];
    }

    public function toBroadcast()
    {
        return new BroadcastMessage([
            'message' => 'test'
        ]);
    }

    public function toArray()
    {
        return [
            'message' => 'test'
        ];
    }

    public function toWebPush()
    {
        return (new WebPushMessage)
            ->title('Testing!')
            ->body('Hey!');
    }
}
