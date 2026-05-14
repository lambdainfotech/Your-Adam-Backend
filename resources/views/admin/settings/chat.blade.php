@extends('admin.layouts.master')

@section('title', 'Chat Settings')
@section('page-title', 'Chat Settings')

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
                <a href="{{ route('admin.settings.faq') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-question-circle w-6"></i> FAQ Page
                </a>
                <a href="{{ route('admin.settings.about') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-info-circle w-6"></i> About Page
                </a>
                <a href="{{ route('admin.settings.terms') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-file-contract w-6"></i> Terms & Conditions
                </a>
                <a href="{{ route('admin.settings.privacy') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-shield-alt w-6"></i> Privacy Policy
                </a>
                <a href="{{ route('admin.settings.chat') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 bg-blue-50 text-blue-600">
                    <i class="fas fa-comments w-6"></i> Chat Settings
                </a>
            </nav>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="md:col-span-2 space-y-6">
        <!-- WhatsApp Settings -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-green-100 text-green-600">
                    <i class="fab fa-whatsapp text-xl"></i>
                </div>
                <h2 class="text-lg font-semibold">WhatsApp Chat</h2>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.chat') }}">

                <div class="mb-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="hidden" name="settings[chat_whatsapp_enabled]" value="0">
                        <input type="checkbox" name="settings[chat_whatsapp_enabled]" value="1" {{ ($settings['chat_whatsapp_enabled'] ?? '0') == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-700">Enable WhatsApp Chat</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Number</label>
                        <input type="text" name="settings[chat_whatsapp_number]" value="{{ $settings['chat_whatsapp_number'] ?? '+8801234567890' }}" placeholder="+8801234567890" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Include country code (e.g., +8801XXXXXXXXX)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Label</label>
                        <input type="text" name="settings[chat_whatsapp_label]" value="{{ $settings['chat_whatsapp_label'] ?? 'Chat on WhatsApp' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Message</label>
                    <textarea name="settings[chat_whatsapp_message]" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['chat_whatsapp_message'] ?? 'Hello! I have a question about your products.' }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">This message will be pre-filled when user opens WhatsApp.</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Button Position</label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input type="radio" name="settings[chat_whatsapp_position]" value="right" {{ ($settings['chat_whatsapp_position'] ?? 'right') == 'right' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Bottom Right</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="settings[chat_whatsapp_position]" value="left" {{ ($settings['chat_whatsapp_position'] ?? 'right') == 'left' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Bottom Left</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save WhatsApp Settings</button>
                </div>
            </form>
        </div>

        <!-- Messenger Settings -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-blue-100 text-blue-600">
                    <i class="fab fa-facebook-messenger text-xl"></i>
                </div>
                <h2 class="text-lg font-semibold">Messenger Chat</h2>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.chat') }}">

                <div class="mb-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="hidden" name="settings[chat_messenger_enabled]" value="0">
                        <input type="checkbox" name="settings[chat_messenger_enabled]" value="1" {{ ($settings['chat_messenger_enabled'] ?? '0') == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-700">Enable Messenger Chat</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Facebook Page ID</label>
                        <input type="text" name="settings[chat_messenger_page_id]" value="{{ $settings['chat_messenger_page_id'] ?? '' }}" placeholder="123456789012345" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Find this in your Facebook Page Settings.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Facebook App ID (Optional)</label>
                        <input type="text" name="settings[chat_messenger_app_id]" value="{{ $settings['chat_messenger_app_id'] ?? '' }}" placeholder="123456789012345" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Label</label>
                        <input type="text" name="settings[chat_messenger_label]" value="{{ $settings['chat_messenger_label'] ?? 'Chat on Messenger' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Position</label>
                        <div class="flex gap-4 mt-2">
                            <label class="flex items-center">
                                <input type="radio" name="settings[chat_messenger_position]" value="left" {{ ($settings['chat_messenger_position'] ?? 'left') == 'left' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Bottom Left</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="settings[chat_messenger_position]" value="right" {{ ($settings['chat_messenger_position'] ?? 'left') == 'right' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Bottom Right</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Greeting Message</label>
                    <textarea name="settings[chat_messenger_greeting]" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['chat_messenger_greeting'] ?? 'Hi! How can we help you today?' }}</textarea>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Messenger Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
