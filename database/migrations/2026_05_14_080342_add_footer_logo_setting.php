<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::updateOrCreate(
            ['key' => 'site_footer_logo_url'],
            [
                'value' => '',
                'group' => 'site',
            ]
        );
    }

    public function down(): void
    {
        Setting::where('key', 'site_footer_logo_url')->delete();
    }
};
