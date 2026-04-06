<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('size_charts', function (Blueprint $table) {
            $table->enum('size_type', ['european', 'asian'])->default('asian')->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('size_charts', function (Blueprint $table) {
            $table->dropColumn('size_type');
        });
    }
};
