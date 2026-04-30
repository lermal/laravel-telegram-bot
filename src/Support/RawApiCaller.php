<?php

namespace Lermal\LaravelTelegram\Support;

use Lermal\LaravelTelegram\Contracts\TelegramClientInterface;

class RawApiCaller
{
    public function __construct(private readonly TelegramClientInterface $client) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function call(string $method, array $payload = []): array
    {
        return $this->client->call($method, $payload);
    }
}
