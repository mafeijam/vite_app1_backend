<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

Artisan::command('init', function () {
   User::create([
       'name' => 'jw',
       'email' => 'admin@jw.mini',
       'password' => bcrypt(123456)
   ]);
});
