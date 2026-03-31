<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Product type: simple or variable
            if (!Schema::hasColumn('products', 'product_type')) {
                $table->enum('product_type', ['simple', 'variable'])->default('simple')->after('category_id');
            }
            
            // Sale pricing with schedule
            if (!Schema::hasColumn('products', 'sale_price')) {
                $table->decimal('sale_price', 10, 2)->nullable()->after('compare_price');
            }
            if (!Schema::hasColumn('products', 'sale_start_date')) {
                $table->timestamp('sale_start_date')->nullable()->after('sale_price');
            }
            if (!Schema::hasColumn('products', 'sale_end_date')) {
                $table->timestamp('sale_end_date')->nullable()->after('sale_start_date');
            }
            
            // Stock management at product level (for simple products)
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku', 50)->nullable()->after('barcode');
            }
            if (!Schema::hasColumn('products', 'manage_stock')) {
                $table->boolean('manage_stock')->default(true)->after('sku');
            }
            if (!Schema::hasColumn('products', 'stock_quantity')) {
                $table->integer('stock_quantity')->default(0)->after('manage_stock');
            }
            if (!Schema::hasColumn('products', 'stock_status')) {
                $table->enum('stock_status', ['in_stock', 'out_of_stock', 'on_backorder'])->default('in_stock')->after('stock_quantity');
            }
            if (!Schema::hasColumn('products', 'low_stock_threshold')) {
                $table->integer('low_stock_threshold')->default(10)->after('stock_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = [
                'product_type',
                'sale_price',
                'sale_start_date',
                'sale_end_date',
                'sku',
                'manage_stock',
                'stock_quantity',
                'stock_status',
                'low_stock_threshold',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
