<?php

namespace Tests\Performance;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class PaymentWebhookVolumeTest extends TestCase
{
    use RefreshDatabase;

    public function test_volume_of_distinct_paid_events_delivers_once(): void
    {
        $this->actingAsApi();
        $product = CommerceCatalog::productWithAvailableKey();
        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku])->json('id');

        for ($i = 0; $i < 50; $i++) {
            $this->postJson(
                '/api/webhook/payment',
                PaymentWebhookPayload::paid($orderId, 'evt_vol_'.$i, $product->price),
            )->assertOk();
        }

        $order = Order::query()->find($orderId);
        $this->assertSame('delivered', $order?->status->value);
        $this->assertSame(1, $order?->productKeys()->count());
        $this->assertSame(1, $order?->delivery()->count());
    }

    public function test_spike_of_duplicate_event_ids_stays_idempotent(): void
    {
        $this->actingAsApi();
        $product = CommerceCatalog::productWithAvailableKey();
        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku])->json('id');
        $payload = PaymentWebhookPayload::paid($orderId, 'evt_spike', $product->price);

        for ($i = 0; $i < 40; $i++) {
            $this->postJson('/api/webhook/payment', $payload)->assertOk();
        }

        $this->assertSame(1, Order::query()->find($orderId)?->productKeys()->count());
    }
}
