<?php

namespace App\Telegram\Handlers;

use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Facades\Telegram;

class SendVideoExample implements UpdateHandlerInterface
{
    public function supports(array $update): bool
    {
        return isset($update['message']['text'], $update['message']['chat']['id'])
            && str_starts_with(trim((string) $update['message']['text']), '/video');
    }

    public function handle(array $update): void
    {
        $chatId = (int) $update['message']['chat']['id'];
        $text = trim((string) ($update['message']['text'] ?? ''));
        $source = $this->extractSource($text) ?? 'https://assets.mixkit.co/videos/1779/1779-720.mp4';

        Telegram::sendVideo([
            'chat_id' => $chatId,
            'video' => $source,
            'caption' => 'Demo video',
        ]);
    }

    private function extractSource(string $text): ?string
    {
        $parts = preg_split('/\s+/', $text, 2) ?: [];
        $source = trim((string) ($parts[1] ?? ''));

        return $source !== '' ? $source : null;
    }
}
