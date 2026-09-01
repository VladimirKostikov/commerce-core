<?php

namespace Tests\Unit\Catalog;

use Database\Seeders\Catalog\CatalogFixture;
use PHPUnit\Framework\TestCase;

final class CatalogFixtureTest extends TestCase
{
    public function test_fixture_matches_assignment_size_and_uniqueness(): void
    {
        $products = CatalogFixture::products();
        $keys = CatalogFixture::keys();
        $skus = array_column($products, 'sku');

        $this->assertCount(12, $products);
        $this->assertCount(50, $keys);
        $this->assertSame(count($skus), count(array_unique($skus)));
        $this->assertSame(count($keys), count(array_unique($keys)));
        $this->assertContains('STEAM-TOPUP-500', $skus);
        $this->assertContains('LFXC-TNCS-BPCD', $keys);
        $this->assertContains('7EQM-K09J-XKUO', $keys);
    }
}
