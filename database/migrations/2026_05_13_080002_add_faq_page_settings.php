<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            'faq_page_title' => [
                'value' => 'Frequently Asked Questions',
                'group' => 'faq',
            ],
            'faq_page_subtitle' => [
                'value' => 'Find answers to common questions about our products, shipping, returns, and more.',
                'group' => 'faq',
            ],
            'faq_page_description' => [
                'value' => 'Can\'t find what you\'re looking for? Feel free to contact our support team for personalized assistance.',
                'group' => 'faq',
            ],
            'faq_page_hero_image' => [
                'value' => '',
                'group' => 'faq',
            ],
            'faq_page_show_search' => [
                'value' => '1',
                'group' => 'faq',
            ],
            'faq_page_show_contact_cta' => [
                'value' => '1',
                'group' => 'faq',
            ],
            'faq_page_contact_cta_text' => [
                'value' => 'Still have questions? Contact our support team.',
                'group' => 'faq',
            ],
            'faq_page_contact_cta_button' => [
                'value' => 'Contact Us',
                'group' => 'faq',
            ],
            'faq_page_contact_cta_link' => [
                'value' => '/contact',
                'group' => 'faq',
            ],
            'faq_page_meta_title' => [
                'value' => 'FAQs | Your Adam',
                'group' => 'faq',
            ],
            'faq_page_meta_description' => [
                'value' => 'Find answers to frequently asked questions about Your Adam products, orders, shipping, returns, and more.',
                'group' => 'faq',
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
            'faq_page_title',
            'faq_page_subtitle',
            'faq_page_description',
            'faq_page_hero_image',
            'faq_page_show_search',
            'faq_page_show_contact_cta',
            'faq_page_contact_cta_text',
            'faq_page_contact_cta_button',
            'faq_page_contact_cta_link',
            'faq_page_meta_title',
            'faq_page_meta_description',
        ];

        Setting::whereIn('key', $keys)->delete();
    }
};
