<?php

namespace App\API\Telegram\Traits;

use Illuminate\Support\Arr;

trait APIMethodsTrait
{
    public function setWebhook()
    {
        $base = config('app.url');
        $webhook = config("telegrambot.bots.{$this->name}.webhook");

        return $this->call('setWebhook', [
            'url' => sprintf('%s/%s', $base, $webhook)
        ]);
    }

    public function getWebhookInfo()
    {
        return $this->call('getWebhookInfo');
    }

    public function setMyCommands()
    {
        $commands = array_merge($this->getLocalCommands(), $this->getGlobalCommands());

        return $this->call('setMyCommands', [
            'commands' => json_encode($commands)
        ]);
    }

    public function sendMessage($params)
    {
        return $this->call('sendMessage', $params);
    }

    public function sendDocument($params)
    {
        $path = Arr::pull($params, 'path');
        $contents = Arr::pull($params, 'contents');

        return $this->callFile('sendDocument', 'document', $path, $contents, $params);
    }

    public function answerCallbackQuery($params)
    {
        return $this->call('answerCallbackQuery', $params);
    }

    public function editMessageText($params)
    {
        $this->call('editMessageText', $params);
    }
}
