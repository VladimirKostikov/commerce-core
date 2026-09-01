<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentWebhookServiceInterface;
use App\Http\Requests\PaymentWebhookRequest;
use Illuminate\Http\JsonResponse;

final class PaymentWebhookController extends Controller
{
    public function __invoke(
        PaymentWebhookRequest $request,
        PaymentWebhookServiceInterface $webhooks,
    ): JsonResponse {
        $webhooks->handle($request->validated());

        return response()->json(['accepted' => true]);
    }
}
