<?php

namespace Lermal\LaravelTelegram;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Lermal\LaravelTelegram\Contracts\TelegramClientInterface;
use Lermal\LaravelTelegram\Exceptions\TelegramApiException;

class TelegramClient implements TelegramClientInterface
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $botToken,
        private readonly string $baseUrl,
        private readonly int $timeout,
        private readonly int $connectTimeout,
        private readonly int $retryTimes,
        private readonly int $retrySleepMs,
    ) {}

    public function call(string $method, array $payload = []): array
    {
        $url = sprintf(
            '%s/bot%s/%s',
            rtrim($this->baseUrl, '/'),
            $this->botToken,
            ltrim($method, '/')
        );

        try {
            /** @var array<string, mixed> $response */
            $response = $this->http
                ->acceptJson()
                ->asJson()
                ->connectTimeout($this->connectTimeout)
                ->timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleepMs)
                ->post($url, $payload)
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw new TelegramApiException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        if (($response['ok'] ?? false) !== true) {
            $description = (string) ($response['description'] ?? 'Telegram API request failed.');

            throw new TelegramApiException($description);
        }

        return (array) ($response['result'] ?? []);
    }

    public function getUpdates(?int $offset = null, ?int $limit = null, ?int $timeout = null): array
    {
        $payload = array_filter(
            [
                'offset' => $offset,
                'limit' => $limit,
                'timeout' => $timeout,
            ],
            static fn (mixed $value): bool => $value !== null
        );

        /** @var array<int, array<string, mixed>> $result */
        $result = $this->call('getUpdates', $payload);

        return $result;
    }

    public function sendMessage(array $payload): array
    {
        return $this->call('sendMessage', $payload);
    }

    public function editMessageText(array $payload): array
    {
        return $this->call('editMessageText', $payload);
    }

    public function deleteMessage(array $payload): array
    {
        return $this->call('deleteMessage', $payload);
    }

    public function sendPhoto(array $payload): array
    {
        return $this->call('sendPhoto', $payload);
    }

    public function sendDocument(array $payload): array
    {
        return $this->call('sendDocument', $payload);
    }

    public function answerCallbackQuery(array $payload): array
    {
        return $this->call('answerCallbackQuery', $payload);
    }

    public function setWebhook(string $url, ?string $secret = null): array
    {
        $payload = ['url' => $url];

        if ($secret !== null && $secret !== '') {
            $payload['secret_token'] = $secret;
        }

        return $this->call('setWebhook', $payload);
    }

    public function deleteWebhook(bool $dropPendingUpdates = false): array
    {
        return $this->call('deleteWebhook', [
            'drop_pending_updates' => $dropPendingUpdates,
        ]);
    }
}
