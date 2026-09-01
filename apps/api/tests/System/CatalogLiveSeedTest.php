<?php

namespace Tests\System;

use PDO;
use PHPUnit\Framework\TestCase;
use Tests\Support\InfrastructureHost;

final class CatalogLiveSeedTest extends TestCase
{
    public function test_live_catalog_has_assignment_products_and_keys(): void
    {
        $pdo = $this->pdo();

        $products = (int) $pdo->query('select count(*) from products')->fetchColumn();
        $keys = (int) $pdo->query('select count(*) from product_keys')->fetchColumn();
        $uniqueKeys = (int) $pdo->query('select count(distinct code) from product_keys')->fetchColumn();

        $this->assertGreaterThanOrEqual(3012, $products);
        $this->assertGreaterThanOrEqual(50, $keys);
        $this->assertSame($keys, $uniqueKeys);
        $this->assertSame(
            1,
            (int) $pdo->query("select count(*) from products where sku = 'STEAM-TOPUP-500'")->fetchColumn(),
        );
    }

    private function pdo(): PDO
    {
        $host = InfrastructureHost::resolve('postgres');
        $dsn = sprintf('pgsql:host=%s;port=5432;dbname=laravel', $host);

        return new PDO($dsn, 'laravel', 'secret', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }
}
