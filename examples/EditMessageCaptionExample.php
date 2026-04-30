<?php

namespace App\Telegram\Handlers;

use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Facades\Telegram;

class EditMessageCaptionExample implements UpdateHandlerInterface
{
    public function supports(array $update): bool
    {
        return isset($update['message']['text'], $update['message']['chat']['id'])
            && trim((string) $update['message']['text']) === '/editcaption';
    }

    public function handle(array $update): void
    {
        $chatId = (int) $update['message']['chat']['id'];
        $messageId = $update['message']['reply_to_message']['message_id'] ?? null;

        if (! is_int($messageId)) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Reply to a media message and use /editcaption.',
            ]);

            return;
        }

        Telegram::editMessageCaption([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'caption' => 'Updated caption',
        ]);
    }
}
