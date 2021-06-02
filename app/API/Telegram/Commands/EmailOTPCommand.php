<?php

namespace App\API\Telegram\Commands;

use App\API\Telegram\BaseCommand;
use App\API\Telegram\ReplyMap;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class EmailOTPCommand extends BaseCommand
{
    protected $text;

    protected $next;

    protected $email;

    public function handle()
    {
        return $this
            ->verifyEmail()
            ->generateOTP()
            ->send();
    }

    protected function verifyEmail()
    {
        $text = $this->update->message->text;

        $entities = $this->update->message->entities ?? [];
        $entitie = collect($entities)->first(fn ($e) => $e->type === 'email');

        if ($entitie) {
            $this->text = ReplyMap::VERIFY;
            $this->next = VerifyOTPCommand::class;
            $this->email = substr($text, $entitie->offset, $entitie->length);
        } else {
            $this->text = ReplyMap::INVALID;
            $this->next = self::class;
            $this->email = null;
        }

        return $this;
    }

    protected function generateOTP()
    {
        if ($this->email) {
            $expiry = now()->addMinutes(10);
            $otp = mt_rand(100000, 999999);
            Mail::raw($otp, fn ($message) => $message->to($this->email));
            Cache::put('tg.otp.'.$this->chatId(), $otp, $expiry);
            Cache::put('tg.email.'.$this->chatId(), $this->email, $expiry);
        }

        return $this;
    }

    protected function send()
    {
        $result = $this->sendMessage([
            'text' => $this->text,
            'reply_markup' => json_encode([
                'force_reply' => true
            ])
        ]);

        return $this->result($result, $this->next);
    }
}
