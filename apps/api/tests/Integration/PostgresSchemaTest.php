<?php

namespace Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;
use Tests\Support\InfrastructureHost;

final class PostgresSchemaTest extends TestCase
{
    public function test_users_table_exists_on_live_database(): void
    {
        $host = InfrastructureHost::resolve('postgres');
        $dsn = sprintf('pgsql:host=%s;port=5432;dbname=laravel', $host);
        $pdo = new PDO($dsn, 'laravel', 'secret', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $statement = $pdo->query("select to_regclass('public.users')");

        $this->assertSame('users', $statement->fetchColumn());
    }

    public function test_live_database_accepts_select(): void
    {
        $host = InfrastructureHost::resolve('postgres');
        $dsn = sprintf('pgsql:host=%s;port=5432;dbname=laravel', $host);
        $pdo = new PDO($dsn, 'laravel', 'secret', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $this->assertSame(1, (int) $pdo->query('select 1')->fetchColumn());
    }
}
