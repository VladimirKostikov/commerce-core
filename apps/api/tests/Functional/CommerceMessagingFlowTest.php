<?php

namespace Tests\Functional;

use App\Contracts\CommerceEventBusInterface;
use App\Contracts\CommerceLoggerInterface;
use App\Contracts\CommerceWorkQueueInterface;
use App\Contracts\DeliveryServiceInterface;
use App\Contracts\PaymentWebhookServiceInterface;
use App\Models\Order;
use App\Services\Logging\FanOutCommerceLogger;
use App\Services\Logging\NullCommerceLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\RecordingCommerceEventBus;
use Tests\Fakes\RecordingCommerceWorkQueue;
use Tests\Support\CommerceCatalog;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class CommerceMessagingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_delivery_publishes_kafka_facts_and_rabbit_notices(): void
    {
        $bus = new RecordingCommerceEventBus();
        $work = new RecordingCommerceWorkQueue();
        $this->app->forgetInstance(DeliveryServiceInterface::class);
        $this->app->forgetInstance(PaymentWebhookServiceInterface::class);
        $this->app->forgetInstance(CommerceLoggerInterface::class);
        $this->app->instance(CommerceEventBusInterface::class, $bus);
        $this->app->instance(CommerceWorkQueueInterface::class, $work);
        $this->app->instance(
            CommerceLoggerInterface::class,
            new FanOutCommerceLogger(new NullCommerceLogger(), $bus),
        );

        $product = CommerceCatalog::productWithAvailableKey('MSG-SKU');
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);

        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::paid(
            $order->id,
            'evt_msg_flow',
            $order->amount,
        ))->assertOk();

        $events = array_column($bus->published, 'event');
        $kinds = array_column($work->items, 'kind');

        $this->assertContains('accepted', $events);
        $this->assertContains('issued', $events);
        $this->assertContains('payment_accepted', $kinds);
        $this->assertContains('issued', $kinds);
    }
}
