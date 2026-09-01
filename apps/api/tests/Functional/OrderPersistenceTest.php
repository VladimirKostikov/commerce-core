<?php

namespace Tests\Functional;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_order_can_be_fetched_by_id(): void
    {
        $product = Product::factory()->create([
            'sku' => 'KEY-CS2-PRIME',
            'price' => 1290,
        ]);
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);

        $found = Order::query()->find($order->id);

        $this->assertNotNull($found);
        $this->assertSame(OrderStatus::Created, $found->status);
        $this->assertSame('KEY-CS2-PRIME', $found->sku);
        $this->assertSame(1290, $found->amount);
    }
}
