<?php

namespace App\Telegram\Handlers;

use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Facades\Telegram;

class StartCommandHandler implements UpdateHandlerInterface
{
    public function supports(array $update): bool
    {
        // This handler runs only for the /start command.
        return isset($update['message']['text'], $update['message']['chat']['id'])
            && trim((string) $update['message']['text']) === '/start';
    }

    public function handle(array $update): void
    {
        $chatId = (int) $update['message']['chat']['id'];

        Telegram::sendMessage([
            'chat_id' => $chatId,
            // The bot sends a welcome message with an inline button.
            'text' => 'Hello! This is a /start command handler example.',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Click me',
                            'callback_data' => 'example:click',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
