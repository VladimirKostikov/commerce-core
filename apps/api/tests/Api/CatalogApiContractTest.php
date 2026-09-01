<?php

namespace Tests\Api;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogApiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_list_json_contract(): void
    {
        Product::factory()->create([
            'sku' => 'HOT-LIST',
            'is_active' => true,
            'available_keys_count' => 5,
        ]);

        $this->getJson('/api/catalog')
            ->assertOk()
            ->assertJsonStructure([
                'items' => [
                    ['sku', 'name', 'price', 'currency', 'type', 'available_keys_count', 'image'],
                ],
            ]);
    }

    public function test_catalog_show_json_contract(): void
    {
        Product::factory()->create([
            'sku' => 'HOT-SHOW',
            'is_active' => true,
            'available_keys_count' => 2,
        ]);

        $this->getJson('/api/catalog/HOT-SHOW')
            ->assertOk()
            ->assertJsonPath('sku', 'HOT-SHOW')
            ->assertJsonPath('available_keys_count', 2);
    }

    public function test_catalog_show_unknown_sku_is_not_found(): void
    {
        $this->getJson('/api/catalog/NO-SUCH-SKU')->assertNotFound();
    }

    public function test_catalog_limit_validation_contract(): void
    {
        $this->getJson('/api/catalog?limit=0')->assertUnprocessable();
        $this->getJson('/api/catalog?limit=101')->assertUnprocessable();
    }
}
