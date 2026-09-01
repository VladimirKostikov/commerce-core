<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('sku', 64);
            $table->unsignedInteger('amount');
            $table->string('currency', 8);
            $table->string('status', 32);
            $table->string('delivery_code', 32)->nullable();
            $table->string('delivery_request_id', 64)->nullable()->unique();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
