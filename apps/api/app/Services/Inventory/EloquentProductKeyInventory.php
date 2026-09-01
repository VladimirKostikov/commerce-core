<?php

namespace App\Services\Inventory;

use App\Contracts\ProductKeyInventoryInterface;
use App\Contracts\StorefrontCacheInterface;
use App\Enums\ProductKeyStatus;
use App\Models\Product;
use App\Models\ProductKey;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

final class EloquentProductKeyInventory implements ProductKeyInventoryInterface
{
    public function __construct(
        private readonly StorefrontCacheInterface $cache,
    ) {
    }

    public function claim(int $productId, string $orderId): ?ProductKey
    {
        return DB::transaction(fn (): ?ProductKey => $this->claimLocked($productId, $orderId));
    }

    private function claimLocked(int $productId, string $orderId): ?ProductKey
    {
        $owned = $this->owned($orderId);

        if ($owned !== null) {
            return $owned;
        }

        $key = $this->nextAvailable($productId);

        if ($key === null) {
            return null;
        }

        try {
            return $this->sell($key, $productId, $orderId);
        } catch (UniqueConstraintViolationException) {
            return $this->owned($orderId);
        }
    }

    private function owned(string $orderId): ?ProductKey
    {
        return ProductKey::query()
            ->where('order_id', $orderId)
            ->lockForUpdate()
            ->first();
    }

    private function nextAvailable(int $productId): ?ProductKey
    {
        $query = ProductKey::query()
            ->where('product_id', $productId)
            ->where('status', ProductKeyStatus::Available)
            ->whereNull('order_id')
            ->orderBy('id');

        if (DB::connection()->getDriverName() === 'pgsql') {
            $query->lock('for update skip locked');
        } else {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function sell(ProductKey $key, int $productId, string $orderId): ProductKey
    {
        $key->order_id = $orderId;
        $key->status = ProductKeyStatus::Sold;
        $key->save();
        $this->refreshStock($productId);

        return $key;
    }

    private function refreshStock(int $productId): void
    {
        Product::query()
            ->where('id', $productId)
            ->where('available_keys_count', '>', 0)
            ->decrement('available_keys_count');
        $this->cache->bust();
    }
}
