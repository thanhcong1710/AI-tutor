<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Token
    |--------------------------------------------------------------------------
    |
    | Your Telegram Bot Token from @BotFather
    |
    */
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Bot Username
    |--------------------------------------------------------------------------
    |
    | Your Telegram Bot Username (without @)
    |
    */
    'bot_username' => env('TELEGRAM_BOT_USERNAME'),

    /*
    |--------------------------------------------------------------------------
    | Webhook URL
    |--------------------------------------------------------------------------
    |
    | The URL where Telegram will send updates
    |
    */
    'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Async Requests
    |--------------------------------------------------------------------------
    |
    | Enable async requests to Telegram API
    |
    */
    'async_requests' => env('TELEGRAM_ASYNC_REQUESTS', false),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Handler
    |--------------------------------------------------------------------------
    |
    | HTTP Client Handler for Telegram Bot SDK
    |
    */
    'http_client_handler' => null,

    /*
    |--------------------------------------------------------------------------
    | Commands
    |--------------------------------------------------------------------------
    |
    | Telegram Bot Commands
    |
    */
    'commands' => [
        Telegram\Bot\Commands\HelpCommand::class,
        App\Telegram\Commands\StartCommand::class, // Added Start Command
        App\Telegram\Commands\LangCommand::class, // Added Lang Command
        App\Telegram\Commands\ListLessonsCommand::class, // Added List Lessons
        App\Telegram\Commands\LearnCommand::class, // Added Learn Command
        App\Telegram\Commands\NextCommand::class, // Added Next Command
        App\Telegram\Commands\QuizCommand::class, // Added Quiz Command
        App\Telegram\Commands\ResetCommand::class, // Added Reset Command
    ],

    /*
    |--------------------------------------------------------------------------
    | Command Groups
    |--------------------------------------------------------------------------
    |
    | Group your commands for better organization
    |
    */
    'command_groups' => [],
];
