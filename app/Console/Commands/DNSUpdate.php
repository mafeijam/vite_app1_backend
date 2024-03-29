<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DNSUpdate extends Command
{
    use NotifyTrait;

    protected $signature = 'dns:update';

    protected $description = 'Update Cloudflare DNS And Digital Ocean Firewall';

    protected const CACHE_KEY = 'dns:update';

    public function handle()
    {
        [$oldIP, $newIP] = $this->getIPs();

        if ($oldIP === $newIP) {
            $this->info("ip unchanged [$newIP]");
            return;
        }

        $cf = $this->updateCloudflare($oldIP, $newIP);

        // $do = $this->updateDigitalOcean($newIP);

        $msg = $this->getMsg($oldIP, $newIP, $cf);

        $this->notifyMe($msg);
    }

    protected function getIPs()
    {
        $oldIP = Cache::get(self::CACHE_KEY);
        $newIP = trim(Http::get('ipapi.io/ip')->body());

        return [$oldIP, $newIP];
    }

    protected function updateCloudflare($oldIP, $newIP)
    {
        $cfAPI = sprintf(
            'https://api.cloudflare.com/client/v4/zones/%s/dns_records/%s',
            config('services.cf.zone'),
            config('services.cf.dns')
        );

        $res = Http::withToken(config('services.cf.token'))
            ->put($cfAPI, [
                'type' => 'A',
                'name' => 'jamwong.me',
                'content' => $newIP,
                'ttl' => 1,
                'proxied' => false
            ]);

        if ($res->ok()) {
            $this->info("dns ip updated from [$oldIP] to [$newIP]");
            Cache::put(self::CACHE_KEY, $newIP);
            Storage::put(
                'cf/update.'.date('Ymd_His').'.json',
                json_encode($res->json(), JSON_PRETTY_PRINT)
            );
        }

        return $res;
    }

    protected function updateDigitalOcean($newIP)
    {
        $id = 'bc317e18-5b91-40a8-b81b-19739d9f56a6';

        $res = Http::withToken(config('services.dg.token'))
            ->put("https://api.digitalocean.com/v2/firewalls/{$id}", [
                'name' => 'me',
                'droplet_ids' => ['160682405'],
                'inbound_rules' => [
                    $this->rule($newIP, '22'),
                    $this->rule($newIP, '3306')
                ]
            ]);

        if ($res->ok()) {
            $this->info("firewall ip updated to [$newIP]");
            Storage::put(
                'do/update.'.date('Ymd_His').'.json',
                json_encode($res->json(), JSON_PRETTY_PRINT)
            );
        }

        return $res;
    }

    protected function rule($newIP, $ports)
    {
        return [
            'protocol' => 'tcp',
            'ports' => $ports,
            'sources' => [
                'addresses' => [$newIP]
            ]
        ];
    }

    protected function getMsg($oldIP, $newIP, $cf)
    {
       return sprintf(
            'ip updated from [%s] to [%s], cf status %s',
            $oldIP,
            $newIP,
            $cf->ok() ? 'ok' : 'fail'
        );
    }
}
