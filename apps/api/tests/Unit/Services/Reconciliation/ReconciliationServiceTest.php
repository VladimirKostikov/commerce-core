<?php

namespace Tests\Unit\Services\Reconciliation;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Services\Reconciliation\ReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReconciliationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_not_delivered_and_delivered_not_paid(): void
    {
        $paidStuck = Order::factory()->paid()->create();
        PaymentEvent::factory()->create([
            'order_id' => $paidStuck->id,
            'status' => PaymentStatus::Paid,
            'processed_at' => now(),
            'amount' => $paidStuck->amount,
        ]);

        $ghost = Order::factory()->delivered()->create([
            'delivery_code' => 'GHOST-KEY-0001',
        ]);

        $report = $this->app->make(ReconciliationService::class)->report();
        $payload = $report->toArray();

        $this->assertSame([$paidStuck->id], array_column($payload['paid_not_delivered'], 'id'));
        $this->assertSame([$ghost->id], array_column($payload['delivered_not_paid'], 'id'));
        $this->assertTrue($payload['ledger']['balanced']);
    }

    public function test_delivered_paid_order_is_absent(): void
    {
        $order = Order::factory()->delivered()->create([
            'delivery_code' => 'LFXC-TNCS-BPCD',
        ]);
        PaymentEvent::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatus::Paid,
            'processed_at' => now(),
            'amount' => $order->amount,
        ]);

        $report = $this->app->make(ReconciliationService::class)->report();

        $this->assertSame([], $report->toArray()['paid_not_delivered']);
        $this->assertSame([], $report->toArray()['delivered_not_paid']);
        $this->assertSame(OrderStatus::Delivered, $order->status);
    }
}
