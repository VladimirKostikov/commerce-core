<?php

namespace Tests\Functional;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogStorefrontFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_in_stock_then_open_sku(): void
    {
        Product::factory()->create([
            'sku' => 'GIFT-ROBLOX-800',
            'name' => 'Roblox',
            'price' => 890,
            'is_active' => true,
            'available_keys_count' => 7,
        ]);
        Product::factory()->create([
            'sku' => 'EMPTY-SKU',
            'is_active' => true,
            'available_keys_count' => 0,
        ]);
        Product::factory()->inactive()->create([
            'sku' => 'OFF-SKU',
            'available_keys_count' => 40,
        ]);

        $sku = $this->getJson('/api/catalog?limit=10')
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->json('items.0.sku');

        $this->assertSame('GIFT-ROBLOX-800', $sku);

        $this->getJson('/api/catalog/'.$sku)
            ->assertOk()
            ->assertJsonPath('sku', 'GIFT-ROBLOX-800')
            ->assertJsonPath('available_keys_count', 7);

        $this->getJson('/api/catalog/OFF-SKU')->assertUnprocessable();
        $this->getJson('/api/catalog/EMPTY-SKU')
            ->assertOk()
            ->assertJsonPath('available_keys_count', 0);
    }
}
