<?php

use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Dispatching\UpdateDispatcher;

it('dispatches update to all supporting handlers', function (): void {
    TestDispatchHandlerOne::$handled = 0;
    TestDispatchHandlerTwo::$handled = 0;

    config()->set('telegram.rate_limit.commands.max_identical', 3);
    config()->set('telegram.rate_limit.commands.window_seconds', 10);
    config()->set('telegram.rate_limit.commands.cache_key_prefix', 'telegram:test:dispatch:supports');

    $dispatcher = new UpdateDispatcher($this->app, $this->app->make('cache.store'), [
        TestDispatchHandlerOne::class,
        TestDispatchHandlerTwo::class,
    ]);

    $dispatcher->dispatch([
        'message' => [
            'chat' => ['id' => 42],
            'text' => 'hello',
        ],
    ]);

    expect(TestDispatchHandlerOne::$handled)->toBe(1);
    expect(TestDispatchHandlerTwo::$handled)->toBe(1);
});

it('blocks repeated identical commands in configured window', function (): void {
    TestCommandLimitHandler::$handled = 0;

    config()->set('telegram.rate_limit.commands.max_identical', 2);
    config()->set('telegram.rate_limit.commands.window_seconds', 60);
    config()->set('telegram.rate_limit.commands.cache_key_prefix', 'telegram:test:dispatch:commands');

    $dispatcher = new UpdateDispatcher($this->app, $this->app->make('cache.store'), [
        TestCommandLimitHandler::class,
    ]);

    $update = [
        'message' => [
            'chat' => ['id' => 77],
            'text' => '/start',
        ],
    ];

    $dispatcher->dispatch($update);
    $dispatcher->dispatch($update);
    $dispatcher->dispatch($update);

    expect(TestCommandLimitHandler::$handled)->toBe(2);
});

class TestDispatchHandlerOne implements UpdateHandlerInterface
{
    public static int $handled = 0;

    public function supports(array $update): bool
    {
        return isset($update['message']);
    }

    public function handle(array $update): void
    {
        self::$handled++;
    }
}

class TestDispatchHandlerTwo implements UpdateHandlerInterface
{
    public static int $handled = 0;

    public function supports(array $update): bool
    {
        return isset($update['message']);
    }

    public function handle(array $update): void
    {
        self::$handled++;
    }
}

class TestCommandLimitHandler implements UpdateHandlerInterface
{
    public static int $handled = 0;

    public function supports(array $update): bool
    {
        return isset($update['message']);
    }

    public function handle(array $update): void
    {
        self::$handled++;
    }
}
