<?php

return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),

    'base_url' => env('TELEGRAM_BASE_URL', 'https://api.telegram.org'),

    'webhook' => [
        'enabled' => (bool) env('TELEGRAM_WEBHOOK_ENABLED', true),
        'path' => env('TELEGRAM_WEBHOOK_PATH', 'telegram/webhook'),
        'secret' => env('TELEGRAM_WEBHOOK_SECRET'),
    ],

    'polling' => [
        'limit' => (int) env('TELEGRAM_POLL_LIMIT', 100),
        'timeout' => (int) env('TELEGRAM_POLL_TIMEOUT', 30),
        'sleep_ms' => (int) env('TELEGRAM_POLL_SLEEP_MS', 1000),
        'lock_seconds' => (int) env('TELEGRAM_POLL_LOCK_SECONDS', 40),
        'offset_cache_key' => env('TELEGRAM_POLL_OFFSET_CACHE_KEY', 'telegram.polling.offset'),
    ],

    'http' => [
        'timeout' => (int) env('TELEGRAM_HTTP_TIMEOUT', 20),
        'connect_timeout' => (int) env('TELEGRAM_HTTP_CONNECT_TIMEOUT', 10),
        'retry_times' => (int) env('TELEGRAM_HTTP_RETRY_TIMES', 3),
        'retry_sleep_ms' => (int) env('TELEGRAM_HTTP_RETRY_SLEEP_MS', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Update handlers
    |--------------------------------------------------------------------------
    |
    | Register classes implementing UpdateHandlerInterface.
    |
    */
    'handlers' => [],
];
