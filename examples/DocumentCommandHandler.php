<?php

namespace App\Telegram\Handlers;

use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Facades\Telegram;

class DocumentCommandHandler implements UpdateHandlerInterface
{
    public function supports(array $update): bool
    {
        // This handler runs only for the /document command.
        return isset($update['message']['text'], $update['message']['chat']['id'])
            && trim((string) $update['message']['text']) === '/document';
    }

    public function handle(array $update): void
    {
        $chatId = (int) $update['message']['chat']['id'];

        Telegram::sendDocument([
            'chat_id' => $chatId,
            // The bot sends a document by URL.
            'document' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'caption' => 'Demo document from /document command.',
        ]);
    }
}
