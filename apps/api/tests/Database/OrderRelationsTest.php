<?php

namespace Tests\Database;

use App\Enums\LedgerDirection;
use App\Models\Delivery;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Models\ProductKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_graph_is_persisted(): void
    {
        $order = Order::factory()->create();
        $key = ProductKey::factory()->create([
            'product_id' => $order->product_id,
            'order_id' => $order->id,
        ]);
        $event = PaymentEvent::factory()->create([
            'order_id' => $order->id,
            'amount' => $order->amount,
        ]);
        $delivery = Delivery::factory()->issued()->create([
            'order_id' => $order->id,
        ]);
        $debit = LedgerEntry::factory()->create([
            'order_id' => $order->id,
            'amount' => $order->amount,
            'direction' => LedgerDirection::Debit,
        ]);
        $credit = LedgerEntry::factory()->credit()->create([
            'order_id' => $order->id,
            'amount' => $order->amount,
        ]);

        $order->refresh();

        $this->assertTrue($order->user->is($order->user));
        $this->assertNotNull($order->user_id);
        $this->assertTrue($order->product->is($order->product));
        $this->assertTrue($order->productKeys->contains($key));
        $this->assertTrue($order->paymentEvents->contains($event));
        $this->assertTrue($order->delivery->is($delivery));
        $this->assertCount(2, $order->ledgerEntries);
        $this->assertSame($order->amount, $debit->amount);
        $this->assertSame($order->amount, $credit->amount);
        $this->assertSame(0, $debit->amount - $credit->amount);
    }
}
