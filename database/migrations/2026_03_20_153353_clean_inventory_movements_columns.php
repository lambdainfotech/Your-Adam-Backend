<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            // Drop old columns if they exist
            if (Schema::hasColumn('inventory_movements', 'previous_stock')) {
                $table->dropColumn('previous_stock');
            }
            if (Schema::hasColumn('inventory_movements', 'new_stock')) {
                $table->dropColumn('new_stock');
            }
            
            // Ensure stock_before and stock_after exist
            if (!Schema::hasColumn('inventory_movements', 'stock_before')) {
                $table->integer('stock_before')->default(0)->after('reference_type');
            }
            if (!Schema::hasColumn('inventory_movements', 'stock_after')) {
                $table->integer('stock_after')->default(0)->after('stock_before');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->integer('previous_stock')->nullable()->after('quantity');
            $table->integer('new_stock')->nullable()->after('previous_stock');
        });
    }
};
