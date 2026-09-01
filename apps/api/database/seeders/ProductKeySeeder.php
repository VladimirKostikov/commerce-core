<?php

namespace Database\Seeders;

use App\Enums\ProductKeyStatus;
use App\Models\Product;
use App\Models\ProductKey;
use Database\Seeders\Catalog\CatalogFixture;
use Illuminate\Database\Seeder;
use RuntimeException;

class ProductKeySeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()->orderBy('id')->get();

        if ($products->isEmpty()) {
            throw new RuntimeException('Products must be seeded before keys');
        }

        $keys = CatalogFixture::keys();

        foreach ($keys as $index => $code) {
            $product = $products[$index % $products->count()];

            ProductKey::query()->updateOrCreate(
                ['code' => $code],
                [
                    'product_id' => $product->id,
                    'status' => ProductKeyStatus::Available,
                    'order_id' => null,
                ],
            );
        }

        foreach ($products as $product) {
            $product->available_keys_count = $product->keys()
                ->where('status', ProductKeyStatus::Available)
                ->count();
            $product->save();
        }
    }
}
