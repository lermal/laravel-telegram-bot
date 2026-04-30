<?php

namespace Lermal\LaravelTelegram\Events;

class UpdateProcessingFailed
{
    /**
     * @param array<string, mixed> $update
     */
    public function __construct(
        public readonly string $instanceId,
        public readonly ?int $updateId,
        public readonly array $update,
        public readonly string $message,
    ) {
    }
}
