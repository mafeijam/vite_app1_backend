<?php

namespace App\Http\Controllers;

use App\API\Telegram\TelegramBot;
use App\Events\TwoFactorAnswered;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramController
{
    public function callback(Request $request)
    {
        return [$request->all(), $request->user()];
    }

    public function webhook(Request $request)
    {
        if ($cb = $request->callback_query) {
            $data = json_decode($cb['data']);
            $timestamp = $cb['message']['date'];
            $expiry = Carbon::createFromTimestamp($timestamp)->addMinutes(10);

            if (now()->gt($expiry)) {
                $this->api('answerCallbackQuery', $this->answerCallbackQuery($cb['id'], 'Login Expired.'));

                $this->api('editMessageText', $this->editMessageText($cb, 'expired'));

                return 'expired';
            }

            event(new TwoFactorAnswered($data->answer, $data->token));
            Cache::put($data->token, $data->answer, $expiry);

            $this->api('answerCallbackQuery', $this->answerCallbackQuery($cb['id'], "Login $data->answer."));

            $this->api('editMessageText', $this->editMessageText($cb, $data->answer));
        }

        Log::channel('telegram')->info('debug', $request->all());

        return 'ok';
    }

    public function webhookMini(Request $request)
    {
        $update = $request->all();

        Storage::put('tg/jw_mini.'.date('Ymd.His').'.json', json_encode($update, JSON_PRETTY_PRINT));

        $bot = new TelegramBot('jw_mini');
        $result = $bot->handleUpdate($update);

        Log::channel('telegram')->info('jw_mini', [$result]);

        return 'ok';
    }

    protected function api($method, array $params)
    {
        $api = sprintf(
            'https://api.telegram.org/bot%s/%s',
            config('services.telegram-bot-api.token'),
            $method
        );

        return Http::post($api, $params);
    }

    protected function answerCallbackQuery($id, $text)
    {
        return [
            'callback_query_id' => $id,
            'text' => $text,
            'cache_time' => 10
        ];
    }

    protected function editMessageText($cb, $text)
    {
        $original = $cb['message']['text'];

        return [
            'chat_id' => $cb['message']['chat']['id'],
            'message_id' => $cb['message']['message_id'],
            'text' => sprintf("%s\nresult: *%s*", $original, $text),
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([
                'inline_keyboard' => []
            ])
        ];
    }
}
