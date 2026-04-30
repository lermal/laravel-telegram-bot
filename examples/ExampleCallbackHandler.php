<?php

namespace App\Telegram\Handlers;

use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Facades\Telegram;

class ExampleCallbackHandler implements UpdateHandlerInterface
{
    public function supports(array $update): bool
    {
        // This handler runs when the example inline button is clicked.
        return isset($update['callback_query']['id'], $update['callback_query']['data'])
            && $update['callback_query']['data'] === 'example:click';
    }

    public function handle(array $update): void
    {
        $callbackQueryId = (string) $update['callback_query']['id'];
        $chatId = (int) $update['callback_query']['message']['chat']['id'];
        $messageId = (int) $update['callback_query']['message']['message_id'];

        Telegram::answerCallbackQuery([
            'callback_query_id' => $callbackQueryId,
            // Telegram shows this short popup to confirm the click.
            'text' => 'Button clicked',
        ]);

        Telegram::editMessageText([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            // The original message is replaced after callback handling.
            'text' => 'Thanks, callback processed.',
        ]);
    }
}
