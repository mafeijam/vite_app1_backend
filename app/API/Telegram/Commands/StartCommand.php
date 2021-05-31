<?php

namespace App\API\Telegram\Commands;

use App\API\Telegram\BaseCommand;

class StartCommand extends BaseCommand
{
    public static $name = 'start';

    public static $description = 'get started';

    public function handle()
    {
        $result = $this->sendMessage([
            'chat_id' => $this->chatId(),
            'text' => 'lets get started',
            'reply_markup' => json_encode([
                'inline_keyboard' => [[
                    ['text' => 'Help', 'callback_data' => '/help 123'],
                    ['text' => 'Register', 'callback_data' => '/register']
                ]]
            ])
        ]);

        return $this->result($result);
    }
}
