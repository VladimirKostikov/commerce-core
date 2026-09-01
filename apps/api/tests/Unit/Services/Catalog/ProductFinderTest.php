<?php

namespace Tests\Unit\Services\Catalog;

use App\Exceptions\InactiveProductException;
use App\Exceptions\ProductNotFoundException;
use App\Models\Product;
use App\Services\Catalog\ProductFinder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductFinderTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_by_sku_returns_product(): void
    {
        $product = Product::factory()->create(['sku' => 'KEY-GTA5']);

        $found = (new ProductFinder())->activeBySku('key-gta5');

        $this->assertTrue($found->is($product));
    }

    public function test_unknown_sku_is_not_found(): void
    {
        $this->expectException(ProductNotFoundException::class);

        (new ProductFinder())->activeBySku('MISSING');
    }

    public function test_inactive_sku_is_rejected(): void
    {
        Product::factory()->inactive()->create(['sku' => 'KEY-GTA5']);

        $this->expectException(InactiveProductException::class);

        (new ProductFinder())->activeBySku('KEY-GTA5');
    }
}
