<?php

namespace Tests\Support;

final class LiveCatalogLoad
{
    public static function ensureProducts(int $count = 3000): void
    {
        $pdo = LivePostgres::pdo();
        $count = max(1, $count);
        $pdo->exec(
            "INSERT INTO products (sku, name, type, price, currency, image, is_active, available_keys_count, created_at, updated_at)
             SELECT
                'LOAD-' || lpad(g::text, 5, '0'),
                'Load SKU ' || g,
                'key',
                500,
                'RUB',
                NULL,
                TRUE,
                1 + (g % 25),
                NOW(),
                NOW()
             FROM generate_series(1, {$count}) AS g
             ON CONFLICT (sku) DO NOTHING",
        );
        $pdo->exec('ANALYZE products');
    }
}
