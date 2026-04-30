<?php

namespace App\Telegram\Handlers;

use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Facades\Telegram;

class EchoMessageHandler implements UpdateHandlerInterface
{
    public function supports(array $update): bool
    {
        // This handler runs for text messages that include a chat ID.
        return isset($update['message']['text'], $update['message']['chat']['id']);
    }

    public function handle(array $update): void
    {
        $chatId = (int) $update['message']['chat']['id'];
        $text = (string) $update['message']['text'];

        Telegram::sendMessage([
            'chat_id' => $chatId,
            // The bot sends the same text back to the user.
            'text' => 'You wrote: '.$text,
        ]);
    }
}
