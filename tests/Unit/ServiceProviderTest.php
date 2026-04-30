<?php

use Lermal\LaravelTelegram\Contracts\TelegramClientInterface;
use Lermal\LaravelTelegram\Dispatching\UpdateDispatcher;
use Lermal\LaravelTelegram\Support\RawApiCaller;

it('registers telegram client and support services', function (): void {
    expect($this->app->bound(TelegramClientInterface::class))->toBeTrue();
    expect($this->app->bound('telegram.client'))->toBeTrue();
    expect($this->app->bound(UpdateDispatcher::class))->toBeTrue();
    expect($this->app->bound(RawApiCaller::class))->toBeTrue();
});
