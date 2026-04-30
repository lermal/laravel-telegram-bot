# lermal/laravel-telegram

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
composer require lermal/laravel-telegram
php artisan telegram:install
```

Далее настройте хэндлеры в `config/telegram.php`.

## Основные команды

- `php artisan telegram:webhook:set {url?}`
- `php artisan telegram:webhook:info`
- `php artisan telegram:webhook:delete {--drop-pending-updates}`
- `php artisan telegram:poll {--once} {--max-iterations=} {--stop-when-empty}`
- `php artisan telegram:install`

## Документация

Подробная документация, архитектура, примеры и troubleshooting:
<https://github.com/lermal/laravel-telegram-bot/wiki>

## Тестирование

```bash
composer test
```
