<?php

namespace Tests\Functional;

use App\Enums\OrderStatus;
use App\Models\ProductKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class OrderCheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_create_pay_and_fetch_delivered_order(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();

        $token = $this->postJson('/api/register', [
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ])->json('token');

        $headers = ['Authorization' => 'Bearer '.$token];

        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku], $headers)
            ->assertCreated()
            ->json('id');

        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::paid($orderId, 'evt_flow', $product->price))
            ->assertOk();

        $this->getJson('/api/orders/'.$orderId, $headers)
            ->assertOk()
            ->assertJsonPath('status', OrderStatus::Delivered->value)
            ->assertJsonPath('delivery_code', 'LFXC-TNCS-BPCD');

        $this->assertSame(1, ProductKey::query()->where('order_id', $orderId)->count());
    }

    public function test_duplicate_paid_webhooks_still_deliver_once(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();

        $token = $this->postJson('/api/register', [
            'name' => 'Buyer',
            'email' => 'buyer-dup@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ])->json('token');

        $headers = ['Authorization' => 'Bearer '.$token];
        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku], $headers)->json('id');
        $payload = PaymentWebhookPayload::paid($orderId, 'evt_flow_dup', $product->price);

        $this->postJson('/api/webhook/payment', $payload)->assertOk();
        $this->postJson('/api/webhook/payment', $payload)->assertOk();
        $this->postJson('/api/webhook/payment', $payload)->assertOk();

        $this->getJson('/api/orders/'.$orderId, $headers)
            ->assertOk()
            ->assertJsonPath('status', OrderStatus::Delivered->value);

        $this->assertSame(1, ProductKey::query()->where('order_id', $orderId)->count());
    }
}
