<?php

namespace App\Services\Recovery;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Collection;

final class StuckOrderFinder
{
    public function __construct(
        private readonly int $staleAfterSeconds,
    ) {
    }

    public function orders(): Collection
    {
        $cutoff = now()->subSeconds($this->staleAfterSeconds);

        return Order::query()
            ->where(function ($query) use ($cutoff): void {
                $query->whereIn('status', [
                    OrderStatus::Paid,
                    OrderStatus::OutOfStock,
                    OrderStatus::DeliveryFailed,
                ])->orWhere(function ($stuck) use ($cutoff): void {
                    $stuck->where('status', OrderStatus::Delivering)
                        ->where('updated_at', '<=', $cutoff);
                });
            })
            ->orderBy('id')
            ->get();
    }
}
