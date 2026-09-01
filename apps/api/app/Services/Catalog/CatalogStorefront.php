<?php

namespace App\Services\Catalog;

use App\Contracts\CatalogStorefrontInterface;
use App\Dto\CatalogItem;
use App\Models\Product;

final class CatalogStorefront implements CatalogStorefrontInterface
{
    public function inStock(int $limit): array
    {
        return Product::query()
            ->where('is_active', true)
            ->where('available_keys_count', '>', 0)
            ->orderByDesc('available_keys_count')
            ->orderBy('sku')
            ->limit($limit)
            ->get(['sku', 'name', 'price', 'currency', 'type', 'image', 'available_keys_count'])
            ->map(fn (Product $product): array => CatalogItem::fromModel($product)->toArray())
            ->all();
    }
}
