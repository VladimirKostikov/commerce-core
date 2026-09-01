<?php

namespace App\Contracts;

use App\Models\ProductKey;

interface ProductKeyInventoryInterface
{
    public function claim(int $productId, string $orderId): ?ProductKey;
}
