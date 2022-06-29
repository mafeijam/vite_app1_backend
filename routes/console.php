<?php

use App\API\Telegram\TelegramBot;
use App\Events\TestEvent;
use App\Mail\CV;
use App\Models\User;
use App\Notifications\Telegram;
use App\Notifications\TestNotification;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

Artisan::command('init', function () {
   User::create([
       'name' => 'jw',
       'email' => 'admin@jw.mini',
       'password' => bcrypt(123456)
   ]);
});

Artisan::command('test', function () {
    User::find(1)->notify(new TestNotification);
    // event(new TestEvent);
});

Artisan::command('telegram', function () {
    $t = (new Telegram('yo testing'))->toTelegram();

    dump($t->toArray());
});

Artisan::command('telegram-test', function (TelegramBot $bot) {
    // $bot = new TelegramBot;

    $res = $bot->sendDocument([
        'chat_id' => '748333103',
        'contents' => fopen(storage_path('app/csl.pdf'), 'rb')
    ]);

    dump($res);
});

Artisan::command('bus', function () {
    $start = microtime(true);
    $api = 'https://data.etabus.gov.hk/v1/transport/kmb/route-stop/960/inbound/1';

    $stops = Http::get($api)->json();

    foreach ($stops['data'] as $stop) {
        $urls[] = 'https://data.etabus.gov.hk/v1/transport/kmb/stop/'.$stop['stop'];
        $data[] = Http::get('https://data.etabus.gov.hk/v1/transport/kmb/stop/'.$stop['stop']);
    }

    // $data = Http::pool(function ($pool) use ($urls) {
    //     foreach ($urls as $u) {
    //         $pools[] = $pool->get($u);
    //     }
    //     return $pools;
    // });

    foreach ($data as $d) {
        dump($d->json()['data']['name_tc']);
    }

    $this->info(microtime(true) - $start);
});

Artisan::command('db', function () {
    config(['database.connections.mysql.database' => 'budget']);

    $start = microtime(true);

    $stocks = DB::table('stock_holding')
        ->selectRaw('code, min(date) date, sum(qty) qty, sum(cost * qty) / sum(qty) cost')
        ->whereNull('sold')
        ->groupBy('code')
        ->get();

    $urls = $stocks->map(fn ($s) => "https://quote.ticker.com.hk/api/historical_data/detail/{$s->code}.HK/6m");

    // foreach ($urls as $url) {
    //     $data = Http::get($url)->json();
    //     $ticker = $data['meta']['ticker'] ?? null;

    //     $this->info($ticker);
    //     if ($ticker === null) {
    //         break;
    //     }
    // }

    // beginning:

    $data = Http::pool(fn ($p) => $urls->map(fn ($u) => $p->get($u)));

    $some = collect($data)->map(fn ($d) => $d['meta']['ticker'] ?? null)
        ->dump()->some(fn ($d) => $d === null);

    dump($some);

    $this->info(microtime(true) - $start);

    // if ($some) {
    //     sleep(1);
    //     goto beginning;
    // }
});

// Artisan::command('cv', function () {
//     Mail::to('tiffany@jncemployment.com')->send(new CV);
// });

Artisan::command('caddy', function () {

    /*$api = sprintf(
        'https://api.cloudflare.com/client/v4/zones/%s/dns_records',
        config('services.cf.zone')
    );

    $zone = Http::withToken(config('services.cf.token'))
        ->post($api, [
            'type' => 'CNAME',
            'name' => 'caddy',
            'content' => 'jamwong.me',
            'ttl' => 1
        ]);

    dump($zone->json());*/

    $res = Http::withBody(Storage::get('Caddyfile'), 'text/caddyfile')->post('localhost:2019/load');

    dump($res->ok(), $res->json());


    /*$api = sprintf(
        'https://api.cloudflare.com/client/v4/zones/%s/dns_records',
        config('services.cf.zone'),
        '093ca06ad7dddeae546f8072af8b9f1a'
    );

    $zone = Http::withToken(config('services.cf.token'))
        ->get($api, [
            'per_page' => 100
        ]);

    $res = $zone->collect('result')
        ->filter(fn ($r) => in_array($r['type'], ['A', 'CNAME']))
        ->map(fn ($r) => [
            'id' => $r['id'],
            'name' => $r['name'],
            'type' => $r['type'],
            'content' => $r['content']
        ])->toArray();

    dump($res);*/
});

Artisan::command('cf', function () {
    $zone = Http::withToken(config('services.cf.token'))
        ->get('https://api.cloudflare.com/client/v4/zones/48be7bf00ad6f8622151f5a38d49060a/dns_records');

    dump($zone->json());
});
