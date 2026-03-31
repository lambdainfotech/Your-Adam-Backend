<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add stock status to variants
        Schema::table('variants', function (Blueprint $table) {
            if (!Schema::hasColumn('variants', 'stock_status')) {
                $table->enum('stock_status', ['in_stock', 'out_of_stock', 'on_backorder'])->default('in_stock')->after('stock_quantity');
            }
            if (!Schema::hasColumn('variants', 'manage_stock')) {
                $table->boolean('manage_stock')->default(true)->after('stock_status');
            }
            if (Schema::hasColumn('variants', 'low_stock_alert') && !Schema::hasColumn('variants', 'low_stock_threshold')) {
                $table->renameColumn('low_stock_alert', 'low_stock_threshold');
            } elseif (!Schema::hasColumn('variants', 'low_stock_threshold') && !Schema::hasColumn('variants', 'low_stock_alert')) {
                $table->integer('low_stock_threshold')->default(5)->after('manage_stock');
            }
        });

        // Enhance inventory movements
        Schema::table('inventory_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_movements', 'reason')) {
                $table->string('reason')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('inventory_movements', 'reference_id')) {
                $table->foreignId('reference_id')->nullable()->after('reason');
            }
            if (!Schema::hasColumn('inventory_movements', 'reference_type')) {
                $table->string('reference_type')->nullable()->after('reference_id');
            }
            if (!Schema::hasColumn('inventory_movements', 'stock_before')) {
                $table->integer('stock_before')->default(0)->after('reference_type');
            }
            if (!Schema::hasColumn('inventory_movements', 'stock_after')) {
                $table->integer('stock_after')->default(0)->after('stock_before');
            }
            if (!Schema::hasColumn('inventory_movements', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('stock_after')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            if (Schema::hasColumn('variants', 'stock_status')) {
                $table->dropColumn('stock_status');
            }
            if (Schema::hasColumn('variants', 'manage_stock')) {
                $table->dropColumn('manage_stock');
            }
            if (Schema::hasColumn('variants', 'low_stock_threshold')) {
                $table->renameColumn('low_stock_threshold', 'low_stock_alert');
            }
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $columns = ['reason', 'reference_id', 'reference_type', 'stock_before', 'stock_after', 'created_by'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('inventory_movements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
