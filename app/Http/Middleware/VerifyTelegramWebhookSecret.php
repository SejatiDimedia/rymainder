<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTelegramWebhookSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedSecret = config('rymainder.channels.telegram.webhook_secret');

        // Only enforce if secret is explicitly configured
        if (! empty($expectedSecret)) {
            $headerSecret = $request->header('X-Telegram-Bot-Api-Secret-Token');

            if (! hash_equals((string) $expectedSecret, (string) $headerSecret)) {
                return response()->json(['error' => 'Unauthorized webhook signature'], 403);
            }
        }

        return $next($request);
    }
}
