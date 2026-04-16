<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            if (!Schema::hasColumn('variants', 'sale_price')) {
                $table->decimal('sale_price', 10, 2)->nullable()->after('discount_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            if (Schema::hasColumn('variants', 'sale_price')) {
                $table->dropColumn('sale_price');
            }
        });
    }
};
