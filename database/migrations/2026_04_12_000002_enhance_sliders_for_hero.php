<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->text('description')->nullable()->after('subtitle');
            $table->string('mobile_image')->nullable()->after('banner_image');
            $table->string('secondary_button_text')->nullable()->after('button_url');
            $table->string('secondary_button_url')->nullable()->after('secondary_button_text');
            $table->string('secondary_button_text_color', 7)->default('#FFFFFF')->after('secondary_button_url');
            $table->string('secondary_button_bg_color', 7)->default('#4B5563')->after('secondary_button_text_color');
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'mobile_image',
                'secondary_button_text',
                'secondary_button_url',
                'secondary_button_text_color',
                'secondary_button_bg_color',
            ]);
        });
    }
};
