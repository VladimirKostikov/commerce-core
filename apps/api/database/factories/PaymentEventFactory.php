<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentEventFactory extends Factory
{
    public function definition(): array
    {
        $eventId = 'evt_'.strtolower((string) Str::ulid());

        return [
            'event_id' => $eventId,
            'order_id' => Order::factory(),
            'status' => PaymentStatus::Paid,
            'amount' => 500,
            'currency' => Currency::Rub,
            'occurred_at' => now(),
            'payload' => [
                'event_id' => $eventId,
                'status' => PaymentStatus::Paid->value,
            ],
            'processed_at' => null,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PaymentStatus::Failed,
        ]);
    }
}
