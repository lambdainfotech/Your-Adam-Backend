<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            'contact_page_title' => [
                'value' => 'Contact Us',
                'group' => 'contact',
            ],
            'contact_page_subtitle' => [
                'value' => "We'd love to hear from you",
                'group' => 'contact',
            ],
            'contact_page_description' => [
                'value' => 'Have a question, feedback, or just want to say hello? Reach out to us and our team will get back to you as soon as possible.',
                'group' => 'contact',
            ],
            'contact_page_hero_image' => [
                'value' => '',
                'group' => 'contact',
            ],
            'contact_page_show_map' => [
                'value' => '1',
                'group' => 'contact',
            ],
            'contact_page_map_embed_url' => [
                'value' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.902808274265!2d90.3742213153631!3d23.750933494588!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjPCsDQ1JzAzLjQiTiA5MMKwMjInMzUuMiJF!5e0!3m2!1sen!2sbd!4v1620000000000!5m2!1sen!2sbd',
                'group' => 'contact',
            ],
            'contact_page_locations' => [
                'value' => json_encode([
                    [
                        'name' => 'Head Office',
                        'address' => 'House 12, Road 5, Dhanmondi, Dhaka 1205',
                        'phone' => '+880 1234-567890',
                        'email' => 'support@youradam.com',
                        'hours' => 'Sat - Thu: 9:00 AM - 8:00 PM',
                    ],
                ]),
                'group' => 'contact',
            ],
            'contact_page_faqs' => [
                'value' => json_encode([
                    [
                        'question' => 'What are your business hours?',
                        'answer' => 'We are open Saturday to Thursday from 9:00 AM to 8:00 PM. Our online store is open 24/7.',
                    ],
                    [
                        'question' => 'How can I track my order?',
                        'answer' => 'You can track your order using the tracking number sent to your email or by visiting the Track Order page.',
                    ],
                    [
                        'question' => 'Do you offer wholesale or bulk orders?',
                        'answer' => 'Yes, we offer special pricing for bulk and corporate orders. Please contact us for more details.',
                    ],
                ]),
                'group' => 'contact',
            ],
            'contact_page_form_enabled' => [
                'value' => '1',
                'group' => 'contact',
            ],
            'contact_page_meta_title' => [
                'value' => 'Contact Us | Your Adam',
                'group' => 'contact',
            ],
            'contact_page_meta_description' => [
                'value' => 'Get in touch with Your Adam. We are here to help you with any questions or concerns.',
                'group' => 'contact',
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
            'contact_page_title',
            'contact_page_subtitle',
            'contact_page_description',
            'contact_page_hero_image',
            'contact_page_show_map',
            'contact_page_map_embed_url',
            'contact_page_locations',
            'contact_page_faqs',
            'contact_page_form_enabled',
            'contact_page_meta_title',
            'contact_page_meta_description',
        ];

        Setting::whereIn('key', $keys)->delete();
    }
};
