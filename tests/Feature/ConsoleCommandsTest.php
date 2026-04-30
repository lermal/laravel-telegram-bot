<?php

use Lermal\LaravelTelegram\Contracts\TelegramClientInterface;
use Lermal\LaravelTelegram\Dispatching\UpdateDispatcher;

it('sets webhook using provided url argument', function (): void {
    $client = Mockery::mock(TelegramClientInterface::class);
    $client
        ->shouldReceive('setWebhook')
        ->once()
        ->with('https://example.com/hook', null)
        ->andReturn([]);
    $this->app->instance(TelegramClientInterface::class, $client);

    $this->artisan('telegram:webhook:set', [
        'url' => 'https://example.com/hook',
    ])->assertSuccessful();
});

it('deletes webhook with drop pending option', function (): void {
    $client = Mockery::mock(TelegramClientInterface::class);
    $client
        ->shouldReceive('deleteWebhook')
        ->once()
        ->with(true)
        ->andReturn([]);
    $this->app->instance(TelegramClientInterface::class, $client);

    $this->artisan('telegram:webhook:delete', [
        '--drop-pending-updates' => true,
    ])->assertSuccessful();
});

it('polls updates once and stores next offset', function (): void {
    config()->set('telegram.polling.offset_cache_key', 'telegram:test:polling:offset');
    config()->set('telegram.polling.limit', 100);
    config()->set('telegram.polling.timeout', 10);
    config()->set('telegram.polling.lock_seconds', 5);

    $client = Mockery::mock(TelegramClientInterface::class);
    $client
        ->shouldReceive('getUpdates')
        ->once()
        ->with(0, 100, 10)
        ->andReturn([
            ['update_id' => 11, 'message' => ['text' => 'a']],
            ['update_id' => 15, 'message' => ['text' => 'b']],
        ]);
    $this->app->instance(TelegramClientInterface::class, $client);

    $dispatcher = Mockery::mock(UpdateDispatcher::class);
    $dispatcher->shouldReceive('dispatch')->twice();
    $this->app->instance(UpdateDispatcher::class, $dispatcher);

    $this->artisan('telegram:poll', ['--once' => true])->assertSuccessful();

    expect(cache()->get('telegram:test:polling:offset'))->toBe(16);
});
