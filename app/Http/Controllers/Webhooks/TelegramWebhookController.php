<?php

namespace App\Http\Controllers\Webhooks;

use App\Domain\Telegram\Actions\ProcessTelegramWebhookAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, ProcessTelegramWebhookAction $action): JsonResponse
    {
        $update = $request->all();
        $result = $action->execute($update);

        return response()->json($result);
    }
}
