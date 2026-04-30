# lermal/laravel-telegram

Laravel package for Telegram Bot API with webhook and polling support.

## Installation

```bash
composer require lermal/laravel-telegram
php artisan vendor:publish --tag=laravel-telegram-config
```

Or run:

```bash
php artisan telegram:install
```

## Configuration

```env
TELEGRAM_BOT_TOKEN=123456:ABCDEF
TELEGRAM_WEBHOOK_ENABLED=true
TELEGRAM_WEBHOOK_PATH=telegram/webhook
TELEGRAM_WEBHOOK_SECRET=your-generated-secret
```

## Basic usage

```php
use Telegram;

Telegram::sendMessage([
    'chat_id' => 123456789,
    'text' => 'Hello from Laravel',
]);
```

## Raw API call

```php
app(\Lermal\LaravelTelegram\Support\RawApiCaller::class)
    ->call('setMyCommands', [
        'commands' => [
            ['command' => 'start', 'description' => 'Start bot'],
        ],
    ]);
```

## Commands

- `php artisan telegram:webhook:set {url?}`
- `php artisan telegram:webhook:delete {--drop-pending-updates}`
- `php artisan telegram:poll {--once}`
- `php artisan telegram:install`
