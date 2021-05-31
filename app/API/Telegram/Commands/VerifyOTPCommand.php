<?php

namespace App\API\Telegram\Commands;

use App\API\Telegram\BaseCommand;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class VerifyOTPCommand extends BaseCommand
{
    public function handle()
    {
        $result = $this->sendMessage([
            'chat_id' => $this->chatId(),
            'text' => $this->verifyOTP() ? 'verified' : 'failure'
        ]);

        return $this->result($result);
    }

    protected function verifyOTP()
    {
        $otp = Cache::get('tg.otp.'.$this->chatId());
        $email = Cache::get('tg.email.'.$this->chatId());
        $user = User::where('email', $email)->first();

        $text = $this->update->message->text;

        return (int) $otp === (int) $text && $user;
    }
}
