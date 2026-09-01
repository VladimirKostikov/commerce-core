<?php

namespace Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;
use Tests\Support\InfrastructureHost;

final class CatalogSchemaTest extends TestCase
{
    public function test_catalog_tables_exist_on_live_database(): void
    {
        $pdo = $this->pdo();
        $tables = [
            'products',
            'orders',
            'product_keys',
            'payment_events',
            'deliveries',
            'ledger_entries',
            'personal_access_tokens',
            'stub_supplier_issues',
        ];

        foreach ($tables as $table) {
            $statement = $pdo->query(sprintf("select to_regclass('public.%s')", $table));
            $this->assertSame($table, $statement->fetchColumn(), $table.' is missing');
        }
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
