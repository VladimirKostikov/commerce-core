<?php

namespace Tests\Support;

final class PaymentWebhookPayload
{
    public static function paid(string $orderId, string $eventId, int $amount = 500): array
    {
        return [
            'event_id' => $eventId,
            'order_id' => $orderId,
            'status' => 'paid',
            'amount' => $amount,
            'currency' => 'RUB',
            'created_at' => '2025-01-01T12:00:00Z',
        ];
    }

    public static function failed(string $orderId, string $eventId, int $amount = 500): array
    {
        $payload = self::paid($orderId, $eventId, $amount);
        $payload['status'] = 'failed';

        return $payload;
    }
}
