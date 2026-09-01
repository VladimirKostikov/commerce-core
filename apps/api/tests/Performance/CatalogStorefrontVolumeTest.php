<?php

namespace Tests\Performance;

use App\Models\Product;
use App\Services\Catalog\CatalogStorefront;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogStorefrontVolumeTest extends TestCase
{
    use RefreshDatabase;

    public function test_volume_of_skus_keeps_storefront_query_fast(): void
    {
        $now = now();
        $rows = [];

        for ($i = 1; $i <= 3000; $i++) {
            $rows[] = [
                'sku' => sprintf('LOAD-%05d', $i),
                'name' => 'Load '.$i,
                'type' => 'key',
                'price' => 500,
                'currency' => 'RUB',
                'image' => null,
                'is_active' => $i % 17 === 0 ? 0 : 1,
                'available_keys_count' => $i % 11 === 0 ? 0 : 1 + ($i % 20),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Product::query()->insert($chunk);
        }

        $startedAt = microtime(true);
        $items = $this->app->make(CatalogStorefront::class)->inStock(50);
        $elapsed = microtime(true) - $startedAt;

        $this->assertCount(50, $items);
        $this->assertLessThan(0.25, $elapsed);
        $this->assertGreaterThan(0, $items[0]['available_keys_count']);
    }

    public function test_spike_of_catalog_list_requests_succeed(): void
    {
        Product::factory()->count(20)->create([
            'is_active' => true,
            'available_keys_count' => 3,
        ]);

        for ($i = 0; $i < 30; $i++) {
            $this->getJson('/api/catalog?limit=20')->assertOk()->assertJsonCount(20, 'items');
        }
    }

    public function test_endurance_of_catalog_list_stays_bounded(): void
    {
        Product::factory()->count(40)->create([
            'is_active' => true,
            'available_keys_count' => 5,
        ]);

        $startedAt = microtime(true);

        for ($i = 0; $i < 80; $i++) {
            $this->getJson('/api/catalog?limit=50')->assertOk();
        }

        $this->assertLessThan(8.0, microtime(true) - $startedAt);
    }
}
