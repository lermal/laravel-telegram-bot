<?php

namespace Lermal\LaravelTelegram\Events;

class PollingStopped
{
    public function __construct(
        public readonly string $instanceId,
        public readonly bool $stoppedBySignal,
    ) {}
}
