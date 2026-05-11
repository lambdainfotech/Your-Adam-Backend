<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sync status with is_active for all products
        DB::table('products')
            ->where('is_active', false)
            ->where('status', '!=', 0)
            ->update(['status' => 0]);

        DB::table('products')
            ->where('is_active', true)
            ->where('status', '!=', 1)
            ->update(['status' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed for data sync
    }
};
