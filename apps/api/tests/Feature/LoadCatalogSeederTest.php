<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\LoadCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LoadCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_load_catalog_seeder_inserts_thousands_of_skus_once(): void
    {
        $this->seed(LoadCatalogSeeder::class);
        $this->seed(LoadCatalogSeeder::class);

        $this->assertSame(3000, Product::query()->where('sku', 'like', 'LOAD-%')->count());
        $this->assertTrue(Product::query()->where('sku', 'LOAD-00001')->exists());
        $this->assertGreaterThan(0, Product::query()->where('sku', 'LOAD-00001')->value('available_keys_count'));

        $this->getJson('/api/catalog')
            ->assertOk()
            ->assertJsonCount(50, 'items');
    }
}
