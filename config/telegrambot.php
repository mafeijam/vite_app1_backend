<?php

return [
    'default' => env('TELEGRAM_BOT2'),

    'bots' => [
        'jw_mini' => [
            'token' => env('TELEGRAM_BOT2_TOKEN'),
            'webhook' => '/webhook/telegram/jw_mini',
            'commands' => [
                App\API\Telegram\Commands\RegisterCommand::class
            ]
        ]
    ],

    'commands' => [
        App\API\Telegram\Commands\StartCommand::class,
        App\API\Telegram\Commands\HelpCommand::class
    ]
];
