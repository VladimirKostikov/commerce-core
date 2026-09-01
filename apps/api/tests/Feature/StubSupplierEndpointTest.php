<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\TestCase;

final class StubSupplierEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_request_id_returns_the_same_code(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);
        $payload = [
            'request_id' => 'req_stub_1',
            'sku' => $product->sku,
            'order_id' => $order->id,
        ];

        $first = $this->postJson('/api/stub/suppliers/a/issue', $payload)
            ->assertOk()
            ->json('code');
        $second = $this->postJson('/api/stub/suppliers/a/issue', $payload)
            ->assertOk()
            ->json('code');

        $this->assertSame($first, $second);
        $this->assertSame('LFXC-TNCS-BPCD', $first);
    }

    public function test_fail_mode_returns_unavailable(): void
    {
        config(['suppliers.a.mode' => 'fail']);
        $product = CommerceCatalog::productWithAvailableKey();
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
        ]);

        $this->postJson('/api/stub/suppliers/a/issue', [
            'request_id' => 'req_stub_fail',
            'sku' => $product->sku,
            'order_id' => $order->id,
        ])->assertStatus(503)
            ->assertJsonPath('status', 'error');
    }
}
