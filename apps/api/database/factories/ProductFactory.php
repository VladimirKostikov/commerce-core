<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku' => 'SKU-'.fake()->unique()->numerify('####'),
            'name' => fake()->words(3, true),
            'type' => ProductType::Key,
            'price' => 500,
            'currency' => Currency::Rub,
            'image' => 'assets/steam.png',
            'is_active' => true,
            'available_keys_count' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function topup(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => ProductType::Topup,
        ]);
    }
}
