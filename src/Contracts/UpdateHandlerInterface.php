<?php

namespace Lermal\LaravelTelegram\Contracts;

interface UpdateHandlerInterface
{
    /**
     * @param  array<string, mixed>  $update
     */
    public function supports(array $update): bool;

    /**
     * @param  array<string, mixed>  $update
     */
    public function handle(array $update): void;
}
