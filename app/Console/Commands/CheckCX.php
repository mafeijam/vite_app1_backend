<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class CheckCX extends Command
{
    use NotifyTrait;

    protected $signature = 'cx:check';

    protected $description = 'Check CX flight';

    public function handle()
    {
        $start = today()->format('Ymd');
        $end = today()->addMonths(6)->format('Ymd');

        $types = ['eco' => 'economy', 'pey' => 'premium'];

        $msg = collect();
        $logs = collect();

        foreach ($types as $type => $name) {
            $resp = Http::get("https://api.cathaypacific.com/afr/search/availability/en.HKG.NRT.{$type}.CX.2.{$start}.{$end}.json")
                ->collect('availabilities.std')
                ->filter(fn ($a) => $a['availability'] !== 'NA' && str_starts_with($a['date'], '202310'))
                ->pluck('date');

            $cached = Cache::get("cx.$name");

            if ($cached) {
                $logs[] = $cached;
            }

            if ($resp->isNotEmpty()) {
                $dates = $resp->map(fn ($a) => substr($a, 6))->join(',');
                $format = "availability {$name}\n{$dates}";

                if ($cached !== $format) {
                    $msg[] = $format;
                    Cache::put("cx.$name", $format);
                }
            }
        }

        if ($msg->isNotEmpty()) {
            $this->notifyMe($msg->join("\n"));
        } else {
            Log::channel('debug')->info('debug called cx', $logs->toArray());
        }
    }
}
