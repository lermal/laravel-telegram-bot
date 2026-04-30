<?php

use Illuminate\Support\Facades\Route;
use Lermal\LaravelTelegram\Http\WebhookController;

$path = ltrim((string) config('telegram.webhook.path', 'telegram/webhook'), '/');

Route::post($path, WebhookController::class)
    ->name('telegram.webhook');
