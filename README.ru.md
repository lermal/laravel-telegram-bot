# lermal/laravel-telegram

Laravel-пакет для Telegram Bot API с поддержкой webhook и polling.

Язык: [English](README.md) | **Русский**

## Установка

```bash
composer require lermal/laravel-telegram
php artisan vendor:publish --tag=laravel-telegram-config
```

Или:

```bash
php artisan telegram:install
```

## Конфигурация

```env
TELEGRAM_BOT_TOKEN=123456:ABCDEF
TELEGRAM_WEBHOOK_ENABLED=true
TELEGRAM_WEBHOOK_PATH=telegram/webhook
TELEGRAM_WEBHOOK_SECRET=your-generated-secret
TELEGRAM_RATE_LIMIT_RPS=30
TELEGRAM_RATE_LIMIT_MAX_IDENTICAL_COMMANDS=3
TELEGRAM_RATE_LIMIT_WINDOW_SECONDS=10
```

## Базовое использование

```php
use Telegram;

Telegram::sendMessage([
    'chat_id' => 123456789,
    'text' => 'Hello from Laravel',
]);
```

## Raw API вызов

```php
app(\Lermal\LaravelTelegram\Support\RawApiCaller::class)
    ->call('setMyCommands', [
        'commands' => [
            ['command' => 'start', 'description' => 'Start bot'],
        ],
    ]);
```

## Примеры в репозитории

В директории `examples` есть готовые примеры обработчиков:

- `examples/StartCommandHandler.php` - обработка команды `/start` и отправка inline-кнопки.
- `examples/ExampleCallbackHandler.php` - обработка callback кнопки и изменение текста сообщения.
- `examples/EchoMessageHandler.php` - echo-ответ на входящий текст.
- `examples/PhotoCommandHandler.php` - обработка `/photo` и отправка фото.
- `examples/DocumentCommandHandler.php` - обработка `/document` и отправка документа.
- `examples/InlineKeyboardDemoHandler.php` - отправка inline-клавиатуры с несколькими действиями.
- `examples/InlineKeyboardCallbackRouterHandler.php` - реакция на inline callback (`inline:*`).
- `examples/ReplyKeyboardDemoHandler.php` - отправка reply-клавиатуры с текстовыми кнопками.
- `examples/ReplyKeyboardResponseHandler.php` - реакция на текст кнопок reply-клавиатуры.

Зарегистрируйте обработчики в конфиге приложения:

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

Для режима polling:

```bash
php artisan telegram:poll --once
```

Для режима webhook:

```bash
php artisan telegram:webhook:set "https://your-domain.com/telegram/webhook"
```

## Очередь и rate-limits

Исходящие вызовы Telegram API ограничиваются по скорости и ставятся в очередь внутри процесса:

- `TELEGRAM_RATE_LIMIT_RPS` - максимальное число запросов в секунду (по умолчанию: `30`).
- запросы выше лимита ждут свободный слот и выполняются позже.

Ограничение спама одинаковых команд работает по каждому чату:

- `TELEGRAM_RATE_LIMIT_MAX_IDENTICAL_COMMANDS` - сколько одинаковых команд допускается в окне (по умолчанию: `3`).
- `TELEGRAM_RATE_LIMIT_WINDOW_SECONDS` - размер окна в секундах для одинаковых команд (по умолчанию: `10`).

## Команды

- `php artisan telegram:webhook:set {url?}`
- `php artisan telegram:webhook:delete {--drop-pending-updates}`
- `php artisan telegram:poll {--once}`
- `php artisan telegram:install`
