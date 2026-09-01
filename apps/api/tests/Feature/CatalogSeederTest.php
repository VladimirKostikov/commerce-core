<?php

namespace Tests\Feature;

use App\Enums\ProductKeyStatus;
use App\Models\Product;
use App\Models\ProductKey;
use Database\Seeders\Catalog\CatalogFixture;
use Database\Seeders\ProductKeySeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_seed_creates_assignment_fixture(): void
    {
        $this->seed(ProductSeeder::class);
        $this->seed(ProductKeySeeder::class);

        $this->assertSame(count(CatalogFixture::products()), Product::query()->count());
        $this->assertSame(count(CatalogFixture::keys()), ProductKey::query()->count());
        $this->assertSame(
            count(CatalogFixture::keys()),
            ProductKey::query()->pluck('code')->unique()->count(),
        );
        $this->assertSame(
            count(CatalogFixture::keys()),
            (int) Product::query()->sum('available_keys_count'),
        );
        $this->assertTrue(Product::query()->where('sku', 'STEAM-TOPUP-500')->exists());
        $this->assertTrue(ProductKey::query()->where('code', 'LFXC-TNCS-BPCD')->exists());
        $this->assertSame(0, ProductKey::query()->where('status', '!=', ProductKeyStatus::Available)->count());
    }
}
