<?php

namespace Lermal\LaravelTelegram\Events;

class PollingStarted
{
    public function __construct(
        public readonly string $instanceId,
    ) {}
}
