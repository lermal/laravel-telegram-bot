<?php

use Illuminate\Support\Facades\Http;
use Lermal\LaravelTelegram\Contracts\TelegramClientInterface;
use Lermal\LaravelTelegram\Exceptions\TelegramApiException;

it('sends telegram requests and returns result payload', function (): void {
    Http::fake([
        'https://api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 15],
        ]),
    ]);

    /** @var TelegramClientInterface $client */
    $client = $this->app->make(TelegramClientInterface::class);
    $result = $client->sendMessage([
        'chat_id' => 123,
        'text' => 'Hello',
    ]);

    expect($result)->toBe(['message_id' => 15]);
    Http::assertSentCount(1);
});

it('throws exception when telegram returns ok false', function (): void {
    Http::fake([
        'https://api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Bad Request: message text is empty',
        ], 200),
    ]);

    /** @var TelegramClientInterface $client */
    $client = $this->app->make(TelegramClientInterface::class);

    expect(fn () => $client->sendMessage(['chat_id' => 1, 'text' => '']))
        ->toThrow(TelegramApiException::class, 'Bad Request: message text is empty');
});

it('throws exception on http layer failure', function (): void {
    Http::fake([
        'https://api.telegram.org/*' => Http::response('Server error', 500),
    ]);

    /** @var TelegramClientInterface $client */
    $client = $this->app->make(TelegramClientInterface::class);

    expect(fn () => $client->sendMessage(['chat_id' => 1, 'text' => 'Hello']))
        ->toThrow(TelegramApiException::class);
});
