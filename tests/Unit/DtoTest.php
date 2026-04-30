<?php

use Lermal\LaravelTelegram\DTO\Chat;
use Lermal\LaravelTelegram\DTO\InlineKeyboardMarkup;
use Lermal\LaravelTelegram\DTO\Message;
use Lermal\LaravelTelegram\DTO\User;

it('converts message dto to array', function (): void {
    $attributes = ['message_id' => 10, 'text' => 'hello'];
    $dto = new Message($attributes);

    expect($dto->toArray())->toBe($attributes);
});

it('converts chat dto to array', function (): void {
    $attributes = ['id' => 1, 'type' => 'private'];
    $dto = new Chat($attributes);

    expect($dto->toArray())->toBe($attributes);
});

it('converts user dto to array', function (): void {
    $attributes = ['id' => 2, 'first_name' => 'John'];
    $dto = new User($attributes);

    expect($dto->toArray())->toBe($attributes);
});

it('converts inline keyboard markup to telegram payload', function (): void {
    $keyboard = [
        [
            ['text' => 'One', 'callback_data' => 'one'],
            ['text' => 'Two', 'callback_data' => 'two'],
        ],
    ];
    $dto = new InlineKeyboardMarkup($keyboard);

    expect($dto->toArray())->toBe([
        'inline_keyboard' => $keyboard,
    ]);
});
