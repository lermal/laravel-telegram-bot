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

## Usage examples in repository

The `examples` directory contains ready-to-use handler examples:

- `examples/StartCommandHandler.php` - handles `/start` command and sends inline button.
- `examples/ExampleCallbackHandler.php` - handles button callback and edits message text.
- `examples/EchoMessageHandler.php` - echoes incoming text messages.
- `examples/PhotoCommandHandler.php` - handles `/photo` and sends a photo.
- `examples/DocumentCommandHandler.php` - handles `/document` and sends a document.
- `examples/InlineKeyboardDemoHandler.php` - sends inline keyboard with multiple actions.
- `examples/InlineKeyboardCallbackRouterHandler.php` - reacts to inline callbacks (`inline:*`).
- `examples/ReplyKeyboardDemoHandler.php` - sends a reply keyboard with text buttons.
- `examples/ReplyKeyboardResponseHandler.php` - reacts to reply keyboard button texts.

Register handlers in your application config:

```php
// config/telegram.php
return [
    // ...
    'handlers' => [
        App\Telegram\Handlers\StartCommandHandler::class,
        App\Telegram\Handlers\ExampleCallbackHandler::class,
        App\Telegram\Handlers\EchoMessageHandler::class,
        App\Telegram\Handlers\PhotoCommandHandler::class,
        App\Telegram\Handlers\DocumentCommandHandler::class,
        App\Telegram\Handlers\InlineKeyboardDemoHandler::class,
        App\Telegram\Handlers\InlineKeyboardCallbackRouterHandler::class,
        App\Telegram\Handlers\ReplyKeyboardDemoHandler::class,
        App\Telegram\Handlers\ReplyKeyboardResponseHandler::class,
    ],
];
```

For polling mode:

```bash
php artisan telegram:poll --once
```

For webhook mode:

```bash
php artisan telegram:webhook:set "https://your-domain.com/telegram/webhook"
```

## Commands

- `php artisan telegram:webhook:set {url?}`
- `php artisan telegram:webhook:delete {--drop-pending-updates}`
- `php artisan telegram:poll {--once}`
- `php artisan telegram:install`
