<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'wholesale_discount_type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->enum('wholesale_discount_type', ['percentage', 'flat'])->nullable()->after('wholesale_price');
                $table->decimal('wholesale_discount_value', 12, 2)->nullable()->after('wholesale_discount_type');
            });
        }

        if (!Schema::hasColumn('variants', 'wholesale_discount_type')) {
            Schema::table('variants', function (Blueprint $table) {
                $table->enum('wholesale_discount_type', ['percentage', 'flat'])->nullable()->after('wholesale_price');
                $table->decimal('wholesale_discount_value', 12, 2)->nullable()->after('wholesale_discount_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'wholesale_discount_type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn(['wholesale_discount_type', 'wholesale_discount_value']);
            });
        }

        if (Schema::hasColumn('variants', 'wholesale_discount_type')) {
            Schema::table('variants', function (Blueprint $table) {
                $table->dropColumn(['wholesale_discount_type', 'wholesale_discount_value']);
            });
        }
    }
};
