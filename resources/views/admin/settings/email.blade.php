@extends('admin.layouts.master')

@section('title', 'Email Settings')
@section('page-title', 'Email Configuration')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-1">
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200 font-semibold">Settings</div>
            <nav class="p-2">
                <a href="{{ route('admin.settings.general') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-cog w-6"></i> General</a>
                <a href="{{ route('admin.settings.store') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-store w-6"></i> Store Information</a>
                <a href="{{ route('admin.settings.email') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 bg-blue-50 text-blue-600"><i class="fas fa-envelope w-6"></i> Email Configuration</a>
                <a href="{{ route('admin.settings.payment') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-credit-card w-6"></i> Payment Settings</a>
                <a href="{{ route('admin.settings.shipping') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-shipping-fast w-6"></i> Shipping Settings</a>
                <a href="{{ route('admin.settings.seo') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-search w-6"></i> SEO Settings</a>
                <a href="{{ route('admin.settings.social') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-share-alt w-6"></i> Social Media</a>
                <a href="{{ route('admin.settings.footer') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-shoe-prints w-6"></i> Footer Settings</a>
            </nav>
        </div>
    </div>

    <div class="md:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Email Configuration</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Address</label>
                        <input type="email" name="settings[mail_from_address]" value="{{ $settings['mail_from_address'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                        <input type="text" name="settings[mail_from_name]" value="{{ $settings['mail_from_name'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mail Driver</label>
                    <select name="settings[mail_driver]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="smtp" {{ ($settings['mail_driver'] ?? '') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                        <option value="sendmail" {{ ($settings['mail_driver'] ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                        <option value="log" {{ ($settings['mail_driver'] ?? '') == 'log' ? 'selected' : '' }}>Log (Testing)</option>
                    </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                        <input type="text" name="settings[mail_host]" value="{{ $settings['mail_host'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                        <input type="text" name="settings[mail_port]" value="{{ $settings['mail_port'] ?? '587' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Username</label>
                    <input type="text" name="settings[mail_username]" value="{{ $settings['mail_username'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Password</label>
                    <input type="password" name="settings[mail_password]" value="{{ $settings['mail_password'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Encryption</label>
                    <select name="settings[mail_encryption]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="tls" {{ ($settings['mail_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ ($settings['mail_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="" {{ ($settings['mail_encryption'] ?? '') == '' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
                <div class="flex items-center gap-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
