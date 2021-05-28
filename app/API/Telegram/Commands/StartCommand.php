<?php

namespace App\API\Telegram\Commands;

use App\API\Telegram\BaseCommand;
use Illuminate\Support\Facades\Log;

class StartCommand extends BaseCommand
{
    public static $name = 'start';

    public static $description = 'get started';

    public function handle()
    {
        $result = $this->sendMessage([
            'chat_id' => $this->chatId(),
            'text' => 'hi back',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => 'Help', 'callback_data' => '/help 123'],
                        ['text' => 'Register', 'callback_data' => '/register']
                    ]
                ]
            ])
        ]);

        Log::channel('debug')->info('bot result', [$result]);

        return class_basename($this);
    }
}
