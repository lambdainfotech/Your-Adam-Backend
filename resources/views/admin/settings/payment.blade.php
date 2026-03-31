@extends('admin.layouts.master')

@section('title', 'Payment Settings')
@section('page-title', 'Payment Settings')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-1">
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200 font-semibold">Settings</div>
            <nav class="p-2">
                <a href="{{ route('admin.settings.general') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-cog w-6"></i> General</a>
                <a href="{{ route('admin.settings.store') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-store w-6"></i> Store Information</a>
                <a href="{{ route('admin.settings.email') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-envelope w-6"></i> Email Configuration</a>
                <a href="{{ route('admin.settings.payment') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 bg-blue-50 text-blue-600"><i class="fas fa-credit-card w-6"></i> Payment Settings</a>
                <a href="{{ route('admin.settings.shipping') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-shipping-fast w-6"></i> Shipping Settings</a>
                <a href="{{ route('admin.settings.seo') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-search w-6"></i> SEO Settings</a>
            </nav>
        </div>
    </div>

    <div class="md:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Payment Settings</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                
                <h3 class="font-medium mb-4">Payment Methods</h3>
                <div class="space-y-4 mb-6">
                    <label class="flex items-center p-4 border rounded-lg">
                        <input type="checkbox" name="settings[payment_method_cod]" value="1" {{ ($settings['payment_method_cod'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                        <div class="ml-3">
                            <span class="font-medium">Cash on Delivery (COD)</span>
                            <p class="text-sm text-gray-500">Pay when you receive</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border rounded-lg">
                        <input type="checkbox" name="settings[payment_method_sslcommerz]" value="1" {{ ($settings['payment_method_sslcommerz'] ?? '0') == '1' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                        <div class="ml-3">
                            <span class="font-medium">SSLCommerz</span>
                            <p class="text-sm text-gray-500">Online payment gateway</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border rounded-lg">
                        <input type="checkbox" name="settings[payment_method_stripe]" value="1" {{ ($settings['payment_method_stripe'] ?? '0') == '1' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                        <div class="ml-3">
                            <span class="font-medium">Stripe</span>
                            <p class="text-sm text-gray-500">Credit/Debit card payments</p>
                        </div>
                    </label>
                </div>

                <h3 class="font-medium mb-4">SSLCommerz Configuration</h3>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Store ID</label>
                    <input type="text" name="settings[sslcommerz_store_id]" value="{{ $settings['sslcommerz_store_id'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Store Password</label>
                    <input type="password" name="settings[sslcommerz_store_password]" value="{{ $settings['sslcommerz_store_password'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="settings[sslcommerz_sandbox]" value="1" {{ ($settings['sslcommerz_sandbox'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                        <span class="ml-2">Sandbox Mode</span>
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
