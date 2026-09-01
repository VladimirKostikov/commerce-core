<?php

namespace Tests\Unit\Enums;

use App\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

final class OrderStatusTest extends TestCase
{
    public function test_final_statuses(): void
    {
        $this->assertTrue(OrderStatus::Delivered->isFinal());
        $this->assertTrue(OrderStatus::PaymentFailed->isFinal());
        $this->assertFalse(OrderStatus::Created->isFinal());
        $this->assertFalse(OrderStatus::OutOfStock->isFinal());
    }

    public function test_recoverable_statuses(): void
    {
        $this->assertTrue(OrderStatus::OutOfStock->isRecoverable());
        $this->assertTrue(OrderStatus::DeliveryFailed->isRecoverable());
        $this->assertFalse(OrderStatus::Delivered->isRecoverable());
        $this->assertFalse(OrderStatus::Paid->isRecoverable());
    }

    public function test_delivery_blocked_statuses(): void
    {
        $this->assertTrue(OrderStatus::Created->blocksDelivery());
        $this->assertTrue(OrderStatus::Delivered->blocksDelivery());
        $this->assertTrue(OrderStatus::PaymentFailed->blocksDelivery());
        $this->assertFalse(OrderStatus::Paid->blocksDelivery());
        $this->assertFalse(OrderStatus::Delivering->blocksDelivery());
        $this->assertFalse(OrderStatus::OutOfStock->blocksDelivery());
    }

    public function test_can_enter_delivering(): void
    {
        $this->assertTrue(OrderStatus::Paid->canEnterDelivering());
        $this->assertTrue(OrderStatus::OutOfStock->canEnterDelivering());
        $this->assertTrue(OrderStatus::DeliveryFailed->canEnterDelivering());
        $this->assertFalse(OrderStatus::Created->canEnterDelivering());
        $this->assertFalse(OrderStatus::Delivering->canEnterDelivering());
        $this->assertFalse(OrderStatus::Delivered->canEnterDelivering());
    }

    public function test_in_flight_status(): void
    {
        $this->assertTrue(OrderStatus::Delivering->isInFlight());
        $this->assertFalse(OrderStatus::Paid->isInFlight());
        $this->assertFalse(OrderStatus::OutOfStock->isInFlight());
    }
}
