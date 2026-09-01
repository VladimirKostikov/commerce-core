<?php

namespace Tests\Functional;

use App\Enums\OrderStatus;
use App\Enums\SupplierName;
use App\Models\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class SupplierFallbackFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_falls_back_to_supplier_b(): void
    {
        config(['suppliers.a.mode' => 'fail']);
        $product = CommerceCatalog::productWithAvailableKey();
        $this->actingAsApi();
        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku])->json('id');

        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::paid($orderId, 'evt_fn_fb', $product->price))
            ->assertOk();

        $this->getJson('/api/orders/'.$orderId)
            ->assertOk()
            ->assertJsonPath('status', OrderStatus::Delivered->value);

        $this->assertSame(
            SupplierName::B,
            Delivery::query()->where('order_id', $orderId)->first()?->supplier,
        );
    }
}
