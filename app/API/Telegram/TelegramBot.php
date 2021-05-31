<?php

namespace App\API\Telegram;

use App\API\Telegram\Traits\APIMethodsTrait;
use App\API\Telegram\Traits\HandleCommandTrait;
use App\Models\Activity;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class TelegramBot
{
    use APIMethodsTrait, HandleCommandTrait;

    protected $token;

    protected $name;

    protected $endpoint = 'https://api.telegram.org/bot';

    protected $handleUpdateTypes = [
        'message',
        'callback_query'
    ];

    public function __construct($name = null)
    {
        $this->name = $name ?? config('telegrambot.default');
        $this->token = $this->getToken();
    }

    public function handleUpdate($update)
    {
        $type = $this->getUpdateType($update);

        Activity::create([
            'type' => 'telegram webhook update',
            'data' => $update,
            'meta' => [
                'bot' => $this->name,
                'type' => $type,
                'from' => $this->getUpdateFrom($update)
            ]
        ]);

        $command = null;

        if (in_array($type, $this->handleUpdateTypes)) {
            $update = json_decode(json_encode($update));
            $command = $this->findCommand($type, $update);
        }

        return $command;
    }

    protected function getUpdateType($update)
    {
        $type = Arr::except($update, 'update_id');
        return key($type);
    }

    protected function getUpdateFrom($update)
    {
        return Arr::get($update, 'message.chat.id') ?? Arr::get($update, 'callback_query.from.id');
    }

    protected function getToken()
    {
        return config("telegrambot.bots.{$this->name}.token");
    }

    protected function call($method, array $params = [])
    {
        return Http::post($this->endpoint($method), $params)->json();
    }

    protected function callFile($method, $name, array $params = [])
    {
        $contents = Arr::pull($params, 'contents');
        $filename = Arr::pull($params, 'filename');

        return Http::attach($name, $contents, $filename)
            ->post($this->endpoint($method), $params)->json();
    }

    protected function endpoint($method)
    {
        return sprintf('%s%s/%s', $this->endpoint, $this->token, $method);
    }
}
