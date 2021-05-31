<?php

namespace App\API\Telegram;

use App\API\Telegram\Commands\EmailOTPCommand;
use App\API\Telegram\Commands\VerifyOTPCommand;

class ReplyMap
{
    public const REGISTER = 'please enter your email address';

    public const INVALID = 'invalid email address, please re-enter';

    public const VERIFY = 'OTP has been sent by email, enter it here to verify';

    public static function command($key)
    {
        return match($key) {
            self::REGISTER, self::INVALID => EmailOTPCommand::class,
            self::VERIFY => VerifyOTPCommand::class,
            default => $key
        };
    }
}
