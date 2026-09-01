<?php

namespace App\Services\Catalog;

use App\Contracts\StorefrontCacheInterface;
use Closure;
use Illuminate\Support\Facades\Cache;

final class StorefrontCache implements StorefrontCacheInterface
{
    public function remember(int $limit, Closure $resolve): array
    {
        $cached = Cache::remember($this->key($limit), $this->ttl(), $resolve);

        return is_array($cached) ? $cached : [];
    }

    public function bust(): void
    {
        Cache::increment($this->epochKey());
    }

    private function key(int $limit): string
    {
        return 'catalog:storefront:'.$this->epoch().':'.$limit;
    }

    private function epoch(): int
    {
        return (int) Cache::get($this->epochKey(), 0);
    }

    private function epochKey(): string
    {
        return 'catalog:storefront:epoch';
    }

    private function ttl(): int
    {
        return (int) config('catalog.cache_ttl', 10);
    }
}
