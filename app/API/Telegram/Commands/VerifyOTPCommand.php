<?php

namespace App\API\Telegram\Commands;

use App\API\Telegram\BaseCommand;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VerifyOTPCommand extends BaseCommand
{
    public function handle()
    {
        $otp = Cache::get('tg.otp.'.$this->chatId());
        $email = Cache::get('tg.email.'.$this->chatId());
        $user = User::where('email', $email)->first();

        $text = $this->update->message->text;

        $pass = (int) $otp === (int) $text && $user;

        $result = $this->sendMessage([
            'chat_id' => $this->chatId(),
            'text' => $pass ? 'verified' : 'failure'
        ]);

        Log::channel('debug')->info('bot result', [$result]);

        return class_basename($this);
    }
}
