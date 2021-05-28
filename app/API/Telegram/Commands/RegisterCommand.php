<?php

namespace App\API\Telegram\Commands;

use App\API\Telegram\BaseCommand;
use Illuminate\Support\Facades\Log;

class RegisterCommand extends BaseCommand
{
    public static $name = 'register';

    public static $description = 'register telegram';

    public function handle()
    {
        $result = $this->sendMessage([
            'chat_id' => $this->chatId(),
            'text' => 'please enter your email address',
            'reply_markup' => json_encode([
                'force_reply' => true
            ])
        ]);

        Log::channel('debug')->info('bot result', [$result]);

        return class_basename($this);
    }
}
