<?php

namespace Tests\Unit\Services\Orders;

use App\Contracts\PaymentWebhookServiceInterface;
use App\Enums\OrderStatus;
use App\Exceptions\InactiveProductException;
use App\Exceptions\ProductNotFoundException;
use App\Models\Product;
use App\Models\User;
use App\Services\Orders\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\TestCase;

final class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_copies_price_from_product(): void
    {
        $user = User::factory()->create();
        $product = CommerceCatalog::productWithAvailableKey();
        $order = $this->app->make(OrderService::class)->create($user, $product->sku);

        $this->assertSame($product->sku, $order->sku);
        $this->assertSame($product->price, $order->amount);
        $this->assertSame(OrderStatus::Created, $order->status);
        $this->assertTrue($order->user->is($user));
    }

    public function test_unknown_sku_is_not_found(): void
    {
        $this->expectException(ProductNotFoundException::class);

        $this->app->make(OrderService::class)->create(User::factory()->create(), 'MISSING-SKU');
    }

    public function test_inactive_sku_is_rejected(): void
    {
        Product::factory()->inactive()->create(['sku' => 'KEY-GTA5']);

        $this->expectException(InactiveProductException::class);

        $this->app->make(OrderService::class)->create(User::factory()->create(), 'KEY-GTA5');
    }

    public function test_create_applies_pending_payment(): void
    {
        $user = User::factory()->create();
        $product = CommerceCatalog::productWithAvailableKey();
        $pending = $this->createMock(PaymentWebhookServiceInterface::class);
        $pending->expects($this->once())->method('applyPendingForOrder');

        $this->app->instance(PaymentWebhookServiceInterface::class, $pending);

        $order = $this->app->make(OrderService::class)->create($user, $product->sku);

        $this->assertSame(OrderStatus::Created, $order->status);
    }
}
