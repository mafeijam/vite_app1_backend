<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'json',
        'meta' => 'json'
    ];

    public static function lastHandledMessage($from, $bot)
    {
        return self::where('type', 'telegram message handled')
            ->where('data->from', $from)
            ->where('meta->bot', $bot)
            ->latest()->first();
    }
}
