<?php

namespace App\Services\Catalog;

use App\Contracts\CatalogStorefrontInterface;
use App\Contracts\StorefrontCacheInterface;

final class CachedCatalogStorefront implements CatalogStorefrontInterface
{
    public function __construct(
        private readonly CatalogStorefront $inner,
        private readonly StorefrontCacheInterface $cache,
    ) {
    }

    public function inStock(int $limit): array
    {
        return $this->cache->remember($limit, fn (): array => $this->inner->inStock($limit));
    }
}
