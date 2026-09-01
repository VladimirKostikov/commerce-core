<?php

namespace App\Services\Catalog;

use App\Exceptions\InactiveProductException;
use App\Exceptions\ProductNotFoundException;
use App\Models\Product;

final class ProductFinder
{
    public function bySku(string $sku): ?Product
    {
        return Product::query()
            ->where('sku', strtoupper(trim($sku)))
            ->first();
    }

    public function activeBySku(string $sku): Product
    {
        $product = $this->bySku($sku);

        return match ($product?->is_active) {
            true => $product,
            false => throw new InactiveProductException(),
            null => throw new ProductNotFoundException(),
        };
    }
}
