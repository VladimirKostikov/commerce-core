<?php

namespace App\Contracts;

interface CatalogStorefrontInterface
{
    public function inStock(int $limit): array;
}
