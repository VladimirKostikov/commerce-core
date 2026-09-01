<?php

namespace Tests\Support;

use App\Enums\ProductKeyStatus;
use App\Models\Product;
use App\Models\ProductKey;

final class CommerceCatalog
{
    public static function productWithAvailableKey(
        string $sku = 'STEAM-TOPUP-500',
        string $code = 'LFXC-TNCS-BPCD',
        int $price = 500,
    ): Product {
        $product = Product::factory()->create([
            'sku' => $sku,
            'price' => $price,
            'available_keys_count' => 1,
        ]);

        ProductKey::factory()->create([
            'product_id' => $product->id,
            'code' => $code,
            'status' => ProductKeyStatus::Available,
        ]);

        return $product;
    }
}
