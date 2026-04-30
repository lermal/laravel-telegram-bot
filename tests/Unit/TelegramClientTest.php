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

it('gets webhook info', function (): void {
    Http::fake([
        'https://api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => [
                'url' => 'https://example.com/telegram/webhook',
                'pending_update_count' => 0,
            ],
        ]),
    ]);

    /** @var TelegramClientInterface $client */
    $client = $this->app->make(TelegramClientInterface::class);
    $result = $client->getWebhookInfo();

    expect($result)->toBe([
        'url' => 'https://example.com/telegram/webhook',
        'pending_update_count' => 0,
    ]);
});

it('sets bot commands', function (): void {
    Http::fake([
        'https://api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['ok' => true],
        ]),
    ]);

    /** @var TelegramClientInterface $client */
    $client = $this->app->make(TelegramClientInterface::class);
    $result = $client->setMyCommands([
        ['command' => 'start', 'description' => 'Start bot'],
    ]);

    expect($result)->toBe(['ok' => true]);
    Http::assertSent(function (\Illuminate\Http\Client\Request $request): bool {
        return $request['commands'][0]['command'] === 'start'
            && $request['commands'][0]['description'] === 'Start bot';
    });
});

it('proxies media and caption methods', function (): void {
    Http::fake([
        'https://api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 99],
        ]),
    ]);

    /** @var TelegramClientInterface $client */
    $client = $this->app->make(TelegramClientInterface::class);

    expect($client->sendVideo(['chat_id' => 1, 'video' => 'file-id'])['message_id'])->toBe(99);
    expect($client->sendAudio(['chat_id' => 1, 'audio' => 'file-id'])['message_id'])->toBe(99);
    expect($client->sendVoice(['chat_id' => 1, 'voice' => 'file-id'])['message_id'])->toBe(99);
    expect($client->editMessageCaption(['chat_id' => 1, 'message_id' => 2, 'caption' => 'Updated'])['message_id'])->toBe(99);
});
