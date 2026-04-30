<?php

namespace Lermal\LaravelTelegram\DTO;

class User
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(private readonly array $attributes) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
