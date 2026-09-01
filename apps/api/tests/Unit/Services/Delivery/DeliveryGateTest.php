<?php

namespace Tests\Unit\Services\Delivery;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Delivery\DeliveryGate;
use App\Services\Orders\OrderStatusMachine;
use Carbon\CarbonImmutable;
use Tests\TestCase;

final class DeliveryGateTest extends TestCase
{
    public function test_terminal_statuses(): void
    {
        $gate = new DeliveryGate(new OrderStatusMachine());

        $this->assertTrue($gate->isTerminal($this->order(OrderStatus::Created)));
        $this->assertTrue($gate->isTerminal($this->order(OrderStatus::Delivered)));
        $this->assertTrue($gate->isTerminal($this->order(OrderStatus::PaymentFailed)));
        $this->assertFalse($gate->isTerminal($this->order(OrderStatus::Paid)));
        $this->assertFalse($gate->isTerminal($this->order(OrderStatus::OutOfStock)));
        $this->assertTrue($gate->isInFlight($this->order(OrderStatus::Delivering)));
        $this->assertFalse($gate->isInFlight($this->order(OrderStatus::Paid)));
    }

    public function test_start_moves_paid_to_delivering(): void
    {
        $order = $this->order(OrderStatus::Paid);
        $order->id = 'ord_gate';

        (new DeliveryGate(new OrderStatusMachine()))->start($order);

        $this->assertSame(OrderStatus::Delivering, $order->status);
        $this->assertSame('req_ord_gate', $order->delivery_request_id);
    }

    public function test_stale_in_flight_uses_updated_at(): void
    {
        $gate = new DeliveryGate(new OrderStatusMachine(), 30);
        $fresh = $this->order(OrderStatus::Delivering);
        $fresh->updated_at = CarbonImmutable::now();
        $stale = $this->order(OrderStatus::Delivering);
        $stale->updated_at = CarbonImmutable::now()->subSeconds(31);

        $this->assertFalse($gate->isStale($fresh));
        $this->assertTrue($gate->isStale($stale));
    }

    private function order(OrderStatus $status): Order
    {
        $order = new Order();
        $order->status = $status;
        $order->lock_version = 0;
        $order->delivery_request_id = null;

        return $order;
    }
}
