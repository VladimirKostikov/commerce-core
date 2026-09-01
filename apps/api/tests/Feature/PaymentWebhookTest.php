<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Models\ProductKey;
use App\Models\User;
use App\Services\Payments\PaymentWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_webhook_delivers_without_auth(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $token = User::factory()->create()->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku], $headers)
            ->assertCreated()
            ->json('id');

        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::paid($orderId, 'evt_a1b2c3', $product->price))
            ->assertOk()
            ->assertJsonPath('accepted', true);

        $this->getJson('/api/orders/'.$orderId, $headers)
            ->assertOk()
            ->assertJsonPath('status', 'delivered')
            ->assertJsonPath('delivery_code', 'LFXC-TNCS-BPCD');
    }

    public function test_duplicate_event_id_does_not_change_order(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $this->actingAsApi();
        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku])->json('id');
        $payload = PaymentWebhookPayload::paid($orderId, 'evt_same', $product->price);

        $this->postJson('/api/webhook/payment', $payload)->assertOk();
        $this->postJson('/api/webhook/payment', $payload)->assertOk();

        $this->assertSame(1, PaymentEvent::query()->count());
        $this->assertSame(1, ProductKey::query()->where('order_id', $orderId)->count());
        $this->assertSame(OrderStatus::Delivered, Order::query()->find($orderId)?->status);
    }

    public function test_failed_webhook_sets_payment_failed(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $this->actingAsApi();
        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku])->json('id');

        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::failed($orderId, 'evt_fail', $product->price))
            ->assertOk();

        $this->assertSame(OrderStatus::PaymentFailed, Order::query()->find($orderId)?->status);
        $this->assertNull(Order::query()->find($orderId)?->delivery_code);
    }

    public function test_out_of_stock_is_recoverable_status(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        ProductKey::query()->delete();
        $product->available_keys_count = 0;
        $product->save();

        $this->actingAsApi();
        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku])->json('id');
        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::paid($orderId, 'evt_oos', $product->price))
            ->assertOk();

        $this->assertSame(OrderStatus::OutOfStock, Order::query()->find($orderId)?->status);
    }

    public function test_paid_after_failed_does_not_reopen_order(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $this->actingAsApi();
        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku])->json('id');

        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::failed($orderId, 'evt_fail_first', $product->price))
            ->assertOk();
        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::paid($orderId, 'evt_paid_late', $product->price))
            ->assertOk();

        $this->assertSame(OrderStatus::PaymentFailed, Order::query()->find($orderId)?->status);
    }

    public function test_webhook_for_unknown_order_is_accepted_unprocessed(): void
    {
        $this->postJson(
            '/api/webhook/payment',
            PaymentWebhookPayload::paid('ord_missing_early', 'evt_early_http'),
        )->assertOk();

        $event = PaymentEvent::query()->where('event_id', 'evt_early_http')->first();

        $this->assertNotNull($event);
        $this->assertNull($event->processed_at);
    }

    public function test_pending_webhook_delivers_when_order_appears(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $orderId = 'ord_prehttp000000000000000001';

        $this->postJson(
            '/api/webhook/payment',
            PaymentWebhookPayload::paid($orderId, 'evt_early_apply', $product->price),
        )->assertOk();

        $order = Order::factory()->create([
            'id' => $orderId,
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);

        $this->app->make(PaymentWebhookService::class)->applyPendingForOrder($order);

        $this->assertSame(OrderStatus::Delivered, $order->fresh()?->status);
        $this->assertNotNull(PaymentEvent::query()->where('event_id', 'evt_early_apply')->value('processed_at'));
    }

    public function test_failed_after_paid_does_not_undo_delivery(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $this->actingAsApi();
        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku])->json('id');

        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::paid($orderId, 'evt_paid_first', $product->price))
            ->assertOk();
        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::failed($orderId, 'evt_fail_late', $product->price))
            ->assertOk();

        $order = Order::query()->find($orderId);

        $this->assertSame(OrderStatus::Delivered, $order?->status);
        $this->assertNotNull($order?->delivery_code);
    }
}
