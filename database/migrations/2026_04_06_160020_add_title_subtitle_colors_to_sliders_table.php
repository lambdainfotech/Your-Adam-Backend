<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->string('title_color')->default('#FFFFFF')->after('subtitle');
            $table->string('subtitle_color')->default('#FFFFFF')->after('title_color');
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn(['title_color', 'subtitle_color']);
        });
    }
};
