<?php

namespace Tests\Unit\Services\Payments;

use App\Enums\OrderStatus;
use App\Enums\ProductKeyStatus;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Services\Payments\PaymentWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class PaymentWebhookServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_webhook_delivers_key(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);

        $this->app->make(PaymentWebhookService::class)->handle(
            PaymentWebhookPayload::paid($order->id, 'evt_paid_1', $order->amount),
        );

        $order->refresh();

        $this->assertSame(OrderStatus::Delivered, $order->status);
        $this->assertSame('LFXC-TNCS-BPCD', $order->delivery_code);
        $this->assertSame(ProductKeyStatus::Sold, $order->productKeys()->first()?->status);
        $this->assertSame(4, $order->ledgerEntries()->count());
        $this->assertSame(
            (int) $order->ledgerEntries()->where('direction', 'debit')->sum('amount'),
            (int) $order->ledgerEntries()->where('direction', 'credit')->sum('amount'),
        );
    }

    public function test_duplicate_event_id_is_noop(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);
        $service = $this->app->make(PaymentWebhookService::class);
        $payload = PaymentWebhookPayload::paid($order->id, 'evt_dup', $order->amount);

        $service->handle($payload);
        $service->handle($payload);

        $this->assertSame(1, PaymentEvent::query()->count());
        $this->assertSame(1, $order->productKeys()->count());
        $this->assertSame(OrderStatus::Delivered, $order->fresh()?->status);
    }

    public function test_webhook_before_order_is_applied_on_create(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $orderId = 'ord_prewebhook0000000000000001';
        $service = $this->app->make(PaymentWebhookService::class);

        $service->handle(PaymentWebhookPayload::paid($orderId, 'evt_early', $product->price));

        $this->assertNull(PaymentEvent::query()->where('event_id', 'evt_early')->value('processed_at'));

        $order = Order::factory()->create([
            'id' => $orderId,
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);

        $service->applyPendingForOrder($order);

        $this->assertSame(OrderStatus::Delivered, $order->fresh()?->status);
        $this->assertNotNull(PaymentEvent::query()->where('event_id', 'evt_early')->value('processed_at'));
    }

    public function test_failed_webhook_marks_payment_failed(): void
    {
        $order = Order::factory()->create();
        $this->app->make(PaymentWebhookService::class)->handle(
            PaymentWebhookPayload::failed($order->id, 'evt_fail', $order->amount),
        );

        $this->assertSame(OrderStatus::PaymentFailed, $order->fresh()?->status);
    }

    public function test_fifty_paid_events_deliver_one_key(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);
        $service = $this->app->make(PaymentWebhookService::class);

        for ($i = 0; $i < 50; $i++) {
            $service->handle(PaymentWebhookPayload::paid($order->id, 'evt_many_'.$i, $order->amount));
        }

        $this->assertSame(1, $order->productKeys()->count());
        $this->assertSame(1, $order->delivery()->count());
        $this->assertSame(50, PaymentEvent::query()->where('order_id', $order->id)->count());
        $this->assertSame(OrderStatus::Delivered, $order->fresh()?->status);
        $this->assertSame(4, $order->ledgerEntries()->count());
    }
}
