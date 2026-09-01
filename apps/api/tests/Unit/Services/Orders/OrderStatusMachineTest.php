<?php

namespace Tests\Unit\Services\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Orders\OrderStatusMachine;
use PHPUnit\Framework\TestCase;

final class OrderStatusMachineTest extends TestCase
{
    public function test_main_path_transitions(): void
    {
        $machine = new OrderStatusMachine();
        $order = new Order();
        $order->status = OrderStatus::Created;
        $order->lock_version = 0;

        $this->assertTrue($machine->transition($order, OrderStatus::Paid));
        $this->assertTrue($machine->transition($order, OrderStatus::Delivering));
        $this->assertTrue($machine->transition($order, OrderStatus::Delivered));
        $this->assertFalse($machine->transition($order, OrderStatus::Paid));
        $this->assertSame(3, $order->lock_version);
    }

    public function test_payment_failed_is_final(): void
    {
        $machine = new OrderStatusMachine();
        $order = new Order();
        $order->status = OrderStatus::Created;
        $order->lock_version = 0;

        $this->assertTrue($machine->transition($order, OrderStatus::PaymentFailed));
        $this->assertFalse($machine->transition($order, OrderStatus::Paid));
    }

    public function test_out_of_stock_can_retry_delivery(): void
    {
        $machine = new OrderStatusMachine();
        $order = new Order();
        $order->status = OrderStatus::Delivering;
        $order->lock_version = 1;

        $this->assertTrue($machine->transition($order, OrderStatus::OutOfStock));
        $this->assertTrue($machine->transition($order, OrderStatus::Delivering));
        $this->assertTrue($machine->transition($order, OrderStatus::Delivered));
    }
}
