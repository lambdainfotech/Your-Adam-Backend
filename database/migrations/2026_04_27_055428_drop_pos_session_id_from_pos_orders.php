<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop foreign key first (MySQL requires this before dropping the column)
        try {
            DB::statement('ALTER TABLE pos_orders DROP FOREIGN KEY pos_orders_pos_session_id_foreign');
        } catch (\Exception $e) {
            // FK may already be dropped or named differently
        }

        Schema::table('pos_orders', function (Blueprint $table) {
            if (Schema::hasColumn('pos_orders', 'pos_session_id')) {
                $table->dropColumn('pos_session_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('pos_session_id')->nullable()->after('id');
        });
    }
};
