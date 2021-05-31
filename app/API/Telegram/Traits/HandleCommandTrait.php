<?php

namespace App\API\Telegram\Traits;

use App\API\Telegram\ReplyMap;
use App\Models\Activity;
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
        $reply = null;

        [$command, $class] = $this->findFromEntities($message);

        if ($command === null) {
            $reply = $this->reply($update);
            $class = ReplyMap::command($reply);
        }

        if (class_exists($class)) {
            $result = (new $class($update, $this))->handle();

            Activity::create([
                'type' => 'telegram message handled',
                'data' => $result,
                'meta' => [
                    'bot' => $this->name,
                    'is_reply' => $reply ? true : false
                ]
            ]);

            return $class;
        }

        return null;
    }

    protected function reply($update)
    {
        return $update->message->reply_to_message?->text
            ?? Activity::lastHandledMessage($update->message->chat->id, $this->name)?->data['next'];
    }

    public function findCommandByCallbackQuery($update)
    {
        $data = $update->callback_query->data;
        $args = explode(' ', $data);
        $command = array_shift($args);

        $class = $this->getCommandClass($command);

        if (class_exists($class)) {
            $result = (new $class($update, $this))->handle(...$args);

            Activity::create([
                'type' => 'telegram callback handled',
                'data' => $result,
                'meta' => [
                    'bot' => $this->name,
                    'args' => $args
                ]
            ]);

            return $class;
        }

        return null;
    }

    protected function findFromEntities($message)
    {
        $entities = $message->entities ?? [];
        $entitie = collect($entities)->first(fn ($e) => $e->type === 'bot_command');
        [$command, $class] = [null, null];

        if ($entitie) {
            $command = substr($message->text, $entitie->offset, $entitie->length);
            $class = $this->getCommandClass($command);
        }

        return [$command, $class];
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
