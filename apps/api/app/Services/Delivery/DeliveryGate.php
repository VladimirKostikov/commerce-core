<?php

namespace App\Services\Delivery;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Orders\OrderStatusMachine;
use Carbon\CarbonImmutable;

final class DeliveryGate
{
    public function __construct(
        private readonly OrderStatusMachine $statusMachine,
        private readonly int $staleAfterSeconds = 30,
    ) {
    }

    public function isTerminal(Order $order): bool
    {
        return $order->status->blocksDelivery();
    }

    public function isInFlight(Order $order): bool
    {
        return $order->status->isInFlight();
    }

    public function isStale(Order $order): bool
    {
        if ($order->updated_at === null) {
            return false;
        }

        $cutoff = CarbonImmutable::now()->subSeconds($this->staleAfterSeconds);

        return $order->updated_at->lte($cutoff);
    }

    public function start(Order $order): void
    {
        if ($order->status->canEnterDelivering()) {
            $this->statusMachine->transition($order, OrderStatus::Delivering);
        }

        if ($order->delivery_request_id === null || $order->delivery_request_id === '') {
            $order->delivery_request_id = 'req_'.$order->id;
        }
    }
}
