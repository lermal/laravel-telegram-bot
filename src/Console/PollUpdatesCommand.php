<?php

namespace Lermal\LaravelTelegram\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\ConnectionException;
use Lermal\LaravelTelegram\Contracts\TelegramClientInterface;
use Lermal\LaravelTelegram\Dispatching\UpdateDispatcher;
use Throwable;

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
        $pollingTimeout = (int) config('telegram.polling.timeout', 30);
        $httpTimeout = (int) config('telegram.http.timeout', 20);
        $sleepMs = (int) config('telegram.polling.sleep_ms', 1000);
        $effectiveTimeout = $this->resolveEffectivePollingTimeout($pollingTimeout, $httpTimeout);
        $instanceId = (string) str()->uuid();
        $stopRequested = false;

        $this->cache->forever($activeProcessKey, $instanceId);
        $this->logInfo(sprintf('Polling started. Instance: %s', $instanceId));

        if ($effectiveTimeout !== $pollingTimeout) {
            $this->logDebug(sprintf(
                'Polling timeout adjusted from %d to %d seconds to prevent HTTP client timeout.',
                $pollingTimeout,
                $effectiveTimeout
            ));
        }

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
                $this->logWarn('Polling was replaced by a newer process. Stopping current process.');

                break;
            }

            $offset = (int) $this->cache->get($offsetKey, 0);
            $updates = [];

            try {
                $updates = $this->client->getUpdates($offset, $limit, $effectiveTimeout);
            } catch (ConnectionException $exception) {
                $this->logError(sprintf(
                    'Telegram connection error while polling updates: %s',
                    $this->sanitizeLogMessage($exception->getMessage())
                ));
            } catch (Throwable $exception) {
                $this->logError(sprintf(
                    'Unexpected polling error: %s',
                    $this->sanitizeLogMessage($exception->getMessage())
                ));
            }

            $maxUpdateId = null;

            foreach ($updates as $update) {
                $updateId = $update['update_id'] ?? null;

                if (is_int($updateId)) {
                    $maxUpdateId = max($maxUpdateId ?? $updateId, $updateId);
                }

                try {
                    $this->outputUpdateInvocation($update);
                    $this->dispatcher->dispatch($update);
                } catch (Throwable $exception) {
                    $this->logError(sprintf(
                        'Failed to process update%s: %s',
                        is_int($updateId) ? sprintf(' #%d', $updateId) : '',
                        $this->sanitizeLogMessage($exception->getMessage())
                    ));
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
            $this->logInfo('Polling stopped by signal.');
        } else {
            $this->logInfo('Polling stopped.');
        }

        return self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $update
     */
    private function outputUpdateInvocation(array $update): void
    {
        $this->outputCommandInvocation($update);
        $this->outputCallbackInvocation($update);
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

        $this->logInfo(sprintf('Command called: %s | params: %s', $command, $formattedParams));
    }

    /**
     * @param array<string, mixed> $update
     */
    private function outputCallbackInvocation(array $update): void
    {
        $callbackData = $update['callback_query']['data'] ?? null;

        if (! is_string($callbackData) || trim($callbackData) === '') {
            return;
        }

        $this->logInfo(sprintf('Callback called: %s', trim($callbackData)));
    }

    private function isCurrentProcess(string $activeProcessKey, string $instanceId): bool
    {
        return $this->cache->get($activeProcessKey) === $instanceId;
    }

    private function resolveEffectivePollingTimeout(int $pollingTimeout, int $httpTimeout): int
    {
        if ($httpTimeout <= 1) {
            return max(1, $pollingTimeout);
        }

        return min(max(1, $pollingTimeout), $httpTimeout - 1);
    }

    private function sanitizeLogMessage(string $message): string
    {
        $token = (string) config('telegram.bot_token', '');

        if ($token === '') {
            return $message;
        }

        $patterns = [
            '/bot'.preg_quote($token, '/').'/',
            '/'.preg_quote($token, '/').'/',
        ];

        return (string) preg_replace($patterns, ['bot[REDACTED]', '[REDACTED]'], $message);
    }

    private function logInfo(string $message): void
    {
        $this->info(sprintf('[INFO] %s', $message));
    }

    private function logDebug(string $message): void
    {
        $this->line(sprintf('[DEBUG] %s', $message));
    }

    private function logWarn(string $message): void
    {
        $this->warn(sprintf('[WARN] %s', $message));
    }

    private function logError(string $message): void
    {
        $this->error(sprintf('[ERROR] %s', $message));
    }
}
