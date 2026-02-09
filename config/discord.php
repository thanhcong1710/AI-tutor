<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Discord Bot Token
    |--------------------------------------------------------------------------
    |
    | Your Discord Bot Token from Discord Developer Portal
    |
    */
    'bot_token' => env('DISCORD_BOT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Client ID & Secret
    |--------------------------------------------------------------------------
    |
    | OAuth2 credentials for Discord
    |
    */
    'client_id' => env('DISCORD_CLIENT_ID'),
    'client_secret' => env('DISCORD_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Webhook URL
    |--------------------------------------------------------------------------
    |
    | The URL where Discord will send interactions
    |
    */
    'webhook_url' => env('DISCORD_WEBHOOK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application Commands
    |--------------------------------------------------------------------------
    |
    | Discord Slash Commands
    |
    */
    'commands' => [
        [
            'name' => 'start',
            'description' => 'Bắt đầu học với AI Tutor',
        ],
        [
            'name' => 'lesson',
            'description' => 'Chọn bài học',
        ],
        [
            'name' => 'progress',
            'description' => 'Xem tiến độ học tập',
        ],
        [
            'name' => 'help',
            'description' => 'Hướng dẫn sử dụng',
        ],
    ],
];
