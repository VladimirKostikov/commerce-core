<?php

namespace Tests\Unit\Services\Suppliers;

use App\Enums\SupplierName;
use App\Models\Order;
use App\Services\Suppliers\DirectSupplierClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\TestCase;

final class DirectSupplierClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_hang_then_retry_returns_the_same_code(): void
    {
        config(['suppliers.a.mode' => 'hang']);
        $product = CommerceCatalog::productWithAvailableKey();
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);
        $client = $this->app->make(DirectSupplierClient::class);

        $first = $client->issue(SupplierName::A, 'req_hang', $product->sku, $order->id);
        $second = $client->issue(SupplierName::A, 'req_hang', $product->sku, $order->id);

        $this->assertTrue($first->isTimeout());
        $this->assertTrue($second->isOk());
        $this->assertSame('LFXC-TNCS-BPCD', $second->code);
        $this->assertSame(1, $order->productKeys()->count());
    }

    public function test_fail_mode_does_not_claim_a_key(): void
    {
        config(['suppliers.a.mode' => 'fail']);
        $product = CommerceCatalog::productWithAvailableKey();
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);

        $result = $this->app->make(DirectSupplierClient::class)
            ->issue(SupplierName::A, 'req_fail', $product->sku, $order->id);

        $this->assertFalse($result->isOk());
        $this->assertSame(0, $order->productKeys()->count());
    }
}
