<?php

namespace App\Telegram\Handlers;

use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Facades\Telegram;

class SendAudioExample implements UpdateHandlerInterface
{
    public function supports(array $update): bool
    {
        return isset($update['message']['text'], $update['message']['chat']['id'])
            && str_starts_with(trim((string) $update['message']['text']), '/audio');
    }

    public function handle(array $update): void
    {
        $chatId = (int) $update['message']['chat']['id'];
        $text = trim((string) ($update['message']['text'] ?? ''));
        $source = $this->extractSource($text) ?? 'https://upload.wikimedia.org/wikipedia/ru/transcoded/0/0b/Rickroll.ogg/Rickroll.ogg.mp3';

        Telegram::sendAudio([
            'chat_id' => $chatId,
            'audio' => $source,
            'caption' => 'Audio sample',
        ]);
    }

    private function extractSource(string $text): ?string
    {
        $parts = preg_split('/\s+/', $text, 2) ?: [];
        $source = trim((string) ($parts[1] ?? ''));

        return $source !== '' ? $source : null;
    }
}
