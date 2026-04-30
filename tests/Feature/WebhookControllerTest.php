<?php

use Lermal\LaravelTelegram\Dispatching\UpdateDispatcher;

it('accepts webhook and dispatches update when secret is valid', function (): void {
    config()->set('telegram.webhook.enabled', true);
    config()->set('telegram.webhook.path', 'telegram/webhook');
    config()->set('telegram.webhook.secret', 'secret-token');

    $dispatcher = Mockery::mock(UpdateDispatcher::class);
    $dispatcher
        ->shouldReceive('dispatch')
        ->once()
        ->with(['update_id' => 1, 'message' => ['text' => 'hi']]);
    $this->app->instance(UpdateDispatcher::class, $dispatcher);

    $response = $this->postJson('/telegram/webhook', [
        'update_id' => 1,
        'message' => ['text' => 'hi'],
    ], [
        'X-Telegram-Bot-Api-Secret-Token' => 'secret-token',
    ]);

    $response->assertOk()->assertExactJson(['ok' => true]);
});

it('rejects webhook request with invalid secret', function (): void {
    config()->set('telegram.webhook.enabled', true);
    config()->set('telegram.webhook.path', 'telegram/webhook');
    config()->set('telegram.webhook.secret', 'expected-secret');

    $dispatcher = Mockery::mock(UpdateDispatcher::class);
    $dispatcher->shouldNotReceive('dispatch');
    $this->app->instance(UpdateDispatcher::class, $dispatcher);

    $response = $this->postJson('/telegram/webhook', [
        'update_id' => 2,
        'message' => ['text' => 'blocked'],
    ], [
        'X-Telegram-Bot-Api-Secret-Token' => 'wrong-secret',
    ]);

    $response
        ->assertForbidden()
        ->assertExactJson([
            'ok' => false,
            'message' => 'Invalid webhook secret.',
        ]);
});

it('rejects webhook payload without update id', function (): void {
    config()->set('telegram.webhook.enabled', true);
    config()->set('telegram.webhook.path', 'telegram/webhook');
    config()->set('telegram.webhook.secret', 'secret-token');

    $dispatcher = Mockery::mock(UpdateDispatcher::class);
    $dispatcher->shouldNotReceive('dispatch');
    $this->app->instance(UpdateDispatcher::class, $dispatcher);

    $response = $this->postJson('/telegram/webhook', [
        'message' => ['text' => 'hi'],
    ], [
        'X-Telegram-Bot-Api-Secret-Token' => 'secret-token',
    ]);

    $response
        ->assertStatus(422)
        ->assertExactJson([
            'ok' => false,
            'message' => 'Invalid webhook payload.',
        ]);
});

it('rejects webhook payload when update id is not integer', function (): void {
    config()->set('telegram.webhook.enabled', true);
    config()->set('telegram.webhook.path', 'telegram/webhook');
    config()->set('telegram.webhook.secret', 'secret-token');

    $dispatcher = Mockery::mock(UpdateDispatcher::class);
    $dispatcher->shouldNotReceive('dispatch');
    $this->app->instance(UpdateDispatcher::class, $dispatcher);

    $response = $this->postJson('/telegram/webhook', [
        'update_id' => '1',
        'message' => ['text' => 'hi'],
    ], [
        'X-Telegram-Bot-Api-Secret-Token' => 'secret-token',
    ]);

    $response
        ->assertStatus(422)
        ->assertExactJson([
            'ok' => false,
            'message' => 'Invalid webhook payload.',
        ]);
});
