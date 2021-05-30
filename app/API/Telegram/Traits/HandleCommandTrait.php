<?php

namespace App\API\Telegram\Traits;

use App\API\Telegram\ReplyMap;
use Illuminate\Support\Str;

trait HandleCommandTrait
{
    protected $namespace = 'App\\API\\Telegram\\Commands\\';

    public function findCommand($type, $update)
    {
        $method = 'findCommandBy'.Str::camel($type);

        if (method_exists($this, $method)) {
            return $this->{$method}($update);
        }

        return null;
    }

    public function findCommandByMessage($update)
    {
        $message = $update->message;

        $entities = $message->entities ?? [];

        $command = null;

        foreach ($entities as $entitie) {
            if ($entitie->type === 'bot_command') {
                $command = substr($message->text, $entitie->offset, $entitie->length);
                break;
            }
        }

        $class = $this->getCommandClass($command);

        if (class_exists($class)) {
            return (new $class($update, $this))->handle();
        }

        if ($reply = $message->reply_to_message) {
            $command = ReplyMap::command($reply->text);

            if (class_exists($command)) {
                return (new $command($update, $this))->handle();
            }
        }

        return null;
    }

    public function findCommandByCallbackQuery($update)
    {
        $data = $update->callback_query->data;
        $args = explode(' ', $data);
        $command = array_shift($args);

        $class = $this->getCommandClass($command);

        if (class_exists($class)) {
            return (new $class($update, $this))->handle(...$args);
        }

        return false;
    }

    protected function getCommandClass($command)
    {
        if (str_starts_with($command, '/')) {
            return $this->namespace . ucfirst(ltrim($command, '/')) . 'Command';
        }

        return null;
    }

    public function getLocalCommands()
    {
        $commands = config("telegrambot.bots.{$this->name}.commands");

        return $this->convertCommands($commands);
    }

    public function getGlobalCommands()
    {
        $commands = config('telegrambot.commands');

        return $this->convertCommands($commands);
    }

    protected function convertCommands($commands)
    {
        return collect($commands)->map(fn ($command) => [
            'command' => $command::$name,
            'description' => $command::$description
        ])->toArray();
    }
}
