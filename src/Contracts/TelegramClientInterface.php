<?php

namespace Lermal\LaravelTelegram\Contracts;

interface TelegramClientInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function call(string $method, array $payload = []): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getUpdates(?int $offset = null, ?int $limit = null, ?int $timeout = null): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function sendMessage(array $payload): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function editMessageText(array $payload): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function deleteMessage(array $payload): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function sendPhoto(array $payload): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function sendDocument(array $payload): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function answerCallbackQuery(array $payload): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function editMessageCaption(array $payload): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function sendVideo(array $payload): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function sendAudio(array $payload): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function sendVoice(array $payload): array;

    /**
     * @param  array<string, mixed>  $commands
     * @return array<string, mixed>
     */
    public function setMyCommands(array $commands): array;

    /**
     * @return array<string, mixed>
     */
    public function setWebhook(string $url, ?string $secret = null): array;

    /**
     * @return array<string, mixed>
     */
    public function getWebhookInfo(): array;

    /**
     * @return array<string, mixed>
     */
    public function deleteWebhook(bool $dropPendingUpdates = false): array;
}
