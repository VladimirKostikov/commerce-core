<?php

namespace Tests\Integration;

use App\Services\Catalog\StorefrontStockQuery;
use PHPUnit\Framework\TestCase;
use Tests\Support\LiveCatalogLoad;
use Tests\Support\LivePostgres;
use Tests\Support\PostgresExplain;

final class StorefrontQueryPlanTest extends TestCase
{
    public function test_storefront_stock_query_uses_covering_index(): void
    {
        LiveCatalogLoad::ensureProducts(3000);
        $pdo = LivePostgres::pdo();

        $indexes = $pdo->query(
            "SELECT indexname FROM pg_indexes WHERE tablename = 'products' AND indexname = 'products_storefront_in_stock_idx'",
        )->fetchColumn();
        $this->assertSame('products_storefront_in_stock_idx', $indexes);

        $pdo->exec('SET enable_seqscan = off');
        $json = $pdo->query('EXPLAIN (FORMAT JSON) '.StorefrontStockQuery::SQL)->fetchColumn();
        $plan = json_decode((string) $json, true);
        $root = $plan[0]['Plan'] ?? [];

        $this->assertTrue(
            PostgresExplain::usesIndex($root, 'products_storefront_in_stock_idx'),
            'storefront query did not use products_storefront_in_stock_idx: '.json_encode(PostgresExplain::nodeTypes($root)),
        );
        $this->assertNotContains('Seq Scan', PostgresExplain::nodeTypes($root));
    }

    public function test_available_keys_claim_index_exists(): void
    {
        $name = LivePostgres::pdo()
            ->query(
                "SELECT indexname FROM pg_indexes WHERE tablename = 'product_keys' AND indexname = 'product_keys_available_claim_idx'",
            )
            ->fetchColumn();

        $this->assertSame('product_keys_available_claim_idx', $name);
    }
}
