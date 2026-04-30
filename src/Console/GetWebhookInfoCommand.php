<?php

namespace Lermal\LaravelTelegram\Console;

use Illuminate\Console\Command;
use Lermal\LaravelTelegram\Contracts\TelegramClientInterface;

class GetWebhookInfoCommand extends Command
{
    protected $signature = 'telegram:webhook:info';

    protected $description = 'Show Telegram webhook information.';

    public function __construct(private readonly TelegramClientInterface $client)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $result = $this->client->getWebhookInfo();

        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}');

        return self::SUCCESS;
    }
}
