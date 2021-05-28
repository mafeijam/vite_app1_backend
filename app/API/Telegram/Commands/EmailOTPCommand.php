<?php

namespace App\API\Telegram\Commands;

use App\API\Telegram\BaseCommand;
use App\API\Telegram\ReplyMap;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailOTPCommand extends BaseCommand
{
    public function handle()
    {
        $otp = mt_rand(1000, 9999);

        $email = $this->verifyEmail();

        $expiry = now()->addMinutes(10);

        if ($email) {
            Mail::raw($otp, fn ($message) => $message->to($email));
            Cache::put('tg.otp.'.$this->chatId(), $otp, $expiry);
            Cache::put('tg.email.'.$this->chatId(), $email, $expiry);
        }

        $result = $this->sendMessage([
            'chat_id' => $this->chatId(),
            'text' => $email ? ReplyMap::VERIFY : ReplyMap::INVALID,
            'reply_markup' => json_encode([
                'force_reply' => true
            ])
        ]);

        Log::channel('debug')->info('bot result', [$result]);

        return class_basename($this);
    }

    protected function verifyEmail()
    {
        $text = $this->update->message->text;

        $email = null;

        $entities = $this->update->message->entities ?? [];

        foreach ($entities as $entitie) {
            if ($entitie->type === 'email') {
                $email = substr($text, $entitie->offset, $entitie->length);
                break;
            }
        }

        return $email;
    }
}
