<?php

namespace Lermal\LaravelTelegram\Tests;

use Lermal\LaravelTelegram\LaravelTelegramServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelTelegramServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('telegram.bot_token', 'test-token');
        $app['config']->set('telegram.base_url', 'https://api.telegram.org');
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
