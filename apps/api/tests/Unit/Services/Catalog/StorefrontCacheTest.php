<?php

namespace Tests\Unit\Services\Catalog;

use App\Services\Catalog\StorefrontCache;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class StorefrontCacheTest extends TestCase
{
    public function test_remember_returns_resolver_and_bust_changes_key(): void
    {
        Cache::flush();
        $cache = new StorefrontCache();
        $calls = 0;

        $first = $cache->remember(5, function () use (&$calls): array {
            $calls++;

            return [['sku' => 'A']];
        });
        $second = $cache->remember(5, function () use (&$calls): array {
            $calls++;

            return [['sku' => 'B']];
        });

        $this->assertSame([['sku' => 'A']], $first);
        $this->assertSame([['sku' => 'A']], $second);
        $this->assertSame(1, $calls);

        $cache->bust();
        $third = $cache->remember(5, function () use (&$calls): array {
            $calls++;

            return [['sku' => 'C']];
        });

        $this->assertSame([['sku' => 'C']], $third);
        $this->assertSame(2, $calls);
    }
}
