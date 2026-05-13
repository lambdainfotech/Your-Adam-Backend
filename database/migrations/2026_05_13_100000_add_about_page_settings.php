<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            'about_page_title' => [
                'value' => 'About Us',
                'group' => 'about',
            ],
            'about_page_subtitle' => [
                'value' => 'Premium fashion meets custom expression',
                'group' => 'about',
            ],
            'about_page_description' => [
                'value' => 'We are a Bangladesh-based fashion brand dedicated to bringing you premium quality apparel and the freedom to create your own designs.',
                'group' => 'about',
            ],
            'about_page_hero_image' => [
                'value' => '',
                'group' => 'about',
            ],
            'about_page_story_title' => [
                'value' => 'Our Story',
                'group' => 'about',
            ],
            'about_page_story_content' => [
                'value' => "Founded in 2020, Your Adam started with a simple mission: to make premium fashion accessible to everyone while giving creative individuals the tools to express themselves. What began as a small print shop in Dhaka has grown into one of Bangladesh's most loved custom apparel brands.\n\nWe believe that what you wear is an extension of who you are. That's why we combine high-quality materials with cutting-edge printing technology to deliver products that not only look great but feel amazing to wear.",
                'group' => 'about',
            ],
            'about_page_mission_title' => [
                'value' => 'Our Mission',
                'group' => 'about',
            ],
            'about_page_mission_content' => [
                'value' => 'To empower individuals and businesses with premium, customizable fashion that inspires creativity and self-expression while maintaining the highest standards of quality and sustainability.',
                'group' => 'about',
            ],
            'about_page_vision_title' => [
                'value' => 'Our Vision',
                'group' => 'about',
            ],
            'about_page_vision_content' => [
                'value' => 'To become the leading custom fashion platform in South Asia, known for innovation, quality, and customer-centric design experiences.',
                'group' => 'about',
            ],
            'about_page_values' => [
                'value' => json_encode([
                    ['icon' => 'Gem', 'title' => 'Quality First', 'description' => 'We never compromise on the quality of our products. Every item goes through rigorous quality checks.'],
                    ['icon' => 'Palette', 'title' => 'Creative Freedom', 'description' => 'We believe everyone should have the tools to express their unique style and creativity.'],
                    ['icon' => 'Heart', 'title' => 'Customer Obsessed', 'description' => 'Our customers are at the heart of everything we do. Your satisfaction is our top priority.'],
                    ['icon' => 'Leaf', 'title' => 'Sustainability', 'description' => 'We are committed to reducing our environmental footprint through eco-friendly practices.'],
                ]),
                'group' => 'about',
            ],
            'about_page_stats' => [
                'value' => json_encode([
                    ['value' => '50K+', 'label' => 'Happy Customers'],
                    ['value' => '100+', 'label' => 'Products'],
                    ['value' => '4', 'label' => 'Years of Service'],
                    ['value' => '99%', 'label' => 'Satisfaction Rate'],
                ]),
                'group' => 'about',
            ],
            'about_page_show_team' => [
                'value' => '1',
                'group' => 'about',
            ],
            'about_page_show_milestones' => [
                'value' => '1',
                'group' => 'about',
            ],
            'about_page_milestones' => [
                'value' => json_encode([
                    ['year' => '2020', 'title' => 'Founded', 'description' => 'Your Adam was founded in Dhaka with a small team and a big dream.'],
                    ['year' => '2021', 'title' => 'First 10K Customers', 'description' => 'Reached our first major milestone of 10,000 happy customers.'],
                    ['year' => '2022', 'title' => 'Expanded Product Line', 'description' => 'Launched corporate merchandise and bulk order services.'],
                    ['year' => '2023', 'title' => 'Nationwide Shipping', 'description' => 'Expanded delivery coverage to all 64 districts of Bangladesh.'],
                    ['year' => '2024', 'title' => 'Custom Design Tool', 'description' => 'Launched our online design studio for real-time customization.'],
                ]),
                'group' => 'about',
            ],
            'about_page_cta_enabled' => [
                'value' => '1',
                'group' => 'about',
            ],
            'about_page_cta_title' => [
                'value' => 'Want to work with us?',
                'group' => 'about',
            ],
            'about_page_cta_text' => [
                'value' => 'We are always looking for passionate individuals to join our team. Check out our open positions or reach out for collaborations.',
                'group' => 'about',
            ],
            'about_page_cta_button' => [
                'value' => 'Get in Touch',
                'group' => 'about',
            ],
            'about_page_cta_link' => [
                'value' => '/contact',
                'group' => 'about',
            ],
            'about_page_meta_title' => [
                'value' => 'About Us | Your Adam',
                'group' => 'about',
            ],
            'about_page_meta_description' => [
                'value' => 'Learn about Your Adam - Bangladesh\'s leading custom fashion and premium apparel brand. Our story, mission, and values.',
                'group' => 'about',
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
            'about_page_title',
            'about_page_subtitle',
            'about_page_description',
            'about_page_hero_image',
            'about_page_story_title',
            'about_page_story_content',
            'about_page_mission_title',
            'about_page_mission_content',
            'about_page_vision_title',
            'about_page_vision_content',
            'about_page_values',
            'about_page_stats',
            'about_page_show_team',
            'about_page_show_milestones',
            'about_page_milestones',
            'about_page_cta_enabled',
            'about_page_cta_title',
            'about_page_cta_text',
            'about_page_cta_button',
            'about_page_cta_link',
            'about_page_meta_title',
            'about_page_meta_description',
        ];

        Setting::whereIn('key', $keys)->delete();
    }
};
