<?php

namespace Lermal\LaravelTelegram\Console;

use Illuminate\Console\Command;
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
        $activeProcessKey = (string) config('telegram.polling.active_process_cache_key', 'telegram.polling.active_process');
        $offsetKey = (string) config('telegram.polling.offset_cache_key', 'telegram.polling.offset');
        $limit = (int) config('telegram.polling.limit', 100);
        $timeout = (int) config('telegram.polling.timeout', 30);
        $sleepMs = (int) config('telegram.polling.sleep_ms', 1000);
        $instanceId = (string) str()->uuid();
        $stopRequested = false;

        $this->cache->forever($activeProcessKey, $instanceId);

        $signals = array_values(array_filter([
            defined('SIGINT') ? SIGINT : null,
            defined('SIGTERM') ? SIGTERM : null,
        ]));

        if ($signals !== []) {
            $this->trap($signals, function () use (&$stopRequested): void {
                $stopRequested = true;
            });
        }

        do {
            if (! $this->isCurrentProcess($activeProcessKey, $instanceId)) {
                $this->warn('Polling was replaced by a newer process. Stopping current process.');

                break;
            }

            $offset = (int) $this->cache->get($offsetKey, 0);
            $updates = $this->client->getUpdates($offset, $limit, $timeout);
            $maxUpdateId = null;

            foreach ($updates as $update) {
                $this->outputCommandInvocation($update);
                $this->dispatcher->dispatch($update);

                $updateId = $update['update_id'] ?? null;

                if (is_int($updateId)) {
                    $maxUpdateId = max($maxUpdateId ?? $updateId, $updateId);
                }
            }

            if ($maxUpdateId !== null) {
                $this->cache->forever($offsetKey, $maxUpdateId + 1);
            }

            if (! $this->option('once') && ! $stopRequested) {
                usleep($sleepMs * 1000);
            }
        } while (! $this->option('once') && ! $stopRequested);

        if ($this->isCurrentProcess($activeProcessKey, $instanceId)) {
            $this->cache->forget($activeProcessKey);
        }

        if ($stopRequested) {
            $this->info('Polling stopped by signal.');
        }

        return self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $update
     */
    private function outputCommandInvocation(array $update): void
    {
        $text = $update['message']['text'] ?? null;

        if (! is_string($text)) {
            return;
        }

        $trimmed = trim($text);

        if ($trimmed === '' || ! str_starts_with($trimmed, '/')) {
            return;
        }

        $parts = preg_split('/\s+/', $trimmed) ?: [];
        $command = array_shift($parts);

        if (! is_string($command) || $command === '') {
            return;
        }

        $params = implode(' ', $parts);
        $formattedParams = $params === '' ? 'none' : $params;

        $this->line(sprintf('Command called: %s | params: %s', $command, $formattedParams));
    }

    private function isCurrentProcess(string $activeProcessKey, string $instanceId): bool
    {
        return $this->cache->get($activeProcessKey) === $instanceId;
    }
}
