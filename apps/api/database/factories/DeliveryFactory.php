<?php

namespace Database\Factories;

use App\Enums\DeliveryStatus;
use App\Enums\SupplierName;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DeliveryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'request_id' => 'req_'.strtolower((string) Str::ulid()),
            'supplier' => SupplierName::A,
            'code' => null,
            'status' => DeliveryStatus::Pending,
        ];
    }

    public function issued(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => DeliveryStatus::Issued,
            'code' => strtoupper(fake()->bothify('????-????-????')),
        ]);
    }
}
