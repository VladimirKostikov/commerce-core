<?php

namespace Database\Seeders;

use App\Models\Product;
use Database\Seeders\Catalog\CatalogFixture;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach (CatalogFixture::products() as $product) {
            Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'type' => $product['type'],
                    'price' => $product['price'],
                    'currency' => $product['currency'],
                    'image' => $product['image'],
                    'is_active' => true,
                ],
            );
        }
    }
}
