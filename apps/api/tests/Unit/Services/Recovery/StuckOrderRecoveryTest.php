<?php

namespace Tests\Unit\Services\Recovery;

use App\Contracts\SupplierInterface;
use App\Dto\SupplierIssueResult;
use App\Enums\OrderStatus;
use App\Jobs\RecoverStuckOrdersJob;
use App\Models\Order;
use App\Services\Delivery\DeliveryService;
use App\Services\Recovery\StuckOrderRecovery;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\SequenceSupplier;
use Tests\Support\CommerceCatalog;
use Tests\TestCase;

final class StuckOrderRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_stale_delivering_order_is_issued_with_the_same_request_id(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $order = Order::factory()->paid()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);
        $supplier = new SequenceSupplier([
            SupplierIssueResult::timeout(),
            SupplierIssueResult::ok('RECOVER-CODE-01'),
        ]);
        $this->app->instance(SupplierInterface::class, $supplier);
        $delivery = $this->app->make(DeliveryService::class);

        $stuck = $delivery->deliver($order);
        $this->assertSame(OrderStatus::Delivering, $stuck->status);
        $this->assertNull($stuck->delivery_code);

        $skipped = $delivery->deliver($stuck);
        $this->assertSame(OrderStatus::Delivering, $skipped->status);
        $this->assertCount(1, $supplier->requestIds);

        Carbon::setTestNow(now()->addSeconds(31));
        $this->app->make(StuckOrderRecovery::class)->recoverAll();

        $recovered = $stuck->fresh();
        $this->assertSame(OrderStatus::Delivered, $recovered?->status);
        $this->assertSame('RECOVER-CODE-01', $recovered?->delivery_code);
        $this->assertCount(2, $supplier->requestIds);
        $this->assertSame($supplier->requestIds[0], $supplier->requestIds[1]);

        Carbon::setTestNow();
    }

    public function test_fresh_delivering_order_is_not_reissued(): void
    {
        $order = Order::factory()->paid()->create();
        $supplier = new SequenceSupplier([
            SupplierIssueResult::timeout(),
            SupplierIssueResult::ok('SHOULD-NOT-ISSUE'),
        ]);
        $this->app->instance(SupplierInterface::class, $supplier);
        $delivery = $this->app->make(DeliveryService::class);

        $stuck = $delivery->deliver($order);
        $this->app->make(RecoverStuckOrdersJob::class)->handle(
            $this->app->make(StuckOrderRecovery::class),
        );

        $this->assertSame(OrderStatus::Delivering, $stuck->fresh()?->status);
        $this->assertCount(1, $supplier->requestIds);
    }
}
