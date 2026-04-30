<?php

namespace App\Telegram\Handlers;

use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Facades\Telegram;

class InlineKeyboardCallbackRouterHandler implements UpdateHandlerInterface
{
    public function supports(array $update): bool
    {
        // This handler runs for inline callback buttons from the /inline example.
        return isset($update['callback_query']['id'], $update['callback_query']['data'])
            && str_starts_with((string) $update['callback_query']['data'], 'inline:');
    }

    public function handle(array $update): void
    {
        $callbackQueryId = (string) $update['callback_query']['id'];
        $callbackData = (string) $update['callback_query']['data'];
        $chatId = (int) $update['callback_query']['message']['chat']['id'];
        $messageId = (int) $update['callback_query']['message']['message_id'];

        $resultText = match ($callbackData) {
            'inline:like' => 'You selected: Like',
            'inline:dislike' => 'You selected: Dislike',
            default => 'Unknown inline action',
        };

        Telegram::answerCallbackQuery([
            'callback_query_id' => $callbackQueryId,
            // Telegram shows this short popup after a button click.
            'text' => $resultText,
        ]);

        Telegram::editMessageText([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            // The original message is replaced with the selected result.
            'text' => $resultText,
        ]);
    }
}
