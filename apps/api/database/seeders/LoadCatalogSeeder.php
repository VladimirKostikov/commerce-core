<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoadCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $count = 3000;

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement(
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
            DB::statement('ANALYZE products');

            return;
        }

        $now = now();

        for ($offset = 1; $offset <= $count; $offset += 500) {
            $rows = [];
            $end = min($offset + 499, $count);

            for ($i = $offset; $i <= $end; $i++) {
                $rows[] = [
                    'sku' => sprintf('LOAD-%05d', $i),
                    'name' => 'Load SKU '.$i,
                    'type' => 'key',
                    'price' => 500,
                    'currency' => 'RUB',
                    'image' => null,
                    'is_active' => true,
                    'available_keys_count' => 1 + ($i % 25),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('products')->insertOrIgnore($rows);
        }
    }
}
