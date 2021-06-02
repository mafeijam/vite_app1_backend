<?php

namespace App\API\Telegram;

abstract class BaseCommand
{
    protected $methodsWithChatId = [
        'sendMessage',
        'sendDocument',
        'editMessageText'
    ];

    public function __construct(
        protected $update,
        protected TelegramBot $bot
    ) {}

    public function chatId()
    {
        return $this->update->message?->chat->id
            ?? $this->update->callback_query?->from->id;
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

    public function result($result, $next = null)
    {
        return [
            'command' => get_class($this),
            'result' => $result,
            'update' => $this->update->update_id,
            'from' => $this->chatId(),
            'next' => $next
        ];
    }

    public function __call($method, $args)
    {
        if (in_array($method, $this->methodsWithChatId) && !isset($args[0]['chat_id'])) {
            $args[0]['chat_id'] = $this->chatId();
        }

        if ($method === 'answerCallbackQuery') {
            $args[0]['callback_query_id'] = $this->queryId();
        }

        if ($method === 'editMessageText') {
            $args[0]['message_id'] = $this->messageId();
        }

        return $this->bot->{$method}(...$args);
    }

}
