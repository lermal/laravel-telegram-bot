<?php

namespace App\Telegram\Handlers;

use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Facades\Telegram;

class ReplyKeyboardResponseHandler implements UpdateHandlerInterface
{
    public function supports(array $update): bool
    {
        // This handler runs for reply keyboard button texts.
        if (! isset($update['message']['text'], $update['message']['chat']['id'])) {
            return false;
        }

        return in_array(trim((string) $update['message']['text']), [
            'Show profile',
            'Show help',
            'Hide keyboard',
        ], true);
    }

    public function handle(array $update): void
    {
        $chatId = (int) $update['message']['chat']['id'];
        $text = trim((string) $update['message']['text']);

        if ($text === 'Show profile') {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                // The bot answers the selected "Show profile" action.
                'text' => 'Demo profile: John Doe, status active.',
            ]);

            return;
        }

        if ($text === 'Show help') {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                // The bot answers the selected "Show help" action.
                'text' => 'Available commands: /start, /photo, /document, /inline, /keyboard',
            ]);

            return;
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            // The bot removes the reply keyboard on user request.
            'text' => 'Keyboard hidden.',
            'reply_markup' => [
                'remove_keyboard' => true,
            ],
        ]);
    }
}
