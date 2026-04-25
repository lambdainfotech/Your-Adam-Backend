<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasIndex('products', 'idx_sub_category_id')) {
                $table->index('sub_category_id', 'idx_sub_category_id');
            }
            if (!Schema::hasIndex('products', 'idx_sku')) {
                $table->index('sku', 'idx_sku');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasIndex('orders', 'idx_transaction_id')) {
                $table->index('transaction_id', 'idx_transaction_id');
            }
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            if (!Schema::hasIndex('inventory_movements', 'idx_product_id')) {
                $table->index('product_id', 'idx_product_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_sub_category_id');
            $table->dropIndex('idx_sku');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_transaction_id');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex('idx_product_id');
        });
    }
};
