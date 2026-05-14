<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            'chat_whatsapp_enabled' => [
                'value' => '1',
                'group' => 'chat',
            ],
            'chat_whatsapp_number' => [
                'value' => '+8801234567890',
                'group' => 'chat',
            ],
            'chat_whatsapp_message' => [
                'value' => 'Hello! I have a question about your products.',
                'group' => 'chat',
            ],
            'chat_whatsapp_position' => [
                'value' => 'right',
                'group' => 'chat',
            ],
            'chat_whatsapp_label' => [
                'value' => 'Chat on WhatsApp',
                'group' => 'chat',
            ],
            'chat_messenger_enabled' => [
                'value' => '1',
                'group' => 'chat',
            ],
            'chat_messenger_page_id' => [
                'value' => '',
                'group' => 'chat',
            ],
            'chat_messenger_app_id' => [
                'value' => '',
                'group' => 'chat',
            ],
            'chat_messenger_greeting' => [
                'value' => 'Hi! How can we help you today?',
                'group' => 'chat',
            ],
            'chat_messenger_position' => [
                'value' => 'left',
                'group' => 'chat',
            ],
            'chat_messenger_label' => [
                'value' => 'Chat on Messenger',
                'group' => 'chat',
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
            'chat_whatsapp_enabled',
            'chat_whatsapp_number',
            'chat_whatsapp_message',
            'chat_whatsapp_position',
            'chat_whatsapp_label',
            'chat_messenger_enabled',
            'chat_messenger_page_id',
            'chat_messenger_app_id',
            'chat_messenger_greeting',
            'chat_messenger_position',
            'chat_messenger_label',
        ];

        Setting::whereIn('key', $keys)->delete();
    }
};
