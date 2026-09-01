<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\LedgerDirection;
use App\Enums\LedgerReason;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class LedgerEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'direction' => LedgerDirection::Debit,
            'amount' => 500,
            'currency' => Currency::Rub,
            'reason' => LedgerReason::PaymentReceived,
        ];
    }

    public function credit(): static
    {
        return $this->state(fn (array $attributes): array => [
            'direction' => LedgerDirection::Credit,
            'reason' => LedgerReason::KeysIssued,
        ]);
    }
}
