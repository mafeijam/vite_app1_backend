<?php

namespace App\API\Telegram;

use App\API\Telegram\Traits\APIMethodsTrait;
use App\API\Telegram\Traits\HandleCommandTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class TelegramBot
{
    use APIMethodsTrait, HandleCommandTrait;

    protected $token;

    protected $name;

    protected $endpoint = 'https://api.telegram.org/bot';

    public function __construct($name = null)
    {
        $this->name = $name ?? config('telegrambot.default');
        $this->token = $this->getToken();
    }

    public function handleUpdate($update)
    {
        $command = null;

        $types = ['message', 'callback_query'];

        foreach ($types as $type) {
            if (array_key_exists($type, $update)) {
                $update = json_decode(json_encode($update));
                $command = $this->findCommand($type, $update);
                break;
            }
        }
        return $command;
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
