<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            if (Schema::hasColumn('order_status_history', 'created_by') && !Schema::hasColumn('order_status_history', 'changed_by')) {
                $table->renameColumn('created_by', 'changed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            if (Schema::hasColumn('order_status_history', 'changed_by') && !Schema::hasColumn('order_status_history', 'created_by')) {
                $table->renameColumn('changed_by', 'created_by');
            }
        });
    }
};
