<?php

namespace App\API\Telegram\Commands;

use App\API\Telegram\BaseCommand;
use App\API\Telegram\ReplyMap;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class EmailOTPCommand extends BaseCommand
{
    public function handle()
    {
        $email = $this->verifyEmail();

        $this->generateOTP($email);

        $result = $this->sendMessage([
            'chat_id' => $this->chatId(),
            'text' => $email ? ReplyMap::VERIFY : ReplyMap::INVALID,
            'reply_markup' => json_encode([
                'force_reply' => true
            ])
        ]);

        return $this->result($result, $email ? VerifyOTPCommand::class : self::class);
    }

    protected function verifyEmail()
    {
        $text = $this->update->message->text;

        $entities = $this->update->message->entities ?? [];
        $entitie = collect($entities)->first(fn ($e) => $e->type === 'email');

        if ($entitie) {
            return substr($text, $entitie->offset, $entitie->length);
        }

        return null;
    }

    protected function generateOTP($email)
    {
        if ($email) {
            $expiry = now()->addMinutes(10);
            $otp = mt_rand(100000, 999999);
            Mail::raw($otp, fn ($message) => $message->to($email));
            Cache::put('tg.otp.'.$this->chatId(), $otp, $expiry);
            Cache::put('tg.email.'.$this->chatId(), $email, $expiry);
        }
    }
}
