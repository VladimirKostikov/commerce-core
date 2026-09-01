<?php

namespace Tests\Feature;

use App\Contracts\SupplierInterface;
use App\Dto\SupplierIssueResult;
use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\SequenceSupplier;
use Tests\Support\CommerceCatalog;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class RecoverStuckOrdersFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_artisan_recover_stuck_delivers_stale_timeout_order(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);
        $supplier = new SequenceSupplier([
            SupplierIssueResult::timeout(),
            SupplierIssueResult::ok('STUCK-OK-0001'),
        ]);
        $this->app->instance(SupplierInterface::class, $supplier);

        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::paid(
            $order->id,
            'evt_stuck',
            $order->amount,
        ))->assertOk();
        $this->assertSame(OrderStatus::Delivering, $order->fresh()?->status);

        Carbon::setTestNow(now()->addSeconds(31));
        $this->artisan('commerce:recover-stuck')->assertSuccessful();

        $this->assertSame(OrderStatus::Delivered, $order->fresh()?->status);
        $this->assertSame('STUCK-OK-0001', $order->fresh()?->delivery_code);
        Carbon::setTestNow();
    }
}
