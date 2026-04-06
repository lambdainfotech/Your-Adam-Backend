<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add discount columns to products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'discount_type')) {
                $table->enum('discount_type', ['percentage', 'flat'])->nullable()->after('compare_price');
            }
            if (!Schema::hasColumn('products', 'discount_value')) {
                $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');
            }
        });

        // Add discount columns to variants table
        Schema::table('variants', function (Blueprint $table) {
            if (!Schema::hasColumn('variants', 'discount_type')) {
                $table->enum('discount_type', ['percentage', 'flat'])->nullable()->after('compare_price');
            }
            if (!Schema::hasColumn('variants', 'discount_value')) {
                $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');
            }
        });
    }

    public function down(): void
    {
        // Remove discount columns from products table
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'discount_type')) {
                $table->dropColumn('discount_type');
            }
            if (Schema::hasColumn('products', 'discount_value')) {
                $table->dropColumn('discount_value');
            }
        });

        // Remove discount columns from variants table
        Schema::table('variants', function (Blueprint $table) {
            if (Schema::hasColumn('variants', 'discount_type')) {
                $table->dropColumn('discount_type');
            }
            if (Schema::hasColumn('variants', 'discount_value')) {
                $table->dropColumn('discount_value');
            }
        });
    }
};
