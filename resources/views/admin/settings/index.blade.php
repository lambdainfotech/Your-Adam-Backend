@extends('admin.layouts.master')

@section('title', 'Settings')
@section('page-title', 'System Settings')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Settings Navigation -->
    <div class="md:col-span-1">
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200 font-semibold">Settings</div>
            <nav class="p-2">
                <a href="{{ route('admin.settings.general') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 {{ request()->routeIs('admin.settings.general') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                    <i class="fas fa-cog w-6"></i> General
                </a>
                <a href="{{ route('admin.settings.store') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 {{ request()->routeIs('admin.settings.store') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                    <i class="fas fa-store w-6"></i> Store Information
                </a>
                <a href="{{ route('admin.settings.email') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 {{ request()->routeIs('admin.settings.email') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                    <i class="fas fa-envelope w-6"></i> Email Configuration
                </a>
                <a href="{{ route('admin.settings.sms') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 {{ request()->routeIs('admin.settings.sms') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                    <i class="fas fa-sms w-6"></i> SMS Configuration
                </a>
                <a href="{{ route('admin.settings.payment') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 {{ request()->routeIs('admin.settings.payment') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                    <i class="fas fa-credit-card w-6"></i> Payment Settings
                </a>
                <a href="{{ route('admin.settings.shipping') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 {{ request()->routeIs('admin.settings.shipping') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                    <i class="fas fa-shipping-fast w-6"></i> Shipping Settings
                </a>
                <a href="{{ route('admin.settings.seo') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 {{ request()->routeIs('admin.settings.seo') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                    <i class="fas fa-search w-6"></i> SEO Settings
                </a>
                <a href="{{ route('admin.settings.social') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 {{ request()->routeIs('admin.settings.social') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                    <i class="fas fa-share-alt w-6"></i> Social Media
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
    <div class="md:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">General Settings</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">App Name</label>
                        <input type="text" name="settings[app_name]" value="{{ $settings['app_name'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                        <input type="text" name="settings[timezone]" value="{{ $settings['timezone'] ?? 'Asia/Dhaka' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date Format</label>
                        <input type="text" name="settings[date_format]" value="{{ $settings['date_format'] ?? 'd M, Y' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Time Format</label>
                        <input type="text" name="settings[time_format]" value="{{ $settings['time_format'] ?? 'h:i A' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
