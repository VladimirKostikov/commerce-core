<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (Schema::getForeignKeys('payment_events') as $foreignKey) {
            $columns = $foreignKey['columns'] ?? [];
            $name = $foreignKey['name'] ?? null;

            if ($name === null || ! in_array('order_id', $columns, true)) {
                continue;
            }

            Schema::table('payment_events', function (Blueprint $table) use ($name): void {
                $table->dropForeign($name);
            });
        }
    }

    public function down(): void
    {
        Schema::table('payment_events', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }
};
