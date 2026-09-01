<?php

namespace Tests\Unit\Services\Catalog;

use App\Models\Product;
use App\Services\Catalog\CachedCatalogStorefront;
use App\Services\Catalog\CatalogStorefront;
use App\Services\Catalog\StorefrontCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class CachedCatalogStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_second_read_uses_cache_until_bust(): void
    {
        Cache::flush();
        Product::factory()->create([
            'sku' => 'CACHED-HOT',
            'is_active' => true,
            'available_keys_count' => 4,
        ]);

        $storefront = new CachedCatalogStorefront(
            $this->app->make(CatalogStorefront::class),
            $this->app->make(StorefrontCache::class),
        );

        $this->assertSame(['CACHED-HOT'], array_column($storefront->inStock(10), 'sku'));

        Product::query()->update(['available_keys_count' => 0]);
        $this->assertSame(['CACHED-HOT'], array_column($storefront->inStock(10), 'sku'));

        $this->app->make(StorefrontCache::class)->bust();
        $this->assertSame([], $storefront->inStock(10));
    }
}
