<?php

namespace Tests\Database;

use App\Services\Catalog\StorefrontCache;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class StorefrontCacheIsolationTest extends TestCase
{
    public function test_epoch_increment_does_not_share_payload_keys(): void
    {
        Cache::flush();
        $cache = new StorefrontCache();
        $cache->remember(7, fn (): array => [['sku' => 'E1']]);
        $cache->bust();
        $cache->remember(7, fn (): array => [['sku' => 'E2']]);

        $this->assertSame([['sku' => 'E2']], $cache->remember(7, fn (): array => [['sku' => 'E3']]));
        $this->assertGreaterThan(0, (int) Cache::get('catalog:storefront:epoch'));
    }
}
