<?php

namespace Tests\Unit\Services\Payments;

use App\Models\PaymentEvent;
use App\Services\Payments\PaymentEventStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class PaymentEventStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_event_id_returns_the_same_row(): void
    {
        $store = new PaymentEventStore();
        $payload = PaymentWebhookPayload::paid('ord_store_1', 'evt_store_dup', 500);

        $first = $store->remember($payload);
        $second = $store->remember($payload);

        $this->assertTrue($first->is($second));
        $this->assertSame(1, PaymentEvent::query()->count());
    }
}
