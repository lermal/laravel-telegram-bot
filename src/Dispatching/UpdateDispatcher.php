<?php

namespace Lermal\LaravelTelegram\Dispatching;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Container\Container;
use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;

class UpdateDispatcher
{
    /**
     * @param  array<int, class-string<UpdateHandlerInterface>>  $handlerClasses
     */
    public function __construct(
        private readonly Container $container,
        private readonly CacheRepository $cache,
        private readonly array $handlerClasses = [],
    ) {
        $commandsRateLimitConfig = (array) config('telegram.rate_limit.commands', []);
        $this->maxIdenticalCommands = (int) ($commandsRateLimitConfig['max_identical'] ?? 3);
        $this->windowSeconds = (int) ($commandsRateLimitConfig['window_seconds'] ?? 10);
        $this->cacheKeyPrefix = (string) ($commandsRateLimitConfig['cache_key_prefix'] ?? 'telegram:rate-limit:commands');
    }

    private readonly int $maxIdenticalCommands;

    private readonly int $windowSeconds;

    private readonly string $cacheKeyPrefix;

    /**
     * @param  array<string, mixed>  $update
     */
    public function dispatch(array $update): void
    {
        if ($this->isBlockedByAntiSpam($update)) {
            return;
        }

        foreach ($this->handlerClasses as $handlerClass) {
            /** @var UpdateHandlerInterface $handler */
            $handler = $this->container->make($handlerClass);

            if ($handler->supports($update)) {
                $handler->handle($update);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $update
     */
    private function isBlockedByAntiSpam(array $update): bool
    {
        if ($this->maxIdenticalCommands <= 0 || $this->windowSeconds <= 0) {
            return false;
        }

        $command = $this->extractCommand($update);
        $chatId = $update['message']['chat']['id'] ?? null;

        if ($command === null || ! is_int($chatId)) {
            return false;
        }

        $key = sprintf('%s:%d:%s', $this->cacheKeyPrefix, $chatId, $command);
        $attempts = $this->cache->add($key, 0, $this->windowSeconds) ? 0 : (int) $this->cache->get($key, 0);
        $attempts++;
        $this->cache->put($key, $attempts, $this->windowSeconds);

        return $attempts > $this->maxIdenticalCommands;
    }

    /**
     * @param  array<string, mixed>  $update
     */
    private function extractCommand(array $update): ?string
    {
        $text = $update['message']['text'] ?? null;

        if (! is_string($text)) {
            return null;
        }

        $trimmed = trim($text);

        if ($trimmed === '' || ! str_starts_with($trimmed, '/')) {
            return null;
        }

        $firstToken = explode(' ', $trimmed, 2)[0] ?? null;

        if (! is_string($firstToken) || $firstToken === '') {
            return null;
        }

        return strtolower($firstToken);
    }
}
