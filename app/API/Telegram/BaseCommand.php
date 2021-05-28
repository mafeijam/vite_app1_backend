<?php

namespace App\API\Telegram;

abstract class BaseCommand
{
    public function __construct(
        protected $update,
        protected TelegramBot $bot
    ) {}

    public function chatId()
    {
        return $this->update->message?->chat->id
            ?? $this->update->callback_query?->message->chat->id;
    }

    public function queryId()
    {
        return $this->update->callback_query->id;
    }

    public function messageId()
    {
        return $this->update->message?->message_id
            ?? $this->update->callback_query?->message->message_id;
    }

    public function __call($method, $args)
    {
        return $this->bot->{$method}(...$args);
    }
}
