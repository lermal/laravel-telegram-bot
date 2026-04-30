<?php

namespace App\Telegram\Handlers;

use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Facades\Telegram;

class ReplyKeyboardDemoHandler implements UpdateHandlerInterface
{
    public function supports(array $update): bool
    {
        // This handler runs only for the /keyboard command.
        return isset($update['message']['text'], $update['message']['chat']['id'])
            && trim((string) $update['message']['text']) === '/keyboard';
    }

    public function handle(array $update): void
    {
        $chatId = (int) $update['message']['chat']['id'];

        Telegram::sendMessage([
            'chat_id' => $chatId,
            // The bot sends a reply keyboard that can be pressed as normal text buttons.
            'text' => 'Pick an option from the reply keyboard:',
            'reply_markup' => [
                'keyboard' => [
                    [
                        ['text' => 'Show profile'],
                        ['text' => 'Show help'],
                    ],
                    [
                        ['text' => 'Hide keyboard'],
                    ],
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ],
        ]);
    }
}
