<?php

namespace Lermal\LaravelTelegram\Console;

use Illuminate\Console\Command;
use Lermal\LaravelTelegram\Contracts\TelegramClientInterface;

class SetWebhookCommand extends Command
{
    protected $signature = 'telegram:webhook:set {url? : Webhook endpoint URL}';

    protected $description = 'Set Telegram webhook URL.';

    public function __construct(private readonly TelegramClientInterface $client)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $url = (string) ($this->argument('url') ?? config('app.url').'/'.ltrim((string) config('telegram.webhook.path', 'telegram/webhook'), '/'));
        $secret = (string) config('telegram.webhook.secret', '');

        $this->client->setWebhook($url, $secret !== '' ? $secret : null);

        $this->info(sprintf('Webhook has been set: %s', $url));

        return self::SUCCESS;
    }
}
