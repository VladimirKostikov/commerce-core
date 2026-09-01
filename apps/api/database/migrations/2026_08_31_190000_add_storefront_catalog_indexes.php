<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(
                'CREATE INDEX IF NOT EXISTS products_storefront_in_stock_idx ON products (available_keys_count DESC, sku) INCLUDE (name, price, currency, type, image) WHERE is_active AND available_keys_count > 0',
            );
            DB::statement(
                "CREATE INDEX IF NOT EXISTS product_keys_available_claim_idx ON product_keys (product_id, id) WHERE status = 'available' AND order_id IS NULL",
            );

            return;
        }

        DB::statement(
            'CREATE INDEX IF NOT EXISTS products_storefront_in_stock_idx ON products (available_keys_count, sku) WHERE is_active = 1 AND available_keys_count > 0',
        );
        DB::statement(
            "CREATE INDEX IF NOT EXISTS product_keys_available_claim_idx ON product_keys (product_id, id) WHERE status = 'available' AND order_id IS NULL",
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS products_storefront_in_stock_idx');
        DB::statement('DROP INDEX IF EXISTS product_keys_available_claim_idx');
    }
};
