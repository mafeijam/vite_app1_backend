<?php

namespace App\API\Telegram\Commands;

use App\API\Telegram\BaseCommand;
use App\API\Telegram\ReplyMap;

class RegisterCommand extends BaseCommand
{
    public static $name = 'register';

    public static $description = 'register telegram';

    public function handle()
    {
        $result = $this->sendMessage([
            'chat_id' => $this->chatId(),
            'text' => ReplyMap::REGISTER,
            'reply_markup' => json_encode([
                'force_reply' => true
            ])
        ]);

        return $this->result($result, EmailOTPCommand::class);
    }
}
