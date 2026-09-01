<?php

namespace Tests\Unit\Services\Catalog;

use App\Models\Product;
use App\Services\Catalog\CatalogStorefront;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_in_stock_skips_inactive_and_empty_and_orders_by_stock(): void
    {
        Product::factory()->create([
            'sku' => 'HOT-LOW',
            'is_active' => true,
            'available_keys_count' => 2,
        ]);
        Product::factory()->create([
            'sku' => 'HOT-HIGH',
            'is_active' => true,
            'available_keys_count' => 9,
        ]);
        Product::factory()->create([
            'sku' => 'COLD-EMPTY',
            'is_active' => true,
            'available_keys_count' => 0,
        ]);
        Product::factory()->inactive()->create([
            'sku' => 'COLD-OFF',
            'available_keys_count' => 40,
        ]);

        $items = $this->app->make(CatalogStorefront::class)->inStock(50);

        $this->assertSame(['HOT-HIGH', 'HOT-LOW'], array_column($items, 'sku'));
        $this->assertSame(9, $items[0]['available_keys_count']);
    }
}
