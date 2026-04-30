<?php

namespace Lermal\LaravelTelegram\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Lermal\LaravelTelegram\Contracts\TelegramClientInterface;
use Lermal\LaravelTelegram\Dispatching\UpdateDispatcher;

class PollUpdatesCommand extends Command
{
    protected $signature = 'telegram:poll {--once : Run single polling iteration only}';

    protected $description = 'Poll updates from Telegram Bot API and dispatch to handlers.';

    public function __construct(
        private readonly TelegramClientInterface $client,
        private readonly UpdateDispatcher $dispatcher,
        private readonly CacheRepository $cache,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $lockKey = 'telegram:poll:lock';
        $lockTtl = (int) config('telegram.polling.lock_seconds', 40);
        $offsetKey = (string) config('telegram.polling.offset_cache_key', 'telegram.polling.offset');
        $limit = (int) config('telegram.polling.limit', 100);
        $timeout = (int) config('telegram.polling.timeout', 30);
        $sleepMs = (int) config('telegram.polling.sleep_ms', 1000);

        try {
            $this->cache->lock($lockKey, $lockTtl)->block(1, function () use ($offsetKey, $limit, $timeout, $sleepMs): void {
                do {
                    $offset = (int) $this->cache->get($offsetKey, 0);
                    $updates = $this->client->getUpdates($offset, $limit, $timeout);
                    $maxUpdateId = null;

                    foreach ($updates as $update) {
                        $this->dispatcher->dispatch($update);

                        $updateId = $update['update_id'] ?? null;

                        if (is_int($updateId)) {
                            $maxUpdateId = max($maxUpdateId ?? $updateId, $updateId);
                        }
                    }

                    if ($maxUpdateId !== null) {
                        $this->cache->forever($offsetKey, $maxUpdateId + 1);
                    }

                    if (! $this->option('once')) {
                        usleep($sleepMs * 1000);
                    }
                } while (! $this->option('once'));
            });
        } catch (LockTimeoutException) {
            $this->warn('Polling is already running in another process.');
        }

        return self::SUCCESS;
    }
}
