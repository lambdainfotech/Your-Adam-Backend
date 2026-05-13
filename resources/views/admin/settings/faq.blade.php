@extends('admin.layouts.master')

@section('title', 'FAQ Page Settings')
@section('page-title', 'FAQ Page Settings')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Settings Navigation -->
    <div class="md:col-span-1">
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200 font-semibold">Settings</div>
            <nav class="p-2">
                <a href="{{ route('admin.settings.general') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-cog w-6"></i> General
                </a>
                <a href="{{ route('admin.settings.store') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-store w-6"></i> Store Information
                </a>
                <a href="{{ route('admin.settings.email') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-envelope w-6"></i> Email Configuration
                </a>
                <a href="{{ route('admin.settings.sms') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-sms w-6"></i> SMS Configuration
                </a>
                <a href="{{ route('admin.settings.payment') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-credit-card w-6"></i> Payment Settings
                </a>
                <a href="{{ route('admin.settings.shipping') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-shipping-fast w-6"></i> Shipping Settings
                </a>
                <a href="{{ route('admin.settings.seo') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-search w-6"></i> SEO Settings
                </a>
                <a href="{{ route('admin.settings.social') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-share-alt w-6"></i> Social Media
                </a>
                <a href="{{ route('admin.settings.footer') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-shoe-prints w-6"></i> Footer Settings
                </a>
                <a href="{{ route('admin.settings.contact') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-address-book w-6"></i> Contact Page
                </a>
                <a href="{{ route('admin.settings.faq') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 bg-blue-50 text-blue-600">
                    <i class="fas fa-question-circle w-6"></i> FAQ Page
                </a>
                <a href="{{ route('admin.settings.returns') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                </a>
                <a href="{{ route('admin.settings.about') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-info-circle w-6"></i> About Page
                </a>
            </nav>
            <div class="p-4 border-t border-gray-200">
                <form action="{{ route('admin.settings.clear-cache') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200">
                        <i class="fas fa-broom mr-2"></i>Clear Cache
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="md:col-span-2 space-y-6">
        <!-- Page Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Page Information</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.faq') }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Page Title</label>
                        <input type="text" name="settings[faq_page_title]" value="{{ $settings['faq_page_title'] ?? 'Frequently Asked Questions' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                        <input type="text" name="settings[faq_page_subtitle]" value="{{ $settings['faq_page_subtitle'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="settings[faq_page_description]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['faq_page_description'] ?? '' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hero Image URL</label>
                        <input type="text" name="settings[faq_page_hero_image]" value="{{ $settings['faq_page_hero_image'] ?? '' }}" placeholder="https://example.com/image.jpg or /storage/..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Leave empty to hide hero image</p>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Page Info</button>
                </div>
            </form>
        </div>

        <!-- Page Features -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Page Features</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.faq') }}">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-700">Search Bar</p>
                            <p class="text-sm text-gray-500">Allow visitors to search through FAQs</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[faq_page_show_search]" value="0">
                            <input type="checkbox" name="settings[faq_page_show_search]" value="1" {{ ($settings['faq_page_show_search'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-700">Contact CTA Section</p>
                            <p class="text-sm text-gray-500">Show a call-to-action to contact support</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[faq_page_show_contact_cta]" value="0">
                            <input type="checkbox" name="settings[faq_page_show_contact_cta]" value="1" {{ ($settings['faq_page_show_contact_cta'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Features</button>
                </div>
            </form>
        </div>

        <!-- Contact CTA Settings -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Contact CTA Settings</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.faq') }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CTA Text</label>
                        <input type="text" name="settings[faq_page_contact_cta_text]" value="{{ $settings['faq_page_contact_cta_text'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Text</label>
                        <input type="text" name="settings[faq_page_contact_cta_button]" value="{{ $settings['faq_page_contact_cta_button'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Link</label>
                        <input type="text" name="settings[faq_page_contact_cta_link]" value="{{ $settings['faq_page_contact_cta_link'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save CTA Settings</button>
                </div>
            </form>
        </div>

        <!-- SEO Settings -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">SEO Settings</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.faq') }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                        <input type="text" name="settings[faq_page_meta_title]" value="{{ $settings['faq_page_meta_title'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                        <textarea name="settings[faq_page_meta_description]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['faq_page_meta_description'] ?? '' }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save SEO Settings</button>
                </div>
            </form>
        </div>

        <!-- Quick Links -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Manage FAQs</h2>
            <p class="text-sm text-gray-500 mb-4">Add, edit, or organize your FAQ items and categories.</p>
            <div class="flex gap-3">
                <a href="{{ route('admin.faq-categories.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    <i class="fas fa-folder mr-2"></i>FAQ Categories
                </a>
                <a href="{{ route('admin.faqs.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-question-circle mr-2"></i>FAQ Items
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
