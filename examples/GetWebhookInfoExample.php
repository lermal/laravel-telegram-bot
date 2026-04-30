<?php

namespace App\Telegram\Handlers;

use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Facades\Telegram;

class GetWebhookInfoExample implements UpdateHandlerInterface
{
    public function supports(array $update): bool
    {
        return isset($update['message']['text'], $update['message']['chat']['id'])
            && trim((string) $update['message']['text']) === '/webhookinfo';
    }

    public function handle(array $update): void
    {
        $chatId = (int) $update['message']['chat']['id'];
        $info = Telegram::getWebhookInfo();

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => 'Webhook info: '.json_encode($info, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
