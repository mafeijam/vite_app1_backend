<?php

use App\API\Telegram\TelegramBot;
use App\Events\TestEvent;
use App\Models\User;
use App\Notifications\Telegram;
use App\Notifications\TestNotification;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

Artisan::command('init', function () {
   User::create([
       'name' => 'jw',
       'email' => 'admin@jw.mini',
       'password' => bcrypt(123456)
   ]);
});

Artisan::command('test', function () {
    User::find(1)->notify(new TestNotification);
    event(new TestEvent);
});

Artisan::command('telegram', function () {
    $t = (new Telegram('yo testing'))->toTelegram();

    dump($t->toArray());
});

Artisan::command('telegram-test', function () {
    $bot = new TelegramBot;

    $res = $bot->sendDocument([
        'chat_id' => '748333103',
        'contents' => fopen(storage_path('app/csl.pdf'), 'rb')
    ]);

    dump($res);
});
