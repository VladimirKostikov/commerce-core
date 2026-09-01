<?php

namespace Tests\Unit\Models;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

final class ProductMutatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_sku_is_normalized(): void
    {
        $product = Product::factory()->create([
            'sku' => '  steam-topup-500  ',
        ]);

        $this->assertSame('STEAM-TOPUP-500', $product->sku);
        $this->assertSame(ProductType::Key, $product->type);
    }

    public function test_name_is_trimmed(): void
    {
        $product = Product::factory()->create([
            'name' => '  Steam 500  ',
        ]);

        $this->assertSame('Steam 500', $product->name);
    }

    public function test_negative_price_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Product::factory()->create([
            'price' => -10,
        ]);
    }
}
