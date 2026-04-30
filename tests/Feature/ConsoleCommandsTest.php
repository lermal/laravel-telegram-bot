<?php

use Illuminate\Http\Client\ConnectionException;
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

it('shows webhook info', function (): void {
    $client = Mockery::mock(TelegramClientInterface::class);
    $client
        ->shouldReceive('getWebhookInfo')
        ->once()
        ->andReturnUsing(static fn (): array => [
            'url' => 'https://example.com/telegram/webhook',
            'pending_update_count' => 2,
        ]);
    $this->app->instance(TelegramClientInterface::class, $client);

    $this->artisan('telegram:webhook:info')->assertSuccessful();
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
            ['update_id' => 11, 'message' => ['text' => '/start foo bar']],
            ['update_id' => 15, 'callback_query' => ['data' => 'btn:confirm:42']],
        ]);
    $this->app->instance(TelegramClientInterface::class, $client);

    $dispatcher = Mockery::mock(UpdateDispatcher::class);
    $dispatcher->shouldReceive('dispatch')->twice();
    $this->app->instance(UpdateDispatcher::class, $dispatcher);

    $this->artisan('telegram:poll', ['--once' => true])
        ->expectsOutputToContain('[INFO] Command called: /start | params: foo bar')
        ->expectsOutputToContain('[INFO] Callback called: btn:confirm:42')
        ->assertSuccessful();

    expect(cache()->get('telegram:test:polling:offset'))->toBe(16);
});

it('adjusts polling timeout to be lower than http timeout', function (): void {
    config()->set('telegram.polling.limit', 100);
    config()->set('telegram.polling.timeout', 30);
    config()->set('telegram.http.timeout', 20);

    $client = Mockery::mock(TelegramClientInterface::class);
    $client
        ->shouldReceive('getUpdates')
        ->once()
        ->with(0, 100, 19)
        ->andReturn([]);
    $this->app->instance(TelegramClientInterface::class, $client);

    $dispatcher = Mockery::mock(UpdateDispatcher::class);
    $dispatcher->shouldNotReceive('dispatch');
    $this->app->instance(UpdateDispatcher::class, $dispatcher);

    $this->artisan('telegram:poll', ['--once' => true])
        ->expectsOutputToContain('[DEBUG] Polling timeout adjusted from 30 to 19 seconds to prevent HTTP client timeout.')
        ->assertSuccessful();
});

it('logs polling connection errors and does not crash in once mode', function (): void {
    config()->set('telegram.polling.limit', 100);
    config()->set('telegram.polling.timeout', 10);

    $client = Mockery::mock(TelegramClientInterface::class);
    $client
        ->shouldReceive('getUpdates')
        ->once()
        ->andThrow(new ConnectionException('cURL error 28: Operation timed out for https://api.telegram.org/bot123/getUpdates'));
    $this->app->instance(TelegramClientInterface::class, $client);

    $dispatcher = Mockery::mock(UpdateDispatcher::class);
    $dispatcher->shouldNotReceive('dispatch');
    $this->app->instance(UpdateDispatcher::class, $dispatcher);

    $this->artisan('telegram:poll', ['--once' => true])
        ->expectsOutputToContain('[ERROR] Telegram connection error while polling updates:')
        ->assertSuccessful();
});

it('logs dispatch errors and continues polling iteration', function (): void {
    config()->set('telegram.polling.offset_cache_key', 'telegram:test:polling:offset:dispatch-error');
    config()->set('telegram.polling.limit', 100);
    config()->set('telegram.polling.timeout', 10);

    $client = Mockery::mock(TelegramClientInterface::class);
    $client
        ->shouldReceive('getUpdates')
        ->once()
        ->with(0, 100, 10)
        ->andReturn([
            ['update_id' => 11, 'message' => ['text' => '/video']],
            ['update_id' => 12, 'message' => ['text' => '/start']],
        ]);
    $this->app->instance(TelegramClientInterface::class, $client);

    $dispatcher = Mockery::mock(UpdateDispatcher::class);
    $dispatcher
        ->shouldReceive('dispatch')
        ->once()
        ->andThrow(new RuntimeException('Bad Request: wrong type of the web page content'));
    $dispatcher
        ->shouldReceive('dispatch')
        ->once()
        ->andReturnNull();
    $this->app->instance(UpdateDispatcher::class, $dispatcher);

    $this->artisan('telegram:poll', ['--once' => true])
        ->expectsOutputToContain('[ERROR] Failed to process update #11: Bad Request: wrong type of the web page content')
        ->assertSuccessful();

    expect(cache()->get('telegram:test:polling:offset:dispatch-error'))->toBe(13);
});
