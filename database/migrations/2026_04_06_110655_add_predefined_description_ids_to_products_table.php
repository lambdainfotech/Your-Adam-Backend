<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'predefined_description_id')) {
                $table->foreignId('predefined_description_id')
                    ->nullable()
                    ->after('description')
                    ->constrained('predefined_descriptions')
                    ->onDelete('set null');
            }
            
            if (!Schema::hasColumn('products', 'predefined_short_description_id')) {
                $table->foreignId('predefined_short_description_id')
                    ->nullable()
                    ->after('short_description')
                    ->constrained('predefined_descriptions')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'predefined_description_id')) {
                $table->dropForeign(['predefined_description_id']);
                $table->dropColumn('predefined_description_id');
            }
            
            if (Schema::hasColumn('products', 'predefined_short_description_id')) {
                $table->dropForeign(['predefined_short_description_id']);
                $table->dropColumn('predefined_short_description_id');
            }
        });
    }
};
