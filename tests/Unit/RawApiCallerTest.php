<?php

use Lermal\LaravelTelegram\Contracts\TelegramClientInterface;
use Lermal\LaravelTelegram\Support\RawApiCaller;

it('forwards call to telegram client', function (): void {
    $client = Mockery::mock(TelegramClientInterface::class);
    $client
        ->shouldReceive('call')
        ->once()
        ->with('setMyCommands', ['commands' => []])
        ->andReturn(['ok' => true]);

    $caller = new RawApiCaller($client);

    expect($caller->call('setMyCommands', ['commands' => []]))->toBe(['ok' => true]);
});
