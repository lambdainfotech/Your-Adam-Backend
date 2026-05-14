<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::updateOrCreate(
            ['key' => 'aamarpay_success_url'],
            [
                'value' => '',
                'group' => 'payment',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'aamarpay_fail_url'],
            [
                'value' => '',
                'group' => 'payment',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'aamarpay_cancel_url'],
            [
                'value' => '',
                'group' => 'payment',
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'aamarpay_success_url',
            'aamarpay_fail_url',
            'aamarpay_cancel_url',
        ])->delete();
    }
};
