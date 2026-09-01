<?php

namespace App\Services\Delivery;

use App\Contracts\SupplierInterface;
use App\Dto\SupplierIssueResult;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Models\Delivery;
use App\Models\Order;
use App\Services\Orders\OrderStatusMachine;

final class DeliveryOutcomeWriter
{
    public function __construct(
        private readonly SupplierInterface $supplier,
        private readonly OrderStatusMachine $statusMachine,
    ) {
    }

    public function apply(Order $order, Delivery $delivery, SupplierIssueResult $result): Order
    {
        if ($result->isTimeout()) {
            return $this->persist($order, $delivery);
        }

        if ($result->isOk()) {
            return $this->issued($order, $delivery, (string) $result->code, $result);
        }

        if ($result->isOutOfStock()) {
            return $this->failed($order, $delivery, OrderStatus::OutOfStock);
        }

        return $this->failed($order, $delivery, OrderStatus::DeliveryFailed);
    }

    private function issued(
        Order $order,
        Delivery $delivery,
        string $code,
        SupplierIssueResult $result,
    ): Order {
        $delivery->code = $code;
        $delivery->status = DeliveryStatus::Issued;
        $delivery->supplier = $result->supplier ?? $this->supplier->name();
        $order->delivery_code = $code;
        $this->statusMachine->transition($order, OrderStatus::Delivered);

        return $this->persist($order, $delivery);
    }

    private function failed(Order $order, Delivery $delivery, OrderStatus $status): Order
    {
        $delivery->status = DeliveryStatus::Failed;
        $this->statusMachine->transition($order, $status);

        return $this->persist($order, $delivery);
    }

    private function persist(Order $order, Delivery $delivery): Order
    {
        $delivery->save();
        $order->save();

        return $order->fresh() ?? $order;
    }
}
