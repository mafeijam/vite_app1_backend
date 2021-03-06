<?php

namespace App\API\Telegram\Commands;

use App\API\Telegram\BaseCommand;

class HelpCommand extends BaseCommand
{
    public static $name = 'help';

    public static $description = 'get help';

    public function handle($args)
    {
        $result1 = $this->answerCallbackQuery([
            'text' => 'call you back ' . $args
        ]);

        $result2 = $this->editMessageText([
            'text' => 'helped',
            'reply_markup' => json_encode([
                'inline_keyboard' => []
            ])
        ]);

        return $this->result([$result1, $result2]);
    }
}
