<?php

namespace Tests\E2E;

use App\Models\Product;
use Database\Seeders\ProductKeySeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_stock_query_returns_seeded_sku(): void
    {
        $this->seed(ProductSeeder::class);
        $this->seed(ProductKeySeeder::class);

        $row = Product::query()
            ->where('is_active', true)
            ->where('sku', 'GIFT-ROBLOX-800')
            ->first(['sku', 'name', 'price', 'available_keys_count']);

        $this->assertNotNull($row);
        $this->assertSame('GIFT-ROBLOX-800', $row->sku);
        $this->assertSame(890, $row->price);
        $this->assertGreaterThan(0, $row->available_keys_count);
    }
}
