<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Tutor Application Settings
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Free Tier Settings
    |--------------------------------------------------------------------------
    */
    'free_minutes_per_day' => env('AI_TUTOR_FREE_MINUTES_PER_DAY', 10),

    /*
    |--------------------------------------------------------------------------
    | Premium Settings
    |--------------------------------------------------------------------------
    */
    'premium_price' => env('AI_TUTOR_PREMIUM_PRICE', 99000), // VNĐ

    /*
    |--------------------------------------------------------------------------
    | Session Settings
    |--------------------------------------------------------------------------
    */
    'session_timeout' => env('AI_TUTOR_SESSION_TIMEOUT', 1800), // 30 minutes

    /*
    |--------------------------------------------------------------------------
    | OpenAI Settings
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'model' => env('OPENAI_MODEL', 'gpt-4o'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 2000),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google TTS Settings
    |--------------------------------------------------------------------------
    */
    'google_tts' => [
        'language_code' => env('GOOGLE_TTS_LANGUAGE_CODE', 'vi-VN'),
        'voice_name' => env('GOOGLE_TTS_VOICE_NAME', 'vi-VN-Wavenet-A'),
        'audio_encoding' => 'MP3',
        'speaking_rate' => 1.0,
        'pitch' => 0.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | ElevenLabs TTS Settings (Premium)
    |--------------------------------------------------------------------------
    */
    'elevenlabs' => [
        'api_key' => env('ELEVENLABS_API_KEY'),
        'voice_id' => env('ELEVENLABS_VOICE_ID'),
        'model_id' => 'eleven_multilingual_v2',
    ],

    /*
    |--------------------------------------------------------------------------
    | Whisper STT Settings
    |--------------------------------------------------------------------------
    */
    'whisper' => [
        'api_key' => env('WHISPER_API_KEY'),
        'model' => env('WHISPER_MODEL', 'whisper-1'),
        'language' => 'vi',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Subjects
    |--------------------------------------------------------------------------
    */
    'subjects' => [
        'english' => [
            'name' => 'Tiếng Anh',
            'icon' => '📚',
            'levels' => ['beginner', 'intermediate', 'advanced'],
        ],
        'math' => [
            'name' => 'Toán',
            'icon' => '🔢',
            'levels' => ['beginner', 'intermediate', 'advanced'],
        ],
        'logic' => [
            'name' => 'Tư duy Logic',
            'icon' => '🧠',
            'levels' => ['beginner', 'intermediate', 'advanced'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Platforms
    |--------------------------------------------------------------------------
    */
    'platforms' => [
        'telegram' => [
            'enabled' => true,
            'name' => 'Telegram',
        ],
        'discord' => [
            'enabled' => true,
            'name' => 'Discord',
        ],
        'web' => [
            'enabled' => true,
            'name' => 'Web App',
        ],
        'mobile' => [
            'enabled' => true,
            'name' => 'Mobile App',
        ],
    ],
];
