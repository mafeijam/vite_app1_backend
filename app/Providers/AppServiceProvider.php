<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $host = request()->getHost();
        dd(request());
        // request()->header('cf-connecting-ip');
        if (str_contains($host, 'jamwong.me')) {
            config([
                'app.url' => 'https://'.$host,
                'session.domain' => $host,
                'stateful.stateful' => $host,
            ]);
        }
    }
}
