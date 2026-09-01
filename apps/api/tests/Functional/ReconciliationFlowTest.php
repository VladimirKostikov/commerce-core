<?php

namespace Tests\Functional;

use App\Models\Order;
use App\Services\Payments\PaymentWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class ReconciliationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconcile_command_after_checkout_is_clean(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $this->actingAsApi();
        $orderId = $this->postJson('/api/orders', ['sku' => $product->sku])->json('id');
        $this->app->make(PaymentWebhookService::class)->handle(
            PaymentWebhookPayload::paid($orderId, 'evt_fn_rec', (int) Order::query()->find($orderId)?->amount),
        );

        $this->artisan('commerce:reconcile')
            ->expectsOutputToContain('"balanced":true')
            ->assertSuccessful();
    }
}
