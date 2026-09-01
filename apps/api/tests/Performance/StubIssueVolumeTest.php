<?php

namespace Tests\Performance;

use App\Models\Order;
use App\Models\StubSupplierIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\TestCase;

final class StubIssueVolumeTest extends TestCase
{
    use RefreshDatabase;

    public function test_volume_of_duplicate_request_ids_stays_idempotent(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);
        $payload = [
            'request_id' => 'req_vol_stub',
            'sku' => $product->sku,
            'order_id' => $order->id,
        ];

        for ($i = 0; $i < 40; $i++) {
            $this->postJson('/api/stub/suppliers/a/issue', $payload)->assertOk();
        }

        $this->assertSame(1, StubSupplierIssue::query()->count());
        $this->assertSame(1, $order->productKeys()->count());
    }
}
