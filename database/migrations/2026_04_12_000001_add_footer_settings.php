<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Default support links
        $supportLinks = json_encode([
            ['name' => 'Contact Us', 'href' => '/contact'],
            ['name' => 'FAQs', 'href' => '/faqs'],
            ['name' => 'Shipping Info', 'href' => '/shipping'],
            ['name' => 'Returns', 'href' => '/returns'],
        ]);

        // Default company links
        $companyLinks = json_encode([
            ['name' => 'About Us', 'href' => '/about'],
            ['name' => 'Careers', 'href' => '/careers'],
            ['name' => 'Terms', 'href' => '/terms'],
            ['name' => 'Privacy', 'href' => '/privacy'],
        ]);

        // Default trust badges
        $trustBadges = json_encode([
            ['icon' => 'Truck', 'text' => 'Free shipping over ৳2,000'],
            ['icon' => 'Shield', 'text' => 'Secure payment'],
            ['icon' => 'RotateCcw', 'text' => '7-day easy returns'],
            ['icon' => 'CreditCard', 'text' => 'COD available'],
        ]);

        // Default payment methods
        $paymentMethods = json_encode(['Visa', 'Mastercard', 'bKash', 'Nagad']);

        // Create settings
        $settings = [
            'footer_brand_description' => [
                'value' => 'Premium fashion meets custom expression. Design your own or choose from our curated collections.',
                'group' => 'footer',
            ],
            'footer_copyright' => [
                'value' => '',
                'group' => 'footer',
            ],
            'footer_support_links' => [
                'value' => $supportLinks,
                'group' => 'footer',
            ],
            'footer_company_links' => [
                'value' => $companyLinks,
                'group' => 'footer',
            ],
            'footer_trust_badges' => [
                'value' => $trustBadges,
                'group' => 'footer',
            ],
            'footer_payment_methods' => [
                'value' => $paymentMethods,
                'group' => 'footer',
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
        // Remove footer settings
        $keys = [
            'footer_brand_description',
            'footer_copyright',
            'footer_support_links',
            'footer_company_links',
            'footer_trust_badges',
            'footer_payment_methods',
        ];

        Setting::whereIn('key', $keys)->delete();
    }
};
