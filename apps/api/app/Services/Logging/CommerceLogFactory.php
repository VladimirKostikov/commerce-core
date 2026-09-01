<?php

namespace App\Services\Logging;

use App\Dto\CommerceLog;
use App\Dto\SupplierIssueResult;
use App\Models\Order;
use App\Models\PaymentEvent;

final class CommerceLogFactory
{
    public function paymentAccepted(Order $order, PaymentEvent $event): CommerceLog
    {
        return new CommerceLog(
            'payment',
            'accepted',
            $order->id,
            (string) $event->event_id,
            $order->status->value,
            'payment accepted',
            ['amount' => $order->amount, 'currency' => $order->currency->value],
        );
    }

    public function paymentFailed(Order $order, PaymentEvent $event): CommerceLog
    {
        return new CommerceLog(
            'payment',
            'failed',
            $order->id,
            (string) $event->event_id,
            $order->status->value,
            'payment failed',
            ['amount' => $order->amount],
        );
    }

    public function delivery(Order $order, SupplierIssueResult $result): CommerceLog
    {
        return new CommerceLog(
            'delivery',
            $this->deliveryEvent($result),
            $order->id,
            (string) ($result->requestId ?? $order->delivery_request_id),
            $order->status->value,
            $result->reason ?? $result->status,
            [
                'code' => $result->code,
                'supplier' => $result->supplier?->value,
            ],
        );
    }

    public function recovery(Order $order): CommerceLog
    {
        return new CommerceLog(
            'delivery',
            'recovery',
            $order->id,
            (string) $order->delivery_request_id,
            $order->status->value,
            'stuck order recovery',
            [],
        );
    }

    private function deliveryEvent(SupplierIssueResult $result): string
    {
        if ($result->isOk()) {
            return 'issued';
        }

        if ($result->isTimeout()) {
            return 'timeout';
        }

        if ($result->isOutOfStock()) {
            return 'out_of_stock';
        }

        return 'failed';
    }
}
