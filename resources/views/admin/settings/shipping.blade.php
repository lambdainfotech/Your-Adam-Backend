@extends('admin.layouts.master')

@section('title', 'Shipping Settings')
@section('page-title', 'Shipping Settings')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-1">
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200 font-semibold">Settings</div>
            <nav class="p-2">
                <a href="{{ route('admin.settings.general') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-cog w-6"></i> General</a>
                <a href="{{ route('admin.settings.store') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-store w-6"></i> Store Information</a>
                <a href="{{ route('admin.settings.email') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-envelope w-6"></i> Email Configuration</a>
                <a href="{{ route('admin.settings.payment') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-credit-card w-6"></i> Payment Settings</a>
                <a href="{{ route('admin.settings.shipping') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 bg-blue-50 text-blue-600"><i class="fas fa-shipping-fast w-6"></i> Shipping Settings</a>
                <a href="{{ route('admin.settings.seo') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-search w-6"></i> SEO Settings</a>
                <a href="{{ route('admin.settings.social') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-share-alt w-6"></i> Social Media</a>
                <a href="{{ route('admin.settings.footer') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-shoe-prints w-6"></i> Footer Settings</a>
            </nav>
        </div>
    </div>

    <div class="md:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Shipping Settings</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Default Shipping Cost (৳)</label>
                        <input type="number" name="settings[default_shipping_cost]" value="{{ $settings['default_shipping_cost'] ?? '60' }}" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Free Shipping Threshold (৳)</label>
                        <input type="number" name="settings[free_shipping_threshold]" value="{{ $settings['free_shipping_threshold'] ?? '1000' }}" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Orders above this amount get free shipping</p>
                    </div>
                </div>
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="settings[enable_courier_tracking]" value="1" {{ ($settings['enable_courier_tracking'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                        <span class="ml-2">Enable Courier Tracking</span>
                    </label>
                </div>
                <div class="flex items-center gap-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
