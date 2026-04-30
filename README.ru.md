# lermal/laravel-telegram

Laravel-пакет для Telegram Bot API с поддержкой webhook и polling.

Язык: [English](README.md) | **Русский**

## Оглавление

- [Возможности](#возможности)
- [Требования](#требования)
- [Установка](#установка)
- [Конфигурация](#конфигурация)
- [Архитектура](#архитектура)
- [Быстрый старт](#быстрый-старт)
- [Консольные команды](#консольные-команды)
- [Examples](#examples)
- [Telemetry-события](#telemetry-события)
- [Rate limits и anti-spam](#rate-limits-и-anti-spam)
- [Рекомендации для production](#рекомендации-для-production)
- [Migration notes (0.x to 1.0)](#migration-notes-0x-to-10)
- [Smoke test чеклист](#smoke-test-чеклист)
- [Настройка GitHub Wiki](#настройка-github-wiki)

## Возможности

- Telegram API-клиент с методами и raw API вызовами.
- Режим webhook с проверкой secret token.
- Режим polling с:
  - обработкой ошибок без падения процесса,
  - error backoff,
  - `--max-iterations`,
  - `--stop-when-empty`.
- Dispatcher апдейтов с pipeline хэндлеров.
- Встроенный anti-spam одинаковых команд.
- Telemetry-события для мониторинга polling и обработки update.

## Требования

- PHP `^8.3`
- Компоненты Laravel `^13.0`:
  - `illuminate/support`
  - `illuminate/http`
  - `illuminate/console`

## Установка

```bash
composer require lermal/laravel-telegram
php artisan vendor:publish --tag=laravel-telegram-config
```

Или используйте установщик пакета:

```bash
php artisan telegram:install
```

## Конфигурация

### Минимальный `.env`

```env
TELEGRAM_BOT_TOKEN=123456:ABCDEF
TELEGRAM_WEBHOOK_ENABLED=true
TELEGRAM_WEBHOOK_PATH=telegram/webhook
TELEGRAM_WEBHOOK_SECRET=your-generated-secret
```

### Polling и лимиты

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

Все опции доступны в `config/telegram.php`.

## Архитектура

- `TelegramClientInterface` / `TelegramClient`: HTTP-общение с Telegram API.
- `UpdateDispatcher`: проходит по хэндлерам и вызывает `supports` + `handle`.
- `WebhookController`: проверяет secret + payload, затем dispatch update.
- `PollUpdatesCommand`: polling-цикл с offset tracking, устойчивостью к ошибкам и telemetry.

## Быстрый старт

### Базовая отправка сообщения

```php
use Telegram;

Telegram::sendMessage([
    'chat_id' => 123456789,
    'text' => 'Hello from Laravel',
]);
```

### Raw API вызов

```php
app(\Lermal\LaravelTelegram\Support\RawApiCaller::class)
    ->call('setMyCommands', [
        'commands' => [
            ['command' => 'start', 'description' => 'Start bot'],
        ],
    ]);
```

### Регистрация хэндлеров

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

## Консольные команды

- `php artisan telegram:webhook:set {url?}`
- `php artisan telegram:webhook:info`
- `php artisan telegram:webhook:delete {--drop-pending-updates}`
- `php artisan telegram:poll {--once} {--max-iterations=} {--stop-when-empty}`
- `php artisan telegram:install`

Полезные примеры команд:

```bash
# Одна итерация polling
php artisan telegram:poll --once

# Остановиться после двух итераций
php artisan telegram:poll --max-iterations=2

# Остановиться, если Telegram вернул пустой batch
php artisan telegram:poll --stop-when-empty

# Явно задать webhook URL
php artisan telegram:webhook:set "https://your-domain.com/telegram/webhook"
```

## Examples

В репозитории есть практические примеры в `examples/`:

- `StartCommandHandler.php` - обработка `/start` с inline-клавиатурой.
- `ExampleCallbackHandler.php` - обработка callback query.
- `EchoMessageHandler.php` - echo-хэндлер текста.
- `PhotoCommandHandler.php` - отправка фото.
- `DocumentCommandHandler.php` - отправка документа.
- `InlineKeyboardDemoHandler.php` - inline-клавиатура с несколькими действиями.
- `InlineKeyboardCallbackRouterHandler.php` - маршрутизация callback по префиксам.
- `ReplyKeyboardDemoHandler.php` - демонстрация reply-клавиатуры.
- `ReplyKeyboardResponseHandler.php` - обработка ответов reply-клавиатуры.
- `GetWebhookInfoExample.php` - пример `getWebhookInfo`.
- `SetMyCommandsExample.php` - пример `setMyCommands`.
- `EditMessageCaptionExample.php` - пример редактирования подписи.
- `SendVideoExample.php` - пример `sendVideo` (поддерживает `/video` и `/video <source>`).
- `SendAudioExample.php` - пример `sendAudio` (поддерживает `/audio` и `/audio <source>`).
- `SendVoiceExample.php` - пример `sendVoice` (поддерживает `/voice` и `/voice <source>`).

Дополнительные методы клиента:

- `getWebhookInfo()`
- `setMyCommands(array $commands)`
- `editMessageCaption(array $payload)`
- `sendVideo(array $payload)`
- `sendAudio(array $payload)`
- `sendVoice(array $payload)`

## Telemetry-события

Вы можете подписаться на события пакета в сервис-провайдере приложения:

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

## Rate limits и anti-spam

Для исходящих API-вызовов:

- `TELEGRAM_RATE_LIMIT_RPS` задаёт максимум запросов в секунду.
- запросы выше лимита ждут слот во внутренней очереди процесса.

Для входящих команд anti-spam:

- `TELEGRAM_RATE_LIMIT_MAX_IDENTICAL_COMMANDS`
- `TELEGRAM_RATE_LIMIT_WINDOW_SECONDS`

Лимиты применяются по `chat + command`.

## Рекомендации для production

- Для production лучше использовать `webhook` с HTTPS и secret token.
- `polling` удобен для локальной разработки, fallback-сценариев и контролируемых воркеров.
- Держите `TELEGRAM_HTTP_TIMEOUT` больше polling timeout (пакет уже учитывает это).
- Сохраняйте логи по `PollingError` и `UpdateProcessingFailed`.
- Регистрируйте только хэндлеры с `UpdateHandlerInterface`.

## Migration notes (0.x to 1.0)

- Замените sample-хэндлеры, которые не реализуют `UpdateHandlerInterface`.
- Учитывайте ответы webhook с `422`, если edge/gateway повторяет невалидные payload.
- Интегрируйте telemetry-события в мониторинг и алерты.
- Проверьте новые polling-опции:
  - `--max-iterations`
  - `--stop-when-empty`
  - env-переменные error backoff

## Smoke test чеклист

- Установить и проверить webhook:
  - `php artisan telegram:webhook:set "https://your-domain.com/telegram/webhook"`
  - `php artisan telegram:webhook:info`
- Запустить одну итерацию polling:
  - `php artisan telegram:poll --once`
- Проверить новые polling-опции:
  - `php artisan telegram:poll --max-iterations=2`
  - `php artisan telegram:poll --stop-when-empty`
- Отправить один валидный update и один с ошибкой, затем проверить логи и telemetry-события.

## Настройка GitHub Wiki

Если хочешь вынести подробную документацию в GitHub Wiki:

1. Открой репозиторий на GitHub.
2. Перейди в `Settings` -> `General` -> `Features`.
3. Включи `Wikis`.
4. Открой вкладку `Wiki` и создай страницы, например:
   - `Home`
   - `Installation`
   - `Configuration`
   - `Handlers and Examples`
   - `Polling and Webhook Operations`
   - `Troubleshooting`
5. Оставь `README.md` как краткую точку входа и добавь ссылки на Wiki-страницы.

Рекомендуемый подход:

- `README.md`: быстрый старт и ключевые команды.
- Wiki: подробная эксплуатационная документация, FAQ, troubleshooting, архитектурные заметки.

## Migration notes (0.x to 1.0)

- Замените sample-хэндлеры, которые не реализуют `UpdateHandlerInterface`.
- Учитывайте ответы webhook с `422`, если ваш gateway повторяет невалидные payload.
- Используйте telemetry-события для мониторинга (`PollingStarted`, `PollingStopped`, `PollingError`, `UpdateProcessed`, `UpdateProcessingFailed`).

## Smoke test чеклист

- Установить и проверить webhook:
  - `php artisan telegram:webhook:set "https://your-domain.com/telegram/webhook"`
  - `php artisan telegram:webhook:info`
- Запустить одну итерацию polling:
  - `php artisan telegram:poll --once`
- Проверить новые опции polling:
  - `php artisan telegram:poll --max-iterations=2`
  - `php artisan telegram:poll --stop-when-empty`
