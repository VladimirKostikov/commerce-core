<?php

namespace Tests\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class StorefrontIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_covering_index_exists(): void
    {
        $names = array_column(Schema::getIndexes('products'), 'name');

        $this->assertContains('products_storefront_in_stock_idx', $names);
    }

    public function test_available_keys_claim_index_exists(): void
    {
        $names = array_column(Schema::getIndexes('product_keys'), 'name');

        $this->assertContains('product_keys_available_claim_idx', $names);
    }
}
