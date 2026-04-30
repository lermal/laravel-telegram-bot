<?php

namespace Lermal\LaravelTelegram\Facades;

use Illuminate\Support\Facades\Facade;
use Lermal\LaravelTelegram\Contracts\TelegramClientInterface;

/**
 * @method static array<string, mixed> call(string $method, array $payload = [])
 * @method static array<string, mixed> sendMessage(array $payload)
 * @method static array<string, mixed> editMessageText(array $payload)
 * @method static array<string, mixed> deleteMessage(array $payload)
 * @method static array<string, mixed> sendPhoto(array $payload)
 * @method static array<string, mixed> sendDocument(array $payload)
 * @method static array<string, mixed> answerCallbackQuery(array $payload)
 * @method static array<string, mixed> setWebhook(string $url, ?string $secret = null)
 * @method static array<string, mixed> deleteWebhook(bool $dropPendingUpdates = false)
 *
 * @see TelegramClientInterface
 */
class Telegram extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TelegramClientInterface::class;
    }
}
