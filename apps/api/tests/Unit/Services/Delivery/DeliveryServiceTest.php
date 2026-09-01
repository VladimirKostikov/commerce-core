<?php

namespace Tests\Unit\Services\Delivery;

use App\Contracts\SupplierInterface;
use App\Dto\SupplierIssueResult;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Delivery\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeSupplier;
use Tests\Support\CommerceCatalog;
use Tests\TestCase;

final class DeliveryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deliver_marks_order_delivered_with_code(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $order = Order::factory()->paid()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);

        $this->app->instance(SupplierInterface::class, new FakeSupplier(
            SupplierIssueResult::ok('LFXC-TNCS-BPCD'),
        ));

        $delivered = $this->app->make(DeliveryService::class)->deliver($order);

        $this->assertSame(OrderStatus::Delivered, $delivered->status);
        $this->assertSame('LFXC-TNCS-BPCD', $delivered->delivery_code);
        $this->assertSame('LFXC-TNCS-BPCD', $this->app->make(DeliveryService::class)->deliver($delivered)->delivery_code);
    }

    public function test_out_of_stock_is_recoverable(): void
    {
        $order = Order::factory()->paid()->create();
        $this->app->instance(SupplierInterface::class, new FakeSupplier(
            SupplierIssueResult::outOfStock(),
        ));

        $result = $this->app->make(DeliveryService::class)->deliver($order);

        $this->assertSame(OrderStatus::OutOfStock, $result->status);
    }

    public function test_timeout_leaves_order_delivering(): void
    {
        $order = Order::factory()->paid()->create();
        $this->app->instance(SupplierInterface::class, new FakeSupplier(
            SupplierIssueResult::timeout(),
        ));

        $result = $this->app->make(DeliveryService::class)->deliver($order);

        $this->assertSame(OrderStatus::Delivering, $result->status);
        $this->assertNull($result->delivery_code);
    }

    public function test_in_flight_order_is_not_issued_again(): void
    {
        $order = Order::factory()->paid()->create();
        $order->status = OrderStatus::Delivering;
        $order->delivery_request_id = 'req_busy';
        $order->save();

        $this->app->instance(SupplierInterface::class, new FakeSupplier(
            SupplierIssueResult::ok('SHOULD-NOT-ISSUE'),
        ));

        $result = $this->app->make(DeliveryService::class)->deliver($order);

        $this->assertSame(OrderStatus::Delivering, $result->status);
        $this->assertNull($result->delivery_code);
    }
}
