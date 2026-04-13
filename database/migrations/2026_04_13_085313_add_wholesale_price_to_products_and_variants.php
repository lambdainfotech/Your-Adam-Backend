<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'wholesale_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('wholesale_price', 12, 2)->nullable()->after('base_price');
            });
        }

        if (!Schema::hasColumn('variants', 'wholesale_price')) {
            Schema::table('variants', function (Blueprint $table) {
                $table->decimal('wholesale_price', 12, 2)->nullable()->after('price');
            });
        }

        if (!Schema::hasColumn('pos_orders', 'is_wholesale')) {
            Schema::table('pos_orders', function (Blueprint $table) {
                $table->boolean('is_wholesale')->default(false)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'wholesale_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('wholesale_price');
            });
        }

        if (Schema::hasColumn('variants', 'wholesale_price')) {
            Schema::table('variants', function (Blueprint $table) {
                $table->dropColumn('wholesale_price');
            });
        }

        if (Schema::hasColumn('pos_orders', 'is_wholesale')) {
            Schema::table('pos_orders', function (Blueprint $table) {
                $table->dropColumn('is_wholesale');
            });
        }
    }
};
