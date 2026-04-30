<?php

return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'), // your bot token

    'base_url' => env('TELEGRAM_BASE_URL', 'https://api.telegram.org'), // base URL of the Telegram API

    'webhook' => [
        'enabled' => (bool) env('TELEGRAM_WEBHOOK_ENABLED', true), // whether to enable webhook
        'path' => env('TELEGRAM_WEBHOOK_PATH', 'telegram/webhook'), // path to the webhook
        'secret' => env('TELEGRAM_WEBHOOK_SECRET'), // secret token for the webhook
    ],

    'polling' => [
        'limit' => (int) env('TELEGRAM_POLL_LIMIT', 100), // how many updates to fetch per request
        'timeout' => (int) env('TELEGRAM_POLL_TIMEOUT', 30), // timeout for the request
        'sleep_ms' => (int) env('TELEGRAM_POLL_SLEEP_MS', 1000), // how long to sleep between requests
        'lock_seconds' => (int) env('TELEGRAM_POLL_LOCK_SECONDS', 40), // how long to lock the polling
        'offset_cache_key' => env('TELEGRAM_POLL_OFFSET_CACHE_KEY', 'telegram.polling.offset'), // cache key for the offset
    ],

    'http' => [
        'timeout' => (int) env('TELEGRAM_HTTP_TIMEOUT', 20), // timeout for the request
        'connect_timeout' => (int) env('TELEGRAM_HTTP_CONNECT_TIMEOUT', 10), // timeout for the connection
        'retry_times' => (int) env('TELEGRAM_HTTP_RETRY_TIMES', 3), // how many times to retry the request
        'retry_sleep_ms' => (int) env('TELEGRAM_HTTP_RETRY_SLEEP_MS', 200), // how long to sleep between retries
    ],

    'rate_limit' => [
        'api' => [
            'rps' => (int) env('TELEGRAM_RATE_LIMIT_RPS', 30), // max API requests per second, i don't recommend to change this value more than 30
            'key' => env('TELEGRAM_RATE_LIMIT_KEY', 'telegram:api:rps'), // rate limit key
            'queue_lock_key' => env('TELEGRAM_RATE_LIMIT_QUEUE_LOCK_KEY', 'telegram:api:queue:lock'), // queue lock key
            'queue_lock_seconds' => (int) env('TELEGRAM_RATE_LIMIT_QUEUE_LOCK_SECONDS', 5), // how long to lock the queue
            'wait_sleep_ms' => (int) env('TELEGRAM_RATE_LIMIT_WAIT_SLEEP_MS', 50), // how long to wait for a slot to be available
        ],
        'commands' => [
            'max_identical' => (int) env('TELEGRAM_RATE_LIMIT_MAX_IDENTICAL_COMMANDS', 3), // how many identical commands are allowed in window
            'window_seconds' => (int) env('TELEGRAM_RATE_LIMIT_WINDOW_SECONDS', 10), // how long the window is
            'cache_key_prefix' => env('TELEGRAM_RATE_LIMIT_COMMANDS_CACHE_PREFIX', 'telegram:rate-limit:commands'), // cache key prefix
        ],
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
