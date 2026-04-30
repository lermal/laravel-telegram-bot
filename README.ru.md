# lermal/laravel-telegram-bot

Laravel-пакет для Telegram Bot API с поддержкой webhook и polling.

Язык: [English](README.md) | **Русский**

## Возможности

- Telegram API-клиент с типизированными методами и raw API-вызовами.
- Режим webhook с проверкой secret.
- Режим polling с retry, backoff, `--max-iterations` и `--stop-when-empty`.
- Диспетчер обновлений с pipeline хэндлеров.
- Встроенный anti-spam повторяющихся команд.
- Telemetry-события для жизненного цикла polling и обработки update.

## Быстрый старт

```bash
composer require lermal/laravel-telegram-bot
php artisan telegram:install
```

Далее настройте хэндлеры в `config/telegram.php`.

## Требования

- PHP 8.3+
- Laravel 13+

## Основные команды

- `php artisan telegram:webhook:set {url?}`
- `php artisan telegram:webhook:info`
- `php artisan telegram:webhook:delete {--drop-pending-updates}`
- `php artisan telegram:poll {--once} {--max-iterations=} {--stop-when-empty}`
- `php artisan telegram:install`

## Документация

Подробная документация, архитектура, примеры и troubleshooting:
<https://github.com/lermal/laravel-telegram-bot/wiki>

## Поддержка

- Issues: <https://github.com/lermal/laravel-telegram-bot/issues>
- Source: <https://github.com/lermal/laravel-telegram-bot>

## Тестирование

```bash
composer test
```

## Лицензия

MIT
