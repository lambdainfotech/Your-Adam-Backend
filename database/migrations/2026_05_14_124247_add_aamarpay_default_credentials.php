<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            'aamarpay_store_id' => [
                'value' => 'aamarpaytest',
                'group' => 'payment',
            ],
            'aamarpay_signature_key' => [
                'value' => 'dbb74894e82415a2f7ff0ec3a97e4183',
                'group' => 'payment',
            ],
            'aamarpay_mode' => [
                'value' => 'sandbox',
                'group' => 'payment',
            ],
        ];

        foreach ($settings as $key => $data) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $data['value'],
                    'group' => $data['group'],
                ]
            );
        }
    }

    public function down(): void
    {
        $keys = [
            'aamarpay_store_id',
            'aamarpay_signature_key',
            'aamarpay_mode',
        ];

        Setting::whereIn('key', $keys)->delete();
    }
};
