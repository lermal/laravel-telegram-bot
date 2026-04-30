<?php

namespace Lermal\LaravelTelegram\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    protected $signature = 'telegram:install';

    protected $description = 'Publish package config and configure Telegram environment variables.';

    public function handle(): int
    {
        $botToken = (string) $this->ask('Enter Telegram bot token');
        if ($botToken === '') {
            $this->error('Telegram bot token is required.');

            return self::FAILURE;
        }

        $botName = (string) $this->ask('Enter Telegram bot username (without @)', (string) env('TELEGRAM_BOT_NAME', ''));
        $baseUrl = (string) $this->ask('Enter Telegram API base URL', (string) config('telegram.base_url', 'https://api.telegram.org'));
        if ($baseUrl === '') {
            $baseUrl = 'https://api.telegram.org';
        }

        $this->call('vendor:publish', [
            '--tag' => 'laravel-telegram-config',
            '--force' => true,
        ]);

        $secret = Str::random(48);
        $this->upsertEnvironmentValues([
            'TELEGRAM_BOT_TOKEN' => $botToken,
            'TELEGRAM_BOT_NAME' => $botName,
            'TELEGRAM_BASE_URL' => $baseUrl,
            'TELEGRAM_WEBHOOK_SECRET' => $secret,
        ]);

        $this->info('Config published and .env updated.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, string>  $values
     */
    private function upsertEnvironmentValues(array $values): void
    {
        /** @var Application $application */
        $application = $this->laravel;
        $environmentFilePath = $application->environmentFilePath();
        $environmentContents = is_file($environmentFilePath)
            ? (string) file_get_contents($environmentFilePath)
            : '';

        foreach ($values as $key => $value) {
            $environmentContents = $this->upsertEnvironmentValue($environmentContents, $key, $value);
        }

        file_put_contents($environmentFilePath, $environmentContents);
    }

    private function upsertEnvironmentValue(string $environmentContents, string $key, string $value): string
    {
        $line = sprintf('%s=%s', $key, $value);
        $pattern = sprintf('/^%s=.*$/m', preg_quote($key, '/'));

        if (preg_match($pattern, $environmentContents) === 1) {
            return (string) preg_replace($pattern, $line, $environmentContents, 1);
        }

        $trimmed = rtrim($environmentContents);
        if ($trimmed !== '') {
            return $trimmed.PHP_EOL.$line.PHP_EOL;
        }

        return $line.PHP_EOL;
    }
}
