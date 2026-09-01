<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\SupplierName;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\ProductKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class SupplierFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_hang_then_same_request_id_delivers_one_code(): void
    {
        config(['suppliers.a.mode' => 'hang']);
        $product = CommerceCatalog::productWithAvailableKey();
        $this->actingAsApi();
        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku])->json('id');

        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::paid($orderId, 'evt_hang', $product->price))
            ->assertOk();

        $order = Order::query()->find($orderId);

        $this->assertSame(OrderStatus::Delivered, $order?->status);
        $this->assertSame('LFXC-TNCS-BPCD', $order?->delivery_code);
        $this->assertSame(1, ProductKey::query()->where('order_id', $orderId)->count());
        $this->assertSame(1, Delivery::query()->where('order_id', $orderId)->count());
        $this->assertSame(SupplierName::A, $order?->delivery?->supplier);
    }

    public function test_a_down_falls_back_to_b_once(): void
    {
        config(['suppliers.a.mode' => 'fail']);
        $product = CommerceCatalog::productWithAvailableKey();
        $this->actingAsApi();
        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku])->json('id');

        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::paid($orderId, 'evt_fb', $product->price))
            ->assertOk();

        $order = Order::query()->find($orderId);
        $delivery = Delivery::query()->where('order_id', $orderId)->first();

        $this->assertSame(OrderStatus::Delivered, $order?->status);
        $this->assertSame(SupplierName::B, $delivery?->supplier);
        $this->assertTrue(str_ends_with((string) $delivery?->request_id, '_b'));
        $this->assertSame(1, ProductKey::query()->where('order_id', $orderId)->count());
    }

    public function test_both_suppliers_down_mark_delivery_failed(): void
    {
        config([
            'suppliers.a.mode' => 'fail',
            'suppliers.b.mode' => 'fail',
        ]);
        $product = CommerceCatalog::productWithAvailableKey();
        $this->actingAsApi();
        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku])->json('id');

        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::paid($orderId, 'evt_down', $product->price))
            ->assertOk();

        $order = Order::query()->find($orderId);

        $this->assertSame(OrderStatus::DeliveryFailed, $order?->status);
        $this->assertNull($order?->delivery_code);
        $this->assertSame(0, ProductKey::query()->where('order_id', $orderId)->count());
    }
}
