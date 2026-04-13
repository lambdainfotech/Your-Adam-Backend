<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            'payment_method_cod' => ['value' => '1', 'group' => 'payment'],
            'payment_method_aamarpay' => ['value' => '1', 'group' => 'payment'],
            'payment_method_sslcommerz' => ['value' => '0', 'group' => 'payment'],
            'payment_method_stripe' => ['value' => '0', 'group' => 'payment'],
            'aamarpay_store_id' => ['value' => '', 'group' => 'payment'],
            'aamarpay_signature_key' => ['value' => '', 'group' => 'payment'],
            'aamarpay_mode' => ['value' => 'sandbox', 'group' => 'payment'],
            'sslcommerz_store_id' => ['value' => '', 'group' => 'payment'],
            'sslcommerz_store_password' => ['value' => '', 'group' => 'payment'],
            'sslcommerz_sandbox' => ['value' => '1', 'group' => 'payment'],
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
            'payment_method_cod',
            'payment_method_aamarpay',
            'payment_method_sslcommerz',
            'payment_method_stripe',
            'aamarpay_store_id',
            'aamarpay_signature_key',
            'aamarpay_mode',
            'sslcommerz_store_id',
            'sslcommerz_store_password',
            'sslcommerz_sandbox',
        ];

        Setting::whereIn('key', $keys)->delete();
    }
};
