<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_lists_in_stock_and_fetches_sku(): void
    {
        Product::factory()->create([
            'sku' => 'GIFT-ROBLOX-800',
            'name' => 'Roblox',
            'price' => 890,
            'is_active' => true,
            'available_keys_count' => 7,
        ]);
        Product::factory()->create([
            'sku' => 'OUT-OF-STOCK',
            'is_active' => true,
            'available_keys_count' => 0,
        ]);

        $this->getJson('/api/catalog?limit=10')
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.sku', 'GIFT-ROBLOX-800');

        $this->getJson('/api/catalog/GIFT-ROBLOX-800')
            ->assertOk()
            ->assertJsonPath('available_keys_count', 7)
            ->assertJsonPath('price', 890);
    }
}
