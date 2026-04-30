<?php

namespace Lermal\LaravelTelegram\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    protected $signature = 'telegram:install';

    protected $description = 'Publish package config and generate webhook secret.';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'laravel-telegram-config',
            '--force' => true,
        ]);

        $secret = Str::random(48);

        $this->info('Config published.');
        $this->line('Add this value to your .env file:');
        $this->line(sprintf('TELEGRAM_WEBHOOK_SECRET=%s', $secret));

        return self::SUCCESS;
    }
}
