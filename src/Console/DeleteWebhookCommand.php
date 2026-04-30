<?php

namespace Lermal\LaravelTelegram\Console;

use Illuminate\Console\Command;
use Lermal\LaravelTelegram\Contracts\TelegramClientInterface;

class DeleteWebhookCommand extends Command
{
    protected $signature = 'telegram:webhook:delete {--drop-pending-updates : Drop all pending Telegram updates}';

    protected $description = 'Delete Telegram webhook.';

    public function __construct(private readonly TelegramClientInterface $client)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->client->deleteWebhook((bool) $this->option('drop-pending-updates'));

        $this->info('Webhook has been deleted.');

        return self::SUCCESS;
    }
}
