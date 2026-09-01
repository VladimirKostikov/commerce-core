<?php

namespace Tests\Database;

use App\Models\Delivery;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\StubSupplierIssue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_sku_is_unique(): void
    {
        Product::factory()->create(['sku' => 'KEY-GTA5']);

        $this->expectException(QueryException::class);

        Product::factory()->create(['sku' => 'KEY-GTA5']);
    }

    public function test_product_key_code_is_unique(): void
    {
        ProductKey::factory()->create(['code' => 'LFXC-TNCS-BPCD']);

        $this->expectException(QueryException::class);

        ProductKey::factory()->create(['code' => 'LFXC-TNCS-BPCD']);
    }

    public function test_one_key_cannot_belong_to_two_orders(): void
    {
        $first = Order::factory()->create();
        $second = Order::factory()->create();
        ProductKey::factory()->create([
            'order_id' => $first->id,
        ]);

        $this->expectException(QueryException::class);

        ProductKey::factory()->create([
            'order_id' => $first->id,
            'product_id' => $second->product_id,
        ]);
    }

    public function test_payment_event_id_is_unique(): void
    {
        PaymentEvent::factory()->create(['event_id' => 'evt_a1b2c3']);

        $this->expectException(QueryException::class);

        PaymentEvent::factory()->create(['event_id' => 'evt_a1b2c3']);
    }

    public function test_delivery_request_id_is_unique(): void
    {
        Delivery::factory()->create(['request_id' => 'req_00123-1']);

        $this->expectException(QueryException::class);

        Delivery::factory()->create(['request_id' => 'req_00123-1']);
    }

    public function test_one_delivery_per_order(): void
    {
        $order = Order::factory()->create();
        Delivery::factory()->create(['order_id' => $order->id]);

        $this->expectException(QueryException::class);

        Delivery::factory()->create(['order_id' => $order->id]);
    }

    public function test_stub_request_id_is_unique_per_supplier(): void
    {
        StubSupplierIssue::query()->create([
            'supplier' => 'a',
            'request_id' => 'req_stub_unique',
            'sku' => 'KEY-GTA5',
            'order_id' => 'ord_stub',
            'code' => 'AAAA-BBBB-CCCC',
        ]);

        $this->expectException(QueryException::class);

        StubSupplierIssue::query()->create([
            'supplier' => 'a',
            'request_id' => 'req_stub_unique',
            'sku' => 'KEY-GTA5',
            'order_id' => 'ord_stub',
            'code' => 'DDDD-EEEE-FFFF',
        ]);
    }

    public function test_ledger_reason_is_unique_per_order(): void
    {
        $order = Order::factory()->create();
        LedgerEntry::factory()->create([
            'order_id' => $order->id,
            'reason' => 'payment_received',
        ]);

        $this->expectException(QueryException::class);

        LedgerEntry::factory()->create([
            'order_id' => $order->id,
            'reason' => 'payment_received',
        ]);
    }
}
