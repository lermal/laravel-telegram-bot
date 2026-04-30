<?php

namespace Lermal\LaravelTelegram;

use Illuminate\Contracts\Container\Container;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;
use Lermal\LaravelTelegram\Console\DeleteWebhookCommand;
use Lermal\LaravelTelegram\Console\GetWebhookInfoCommand;
use Lermal\LaravelTelegram\Console\InstallCommand;
use Lermal\LaravelTelegram\Console\PollUpdatesCommand;
use Lermal\LaravelTelegram\Console\SetWebhookCommand;
use Lermal\LaravelTelegram\Contracts\TelegramClientInterface;
use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;
use Lermal\LaravelTelegram\Dispatching\UpdateDispatcher;
use Lermal\LaravelTelegram\Support\RawApiCaller;

class LaravelTelegramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/telegram.php', 'telegram');

        $this->app->singleton(TelegramClientInterface::class, function (Container $app): TelegramClient {
            $config = (array) config('telegram');
            $httpConfig = (array) ($config['http'] ?? []);
            $rateLimitConfig = (array) ($config['rate_limit']['api'] ?? []);

            return new TelegramClient(
                http: $app->make(HttpFactory::class),
                cache: $app->make(CacheRepository::class),
                rateLimiter: $app->make(RateLimiter::class),
                botToken: (string) ($config['bot_token'] ?? ''),
                baseUrl: (string) ($config['base_url'] ?? 'https://api.telegram.org'),
                timeout: (int) ($httpConfig['timeout'] ?? 20),
                connectTimeout: (int) ($httpConfig['connect_timeout'] ?? 10),
                retryTimes: (int) ($httpConfig['retry_times'] ?? 3),
                retrySleepMs: (int) ($httpConfig['retry_sleep_ms'] ?? 200),
                maxRequestsPerSecond: (int) ($rateLimitConfig['rps'] ?? 30),
                rateLimitKey: (string) ($rateLimitConfig['key'] ?? 'telegram:api:rps'),
                queueLockKey: (string) ($rateLimitConfig['queue_lock_key'] ?? 'telegram:api:queue:lock'),
                queueLockSeconds: (int) ($rateLimitConfig['queue_lock_seconds'] ?? 5),
                waitSleepMs: (int) ($rateLimitConfig['wait_sleep_ms'] ?? 50),
            );
        });

        $this->app->alias(TelegramClientInterface::class, 'telegram.client');

        $this->app->singleton(RawApiCaller::class, fn (Container $app): RawApiCaller => new RawApiCaller(
            $app->make(TelegramClientInterface::class)
        ));

        $this->app->singleton(UpdateDispatcher::class, function (Container $app): UpdateDispatcher {
            /** @var array<int, class-string<UpdateHandlerInterface>> $handlerClasses */
            $handlerClasses = (array) config('telegram.handlers', []);

            return new UpdateDispatcher(
                container: $app,
                cache: $app->make(CacheRepository::class),
                handlerClasses: $handlerClasses
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/telegram.php' => config_path('telegram.php'),
        ], 'laravel-telegram-config');

        if ((bool) config('telegram.webhook.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/telegram.php');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                PollUpdatesCommand::class,
                SetWebhookCommand::class,
                DeleteWebhookCommand::class,
                GetWebhookInfoCommand::class,
            ]);
        }
    }
}
