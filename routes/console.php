<?php

use App\Events\TestEvent;
use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Support\Facades\Artisan;

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
