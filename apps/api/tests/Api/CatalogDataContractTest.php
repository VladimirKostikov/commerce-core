<?php

namespace Tests\Api;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogDataContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_product_matches_assignment_contract(): void
    {
        $this->seed(ProductSeeder::class);

        $product = Product::query()->where('sku', 'STEAM-TOPUP-500')->first();

        $this->assertNotNull($product);
        $this->assertSame(500, $product->price);
        $this->assertSame('RUB', $product->currency->value);
        $this->assertSame('topup', $product->type->value);
    }
}
