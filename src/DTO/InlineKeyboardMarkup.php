<?php

namespace Lermal\LaravelTelegram\DTO;

class InlineKeyboardMarkup
{
    /**
     * @param  array<int, array<int, array<string, mixed>>>  $inlineKeyboard
     */
    public function __construct(private readonly array $inlineKeyboard) {}

    /**
     * @return array{inline_keyboard: array<int, array<int, array<string, mixed>>>}
     */
    public function toArray(): array
    {
        return [
            'inline_keyboard' => $this->inlineKeyboard,
        ];
    }
}
