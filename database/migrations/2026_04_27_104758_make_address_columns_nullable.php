<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('district', 100)->nullable()->change();
            $table->string('postal_code', 20)->nullable()->change();
            $table->string('address_line_2', 255)->nullable()->change();
            $table->string('landmark', 255)->nullable()->change();
            $table->string('country', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('district', 100)->nullable(false)->change();
            $table->string('postal_code', 20)->nullable(false)->change();
            $table->string('address_line_2', 255)->nullable(false)->change();
            $table->string('landmark', 255)->nullable(false)->change();
        });
    }
};
