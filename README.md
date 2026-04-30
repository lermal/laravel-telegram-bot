# lermal/laravel-telegram

Laravel package for Telegram Bot API with webhook and polling support.

Language: **English** | [Русский](README.ru.md)

## Table of contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Architecture](#architecture)
- [Quick start](#quick-start)
- [Console commands](#console-commands)
- [Examples](#examples)
- [Telemetry events](#telemetry-events)
- [Rate limits and anti-spam](#rate-limits-and-anti-spam)
- [Production recommendations](#production-recommendations)
- [Migration notes (0.x to 1.0)](#migration-notes-0x-to-10)
- [Smoke test checklist](#smoke-test-checklist)
- [GitHub Wiki setup](#github-wiki-setup)

## Features

- Telegram API client with typed methods and raw API calls.
- Webhook mode with secret validation.
- Polling mode with:
  - graceful error handling,
  - error backoff,
  - `--max-iterations`,
  - `--stop-when-empty`.
- Update dispatcher with handler pipeline.
- Built-in anti-spam for identical chat commands.
- Telemetry events for monitoring polling lifecycle and update processing.

## Requirements

- PHP `^8.3`
- Laravel `^13.0` components:
  - `illuminate/support`
  - `illuminate/http`
  - `illuminate/console`

## Installation

```bash
composer require lermal/laravel-telegram
php artisan vendor:publish --tag=laravel-telegram-config
```

Or use package installer:

```bash
php artisan telegram:install
```

## Configuration

### Minimum `.env`

```env
TELEGRAM_BOT_TOKEN=123456:ABCDEF
TELEGRAM_WEBHOOK_ENABLED=true
TELEGRAM_WEBHOOK_PATH=telegram/webhook
TELEGRAM_WEBHOOK_SECRET=your-generated-secret
```

### Polling and limits

```env
TELEGRAM_POLL_LIMIT=100
TELEGRAM_POLL_TIMEOUT=30
TELEGRAM_POLL_SLEEP_MS=1000
TELEGRAM_POLL_ERROR_BACKOFF_INITIAL_MS=500
TELEGRAM_POLL_ERROR_BACKOFF_MAX_MS=8000

TELEGRAM_RATE_LIMIT_RPS=30
TELEGRAM_RATE_LIMIT_MAX_IDENTICAL_COMMANDS=3
TELEGRAM_RATE_LIMIT_WINDOW_SECONDS=10
```

All options are available in `config/telegram.php`.

## Architecture

- `TelegramClientInterface` / `TelegramClient`: HTTP communication with Telegram API.
- `UpdateDispatcher`: iterates configured handlers and calls `supports` + `handle`.
- `WebhookController`: validates secret + payload, then dispatches update.
- `PollUpdatesCommand`: long-polling loop with offset tracking, retry-safe behavior, and telemetry.

## Quick start

### Basic message

```php
use Telegram;

Telegram::sendMessage([
    'chat_id' => 123456789,
    'text' => 'Hello from Laravel',
]);
```

### Raw API call

```php
app(\Lermal\LaravelTelegram\Support\RawApiCaller::class)
    ->call('setMyCommands', [
        'commands' => [
            ['command' => 'start', 'description' => 'Start bot'],
        ],
    ]);
```

### Register handlers

```php
// config/telegram.php
return [
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
        App\Telegram\Handlers\GetWebhookInfoExample::class,
        App\Telegram\Handlers\SetMyCommandsExample::class,
        App\Telegram\Handlers\EditMessageCaptionExample::class,
        App\Telegram\Handlers\SendVideoExample::class,
        App\Telegram\Handlers\SendAudioExample::class,
        App\Telegram\Handlers\SendVoiceExample::class,
    ],
];
```

## Console commands

- `php artisan telegram:webhook:set {url?}`
- `php artisan telegram:webhook:info`
- `php artisan telegram:webhook:delete {--drop-pending-updates}`
- `php artisan telegram:poll {--once} {--max-iterations=} {--stop-when-empty}`
- `php artisan telegram:install`

Useful command examples:

```bash
# One polling iteration
php artisan telegram:poll --once

# Stop after two loops
php artisan telegram:poll --max-iterations=2

# Stop when no updates are returned
php artisan telegram:poll --stop-when-empty

# Set webhook explicitly
php artisan telegram:webhook:set "https://your-domain.com/telegram/webhook"
```

## Examples

The repository contains practical examples in `examples/`:

- `StartCommandHandler.php` - `/start` handler with inline keyboard.
- `ExampleCallbackHandler.php` - callback query processing.
- `EchoMessageHandler.php` - echo text handler.
- `PhotoCommandHandler.php` - send photo flow.
- `DocumentCommandHandler.php` - send document flow.
- `InlineKeyboardDemoHandler.php` - inline keyboard with multiple actions.
- `InlineKeyboardCallbackRouterHandler.php` - callback routing with prefixes.
- `ReplyKeyboardDemoHandler.php` - reply keyboard demonstration.
- `ReplyKeyboardResponseHandler.php` - reply keyboard answer processing.
- `GetWebhookInfoExample.php` - command-style `getWebhookInfo`.
- `SetMyCommandsExample.php` - command-style `setMyCommands`.
- `EditMessageCaptionExample.php` - caption edit example.
- `SendVideoExample.php` - `sendVideo` example (supports `/video` and `/video <source>`).
- `SendAudioExample.php` - `sendAudio` example (supports `/audio` and `/audio <source>`).
- `SendVoiceExample.php` - `sendVoice` example (supports `/voice` and `/voice <source>`).

Additional client methods available:

- `getWebhookInfo()`
- `setMyCommands(array $commands)`
- `editMessageCaption(array $payload)`
- `sendVideo(array $payload)`
- `sendAudio(array $payload)`
- `sendVoice(array $payload)`

## Telemetry events

You can subscribe to package events in your application service provider:

```php
use Illuminate\Support\Facades\Event;
use Lermal\LaravelTelegram\Events\PollingError;
use Lermal\LaravelTelegram\Events\PollingStarted;
use Lermal\LaravelTelegram\Events\PollingStopped;
use Lermal\LaravelTelegram\Events\UpdateProcessed;
use Lermal\LaravelTelegram\Events\UpdateProcessingFailed;

Event::listen(PollingStarted::class, function (PollingStarted $event): void {
    logger()->info('Telegram polling started', [
        'instance_id' => $event->instanceId,
    ]);
});

Event::listen(PollingStopped::class, function (PollingStopped $event): void {
    logger()->info('Telegram polling stopped', [
        'instance_id' => $event->instanceId,
        'stopped_by_signal' => $event->stoppedBySignal,
    ]);
});

Event::listen(PollingError::class, function (PollingError $event): void {
    logger()->error('Telegram polling error', [
        'instance_id' => $event->instanceId,
        'message' => $event->message,
    ]);
});

Event::listen(UpdateProcessed::class, function (UpdateProcessed $event): void {
    logger()->info('Telegram update processed', [
        'instance_id' => $event->instanceId,
        'update_id' => $event->updateId,
    ]);
});

Event::listen(UpdateProcessingFailed::class, function (UpdateProcessingFailed $event): void {
    logger()->error('Telegram update processing failed', [
        'instance_id' => $event->instanceId,
        'update_id' => $event->updateId,
        'message' => $event->message,
    ]);
});
```

## Rate limits and anti-spam

Outgoing API calls:

- `TELEGRAM_RATE_LIMIT_RPS` controls max requests per second.
- requests above threshold wait in in-process queue.

Incoming commands anti-spam:

- `TELEGRAM_RATE_LIMIT_MAX_IDENTICAL_COMMANDS`
- `TELEGRAM_RATE_LIMIT_WINDOW_SECONDS`

These limits are applied per chat and command token.

## Production recommendations

- Prefer `webhook` for production with HTTPS and secret token.
- Use `polling` mainly for local/dev, fallback scenarios, or controlled workers.
- Keep `TELEGRAM_HTTP_TIMEOUT` greater than polling timeout (package already guards this).
- Store logs for `PollingError` and `UpdateProcessingFailed`.
- Register only handlers that implement `UpdateHandlerInterface`.

## Migration notes (0.x to 1.0)

- Replace sample handlers that do not implement `UpdateHandlerInterface`.
- Handle webhook validation responses (`422`) if your edge retries malformed payloads.
- Integrate telemetry events into your monitoring/alerts.
- Review polling options:
  - `--max-iterations`
  - `--stop-when-empty`
  - error backoff env vars

## Smoke test checklist

- Set and verify webhook:
  - `php artisan telegram:webhook:set "https://your-domain.com/telegram/webhook"`
  - `php artisan telegram:webhook:info`
- Run one polling iteration:
  - `php artisan telegram:poll --once`
- Verify polling controls:
  - `php artisan telegram:poll --max-iterations=2`
  - `php artisan telegram:poll --stop-when-empty`
- Trigger one valid update and one failing update, then confirm logs and telemetry events.

## GitHub Wiki setup

If you want documentation organized as GitHub Wiki:

1. Open your repository on GitHub.
2. Go to `Settings` -> `General` -> `Features`.
3. Enable `Wikis`.
4. Open the `Wiki` tab and create pages, for example:
   - `Home`
   - `Installation`
   - `Configuration`
   - `Handlers and Examples`
   - `Polling and Webhook Operations`
   - `Troubleshooting`
5. Keep `README.md` as a compact entry point and add links to wiki pages.

Recommended approach:

- `README.md`: quick start + essential commands.
- Wiki: deep operational docs, FAQ, troubleshooting, architecture notes.

## Migration notes (0.x to 1.0)

- Replace any sample handlers that don't implement `UpdateHandlerInterface`.
- Handle webhook validation responses (`422`) if your gateway retries malformed payloads.
- Use telemetry events for monitoring (`PollingStarted`, `PollingStopped`, `PollingError`, `UpdateProcessed`, `UpdateProcessingFailed`).

## Smoke test checklist

- Set and verify webhook:
  - `php artisan telegram:webhook:set "https://your-domain.com/telegram/webhook"`
  - `php artisan telegram:webhook:info`
- Run one polling iteration:
  - `php artisan telegram:poll --once`
- Verify polling controls:
  - `php artisan telegram:poll --max-iterations=2`
  - `php artisan telegram:poll --stop-when-empty`
