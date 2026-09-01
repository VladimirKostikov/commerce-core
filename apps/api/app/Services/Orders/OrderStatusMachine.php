<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;

final class OrderStatusMachine
{
    public function canTransition(OrderStatus $from, OrderStatus $to): bool
    {
        if ($from === $to) {
            return false;
        }

        return in_array($to, $this->allowed($from), true);
    }

    public function transition(Order $order, OrderStatus $to): bool
    {
        if (! $this->canTransition($order->status, $to)) {
            return false;
        }

        $order->status = $to;
        $order->lock_version = $order->lock_version + 1;

        return true;
    }

    private function allowed(OrderStatus $from): array
    {
        return match ($from) {
            OrderStatus::Created => [OrderStatus::Paid, OrderStatus::PaymentFailed],
            OrderStatus::Paid => [OrderStatus::Delivering],
            OrderStatus::Delivering => [
                OrderStatus::Delivered,
                OrderStatus::OutOfStock,
                OrderStatus::DeliveryFailed,
            ],
            OrderStatus::OutOfStock, OrderStatus::DeliveryFailed => [OrderStatus::Delivering],
            OrderStatus::Delivered, OrderStatus::PaymentFailed => [],
        };
    }
}
