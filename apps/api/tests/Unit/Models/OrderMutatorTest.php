<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

final class OrderMutatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_id_is_generated_with_prefix(): void
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->id);
        $this->assertStringStartsWith('ord_', $order->id);
    }

    public function test_sku_is_copied_from_product(): void
    {
        $order = Order::factory()->create();

        $this->assertSame($order->product->sku, $order->sku);
        $this->assertSame($order->product->price, $order->amount);
    }

    public function test_negative_amount_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $order = new Order();
        $order->amount = -1;
    }
}
