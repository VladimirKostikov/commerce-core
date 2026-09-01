<?php

namespace Tests\Support;

use PDO;
use RuntimeException;

final class LivePostgres
{
    public static function pdo(): PDO
    {
        $host = InfrastructureHost::resolve('postgres');
        $dsn = sprintf('pgsql:host=%s;port=5432;dbname=laravel', $host);

        return new PDO($dsn, 'laravel', 'secret', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    public static function skuWithStock(): string
    {
        $sku = self::firstSkuWithStock();

        if ($sku !== '') {
            return $sku;
        }

        self::replenishAvailableKey();
        $sku = self::firstSkuWithStock();

        if ($sku === '') {
            throw new RuntimeException('Live catalog has no available keys');
        }

        return $sku;
    }

    private static function firstSkuWithStock(): string
    {
        $sku = self::pdo()
            ->query(
                "select p.sku
                 from products p
                 inner join product_keys k on k.product_id = p.id
                 where p.is_active = true
                   and k.status = 'available'
                   and k.order_id is null
                 order by p.available_keys_count desc, p.sku
                 limit 1",
            )
            ->fetchColumn();

        return is_string($sku) ? $sku : '';
    }

    private static function replenishAvailableKey(): void
    {
        $product = self::pdo()
            ->query('select id from products where is_active = true order by id limit 1')
            ->fetch(PDO::FETCH_ASSOC);

        if (! is_array($product) || ! isset($product['id'])) {
            return;
        }

        $code = 'LIVE-'.strtoupper(bin2hex(random_bytes(6)));
        $now = date('Y-m-d H:i:s');
        $insert = self::pdo()->prepare(
            'insert into product_keys (product_id, code, status, created_at, updated_at) values (:product_id, :code, :status, :created_at, :updated_at)',
        );
        $insert->execute([
            'product_id' => $product['id'],
            'code' => $code,
            'status' => 'available',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $bump = self::pdo()->prepare(
            'update products set available_keys_count = available_keys_count + 1 where id = :id',
        );
        $bump->execute(['id' => $product['id']]);
    }

    public static function countKeysForOrder(string $orderId): int
    {
        $statement = self::pdo()->prepare('select count(*) from product_keys where order_id = :order_id');
        $statement->execute(['order_id' => $orderId]);

        return (int) $statement->fetchColumn();
    }

    public static function countDeliveriesForOrder(string $orderId): int
    {
        $statement = self::pdo()->prepare('select count(*) from deliveries where order_id = :order_id');
        $statement->execute(['order_id' => $orderId]);

        return (int) $statement->fetchColumn();
    }

    public static function countEventsForOrder(string $orderId): int
    {
        $statement = self::pdo()->prepare('select count(*) from payment_events where order_id = :order_id');
        $statement->execute(['order_id' => $orderId]);

        return (int) $statement->fetchColumn();
    }

    public static function countStubIssues(string $requestId): int
    {
        $statement = self::pdo()->prepare('select count(*) from stub_supplier_issues where request_id = :request_id');
        $statement->execute(['request_id' => $requestId]);

        return (int) $statement->fetchColumn();
    }
}
