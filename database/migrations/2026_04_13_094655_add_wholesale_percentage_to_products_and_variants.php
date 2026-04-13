<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'wholesale_percentage')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('wholesale_percentage', 5, 2)->nullable()->after('wholesale_price');
            });
        }

        if (!Schema::hasColumn('variants', 'wholesale_percentage')) {
            Schema::table('variants', function (Blueprint $table) {
                $table->decimal('wholesale_percentage', 5, 2)->nullable()->after('wholesale_price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'wholesale_percentage')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('wholesale_percentage');
            });
        }

        if (Schema::hasColumn('variants', 'wholesale_percentage')) {
            Schema::table('variants', function (Blueprint $table) {
                $table->dropColumn('wholesale_percentage');
            });
        }
    }
};
