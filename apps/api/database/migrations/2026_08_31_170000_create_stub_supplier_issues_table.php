<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stub_supplier_issues', function (Blueprint $table) {
            $table->id();
            $table->string('supplier', 8);
            $table->string('request_id', 64);
            $table->string('sku', 64);
            $table->string('order_id', 40);
            $table->string('code', 32);
            $table->timestamps();

            $table->unique(['supplier', 'request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stub_supplier_issues');
    }
};
