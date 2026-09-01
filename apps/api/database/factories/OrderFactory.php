<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'sku' => 'SKU-0000',
            'amount' => 500,
            'currency' => Currency::Rub,
            'status' => OrderStatus::Created,
            'delivery_code' => null,
            'delivery_request_id' => null,
            'lock_version' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Order $order): void {
            $product = $order->product;

            if ($product === null) {
                return;
            }

            if ($order->sku === 'SKU-0000') {
                $order->sku = $product->sku;
                $order->amount = $product->price;
                $order->currency = $product->currency;
            }
        });
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => OrderStatus::Paid,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => OrderStatus::Delivered,
        ]);
    }
}
