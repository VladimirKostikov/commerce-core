<?php

namespace Database\Factories;

use App\Enums\ProductKeyStatus;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductKeyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'code' => strtoupper(fake()->unique()->bothify('????-????-????')),
            'status' => ProductKeyStatus::Available,
            'order_id' => null,
        ];
    }

    public function sold(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductKeyStatus::Sold,
        ]);
    }

    public function reserved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductKeyStatus::Reserved,
        ]);
    }
}
