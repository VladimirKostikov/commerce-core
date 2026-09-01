<?php

namespace Tests\Feature;

use App\Contracts\SupplierInterface;
use App\Dto\SupplierIssueResult;
use App\Enums\LedgerReason;
use App\Enums\OrderStatus;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Services\Ledger\LedgerTotals;
use App\Services\Payments\PaymentWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeSupplier;
use Tests\Support\CommerceCatalog;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class ReconciliationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_checkout_is_absent_from_mismatches_and_ledger_balances(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);
        $this->app->make(PaymentWebhookService::class)->handle(
            PaymentWebhookPayload::paid($order->id, 'evt_rec_ok', $order->amount),
        );

        $this->getJson('/api/reconciliation')
            ->assertOk()
            ->assertJsonPath('paid_not_delivered', [])
            ->assertJsonPath('delivered_not_paid', [])
            ->assertJsonPath('ledger.balanced', true);

        $this->assertSame(4, LedgerEntry::query()->where('order_id', $order->id)->count());
        $this->assertTrue($this->app->make(LedgerTotals::class)->balanced());
        $this->assertTrue(
            LedgerEntry::query()->where('order_id', $order->id)->where('reason', LedgerReason::KeysIssued)->exists(),
        );
    }

    public function test_timeout_order_is_paid_not_delivered(): void
    {
        $order = Order::factory()->create();
        $this->app->instance(SupplierInterface::class, new FakeSupplier(SupplierIssueResult::timeout()));
        $this->app->make(PaymentWebhookService::class)->handle(
            PaymentWebhookPayload::paid($order->id, 'evt_rec_hang', $order->amount),
        );

        $this->assertSame(OrderStatus::Delivering, $order->fresh()?->status);
        $this->getJson('/api/reconciliation')
            ->assertOk()
            ->assertJsonPath('paid_not_delivered.0.id', $order->id)
            ->assertJsonPath('ledger.balanced', true);
    }
}
