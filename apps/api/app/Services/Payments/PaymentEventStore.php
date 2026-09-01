<?php

namespace App\Services\Payments;

use App\Enums\Currency;
use App\Enums\PaymentStatus;
use App\Models\PaymentEvent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;

final class PaymentEventStore
{
    private const EXISTING_ATTEMPTS = 25;

    private const EXISTING_RETRY_MICROSECONDS = 10_000;

    public function remember(array $payload): PaymentEvent
    {
        $eventId = trim((string) $payload['event_id']);

        try {
            return $this->insert($eventId, $payload);
        } catch (UniqueConstraintViolationException) {
            return $this->existing($eventId);
        }
    }

    private function insert(string $eventId, array $payload): PaymentEvent
    {
        return PaymentEvent::query()->create([
            'event_id' => $eventId,
            'order_id' => trim((string) $payload['order_id']),
            'status' => PaymentStatus::from((string) $payload['status']),
            'amount' => (int) $payload['amount'],
            'currency' => Currency::from((string) $payload['currency']),
            'occurred_at' => isset($payload['created_at'])
                ? Carbon::parse((string) $payload['created_at'])
                : now(),
            'payload' => $payload,
            'processed_at' => null,
        ]);
    }

    private function existing(string $eventId): PaymentEvent
    {
        for ($attempt = 0; $attempt < self::EXISTING_ATTEMPTS; $attempt++) {
            $event = PaymentEvent::query()->where('event_id', $eventId)->first();

            if ($event !== null) {
                return $event;
            }

            usleep(self::EXISTING_RETRY_MICROSECONDS);
        }

        throw (new ModelNotFoundException())->setModel(PaymentEvent::class, [$eventId]);
    }
}
