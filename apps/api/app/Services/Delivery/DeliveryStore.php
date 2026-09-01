<?php

namespace App\Services\Delivery;

use App\Enums\DeliveryStatus;
use App\Enums\SupplierName;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Database\UniqueConstraintViolationException;

final class DeliveryStore
{
    public function findOrCreate(Order $order): Delivery
    {
        $existing = Delivery::query()
            ->where('order_id', $order->id)
            ->lockForUpdate()
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        try {
            return Delivery::query()->create([
                'order_id' => $order->id,
                'request_id' => $order->delivery_request_id,
                'supplier' => SupplierName::A,
                'status' => DeliveryStatus::Pending,
            ]);
        } catch (UniqueConstraintViolationException) {
            return Delivery::query()
                ->where('order_id', $order->id)
                ->firstOrFail();
        }
    }
}
