<?php

namespace Tests\Unit\Services\Suppliers;

use App\Enums\SupplierName;
use App\Models\Order;
use App\Models\StubSupplierIssue;
use App\Services\Suppliers\StubIssueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\TestCase;

final class StubIssueServiceTest extends TestCase
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
        $issuer = $this->app->make(StubIssueService::class);

        $first = $issuer->issue(SupplierName::A, 'req_idem', $product->sku, $order->id);
        $second = $issuer->issue(SupplierName::A, 'req_idem', $product->sku, $order->id);

        $this->assertTrue($first->isOk());
        $this->assertSame($first->code, $second->code);
        $this->assertSame(1, StubSupplierIssue::query()->count());
        $this->assertSame(1, $order->productKeys()->count());
    }
}
