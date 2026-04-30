<?php

namespace App\Telegram\Handlers;

use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Facades\Telegram;

class InlineKeyboardDemoHandler implements UpdateHandlerInterface
{
    public function supports(array $update): bool
    {
        // This handler runs only for the /inline command.
        return isset($update['message']['text'], $update['message']['chat']['id'])
            && trim((string) $update['message']['text']) === '/inline';
    }

    public function handle(array $update): void
    {
        $chatId = (int) $update['message']['chat']['id'];

        Telegram::sendMessage([
            'chat_id' => $chatId,
            // The bot sends two inline buttons with different callback actions.
            'text' => 'Choose an inline action:',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Like',
                            'callback_data' => 'inline:like',
                        ],
                        [
                            'text' => 'Dislike',
                            'callback_data' => 'inline:dislike',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
