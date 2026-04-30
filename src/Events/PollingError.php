<?php

namespace Lermal\LaravelTelegram\Events;

class PollingError
{
    public function __construct(
        public readonly string $instanceId,
        public readonly string $message,
    ) {}
}
