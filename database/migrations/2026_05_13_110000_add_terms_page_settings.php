<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            'terms_page_title' => [
                'value' => 'Terms & Conditions',
                'group' => 'terms',
            ],
            'terms_page_subtitle' => [
                'value' => 'Please read these terms carefully before using our website',
                'group' => 'terms',
            ],
            'terms_page_description' => [
                'value' => 'These Terms and Conditions govern your use of our website and services. By accessing or using our platform, you agree to be bound by these terms.',
                'group' => 'terms',
            ],
            'terms_page_hero_image' => [
                'value' => '',
                'group' => 'terms',
            ],
            'terms_page_last_updated' => [
                'value' => now()->format('F d, Y'),
                'group' => 'terms',
            ],
            'terms_page_sections' => [
                'value' => json_encode([
                    [
                        'title' => 'Introduction',
                        'content' => 'Welcome to Your Adam. These Terms and Conditions govern your use of our website located at youradam.com and form a binding contractual agreement between you, the user of the Website, and us, Your Adam.\n\nBy accessing and using the Website, you acknowledge that you have read, understood, and agree to be bound by these Terms. If you do not agree with any part of these terms, you must not use our Website or services.',
                    ],
                    [
                        'title' => 'Use of Our Website',
                        'content' => 'You agree to use our Website only for lawful purposes and in a way that does not infringe the rights of, restrict, or inhibit anyone else\'s use and enjoyment of the Website.\n\nProhibited behavior includes harassing or causing distress or inconvenience to any other user, transmitting obscene or offensive content, or disrupting the normal flow of dialogue within our Website.\n\nYou must not misuse our Website by knowingly introducing viruses, trojans, worms, logic bombs, or other material that is malicious or technologically harmful.',
                    ],
                    [
                        'title' => 'Orders & Payments',
                        'content' => 'All orders placed through our Website are subject to acceptance and availability. We reserve the right to refuse any order without giving reasons.\n\nPrices for products are subject to change without notice. We reserve the right at any time to modify or discontinue any product without notice.\n\nYou agree to provide current, complete, and accurate purchase and account information for all purchases made at our store. You agree to promptly update your account and other information so that we can complete your transactions and contact you as needed.',
                    ],
                    [
                        'title' => 'Shipping & Delivery',
                        'content' => 'We aim to deliver products within the estimated timeframe specified at the time of order. However, delivery dates are not guaranteed and may be affected by factors beyond our control.\n\nRisk of loss and title for items purchased from our Website pass to you upon delivery of the items to the carrier.\n\nShipping costs are calculated at checkout based on your delivery location and order value. Free shipping may be available on orders exceeding the specified threshold.',
                    ],
                    [
                        'title' => 'Returns & Refunds',
                        'content' => 'We accept returns within 7 days of delivery, provided the items are unused, unwashed, and in original packaging with all tags attached.\n\nTo initiate a return, please contact our customer support team with your order number and reason for return.\n\nRefunds will be processed to your original payment method within 5-7 business days after we receive and inspect the returned items. Shipping costs are non-refundable unless the item was defective or we made an error.',
                    ],
                    [
                        'title' => 'Privacy & Data Protection',
                        'content' => 'Your privacy is important to us. Please review our Privacy Policy to understand how we collect, use, and protect your personal information.\n\nBy using our Website, you consent to the collection and use of your information as described in our Privacy Policy.',
                    ],
                    [
                        'title' => 'Intellectual Property',
                        'content' => 'All content on this Website, including but not limited to text, graphics, logos, images, audio clips, digital downloads, data compilations, and software, is the property of Your Adam or its content suppliers and is protected by Bangladeshi and international copyright laws.\n\nYou may not reproduce, distribute, modify, create derivative works of, publicly display, publicly perform, republish, download, store, or transmit any of the material on our Website without our prior written consent.',
                    ],
                    [
                        'title' => 'Limitation of Liability',
                        'content' => 'To the fullest extent permitted by applicable law, Your Adam shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses.\n\nOur total liability to you for all claims arising from or relating to these Terms or your use of the Website shall not exceed the amount you paid to us during the six (6) months preceding the event giving rise to liability.',
                    ],
                    [
                        'title' => 'Governing Law',
                        'content' => 'These Terms shall be governed by and construed in accordance with the laws of Bangladesh. Any disputes arising under or in connection with these Terms shall be subject to the exclusive jurisdiction of the courts of Dhaka, Bangladesh.',
                    ],
                    [
                        'title' => 'Changes to Terms',
                        'content' => 'We reserve the right, at our sole discretion, to update, change, or replace any part of these Terms by posting updates and changes to our Website. It is your responsibility to check our Website periodically for changes.\n\nYour continued use of or access to our Website following the posting of any changes constitutes acceptance of those changes.',
                    ],
                    [
                        'title' => 'Contact Us',
                        'content' => 'If you have any questions about these Terms, please contact us at:\n\nEmail: support@youradam.com\nPhone: +880 1234-567890\nAddress: House 12, Road 5, Dhanmondi, Dhaka 1205, Bangladesh',
                    ],
                ]),
                'group' => 'terms',
            ],
            'terms_page_show_last_updated' => [
                'value' => '1',
                'group' => 'terms',
            ],
            'terms_page_show_contact_cta' => [
                'value' => '1',
                'group' => 'terms',
            ],
            'terms_page_contact_cta_text' => [
                'value' => 'Have questions about our terms? Contact our support team.',
                'group' => 'terms',
            ],
            'terms_page_contact_cta_button' => [
                'value' => 'Contact Us',
                'group' => 'terms',
            ],
            'terms_page_contact_cta_link' => [
                'value' => '/contact',
                'group' => 'terms',
            ],
            'terms_page_meta_title' => [
                'value' => 'Terms & Conditions | Your Adam',
                'group' => 'terms',
            ],
            'terms_page_meta_description' => [
                'value' => 'Read our Terms and Conditions to understand the rules and guidelines for using our website and services.',
                'group' => 'terms',
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
            'terms_page_title',
            'terms_page_subtitle',
            'terms_page_description',
            'terms_page_hero_image',
            'terms_page_last_updated',
            'terms_page_sections',
            'terms_page_show_last_updated',
            'terms_page_show_contact_cta',
            'terms_page_contact_cta_text',
            'terms_page_contact_cta_button',
            'terms_page_contact_cta_link',
            'terms_page_meta_title',
            'terms_page_meta_description',
        ];

        Setting::whereIn('key', $keys)->delete();
    }
};
