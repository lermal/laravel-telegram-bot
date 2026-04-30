<?php

namespace Lermal\LaravelTelegram\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lermal\LaravelTelegram\Dispatching\UpdateDispatcher;
use Symfony\Component\HttpFoundation\Response;

class WebhookController
{
    public function __construct(private readonly UpdateDispatcher $dispatcher) {}

    public function __invoke(Request $request): JsonResponse
    {
        $expectedSecret = (string) config('telegram.webhook.secret', '');

        if ($expectedSecret !== '') {
            $providedSecret = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');

            if (! hash_equals($expectedSecret, $providedSecret)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Invalid webhook secret.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        /** @var array<string, mixed> $update */
        $update = $request->json()->all();
        $this->dispatcher->dispatch($update);

        return response()->json(['ok' => true]);
    }
}
