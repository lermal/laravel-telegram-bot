<?php

namespace Lermal\LaravelTelegram\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lermal\LaravelTelegram\Dispatching\UpdateDispatcher;
use Symfony\Component\HttpFoundation\Response;

class WebhookController
{
    public function __construct(private readonly UpdateDispatcher $dispatcher)
    {
    }

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

        if (!$this->isValidPayload($update)) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid webhook payload.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->dispatcher->dispatch($update);

        return response()->json(['ok' => true]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function isValidPayload(array $payload): bool
    {
        return isset($payload['update_id']) && is_int($payload['update_id']);
    }
}
