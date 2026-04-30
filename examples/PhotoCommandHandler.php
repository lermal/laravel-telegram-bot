<?php

namespace App\Telegram\Handlers;

use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Facades\Telegram;

class PhotoCommandHandler implements UpdateHandlerInterface
{
    public function supports(array $update): bool
    {
        // This handler runs only for the /photo command.
        return isset($update['message']['text'], $update['message']['chat']['id'])
            && trim((string) $update['message']['text']) === '/photo';
    }

    public function handle(array $update): void
    {
        $chatId = (int) $update['message']['chat']['id'];

        Telegram::sendPhoto([
            'chat_id' => $chatId,
            // The bot sends an image by URL with a caption.
            'photo' => 'https://picsum.photos/800/400',
            'caption' => 'Random demo photo from /photo command.',
        ]);
    }
}
