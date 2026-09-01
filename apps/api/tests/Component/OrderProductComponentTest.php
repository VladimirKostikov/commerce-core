<?php

namespace Tests\Component;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderProductComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_belongs_to_its_product(): void
    {
        $product = Product::factory()->create(['sku' => 'SUB-YT-3M']);
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);

        $this->assertTrue($order->product->is($product));
        $this->assertTrue($product->orders->contains($order));
    }
}
