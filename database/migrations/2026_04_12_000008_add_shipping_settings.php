<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            'shipping_base_rate' => ['value' => '100', 'group' => 'shipping'],
            'shipping_per_item_rate' => ['value' => '50', 'group' => 'shipping'],
            'shipping_express_rate' => ['value' => '200', 'group' => 'shipping'],
            'shipping_cod_rate' => ['value' => '50', 'group' => 'shipping'],
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
            'shipping_base_rate',
            'shipping_per_item_rate',
            'shipping_express_rate',
            'shipping_cod_rate',
        ];

        Setting::whereIn('key', $keys)->delete();
    }
};
