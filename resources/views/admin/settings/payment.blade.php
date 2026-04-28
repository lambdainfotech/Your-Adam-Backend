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
                <a href="{{ route('admin.settings.sms') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 {{ request()->routeIs('admin.settings.sms') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}"><i class="fas fa-sms w-6"></i> SMS Configuration</a>
                <a href="{{ route('admin.settings.payment') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 bg-blue-50 text-blue-600"><i class="fas fa-credit-card w-6"></i> Payment Settings</a>
                <a href="{{ route('admin.settings.shipping') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-shipping-fast w-6"></i> Shipping Settings</a>
                <a href="{{ route('admin.settings.seo') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-search w-6"></i> SEO Settings</a>
                <a href="{{ route('admin.settings.social') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-share-alt w-6"></i> Social Media</a>
                <a href="{{ route('admin.settings.footer') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-shoe-prints w-6"></i> Footer Settings</a>
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
                    <label class="flex items-center p-4 border rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="hidden" name="settings[payment_method_cod]" value="0">
                        <input type="checkbox" id="payment_cod" name="settings[payment_method_cod]" value="1" {{ ($settings['payment_method_cod'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                        <div class="ml-3">
                            <span class="font-medium">Cash on Delivery (COD)</span>
                            <p class="text-sm text-gray-500">Pay when you receive</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="hidden" name="settings[payment_method_aamarpay]" value="0">
                        <input type="checkbox" id="payment_aamarpay" name="settings[payment_method_aamarpay]" value="1" {{ ($settings['payment_method_aamarpay'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                        <div class="ml-3">
                            <span class="font-medium">aamarPay</span>
                            <p class="text-sm text-gray-500">Bangladesh payment gateway (bKash, Nagad, Cards)</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="hidden" name="settings[payment_method_sslcommerz]" value="0">
                        <input type="checkbox" id="payment_sslcommerz" name="settings[payment_method_sslcommerz]" value="1" {{ ($settings['payment_method_sslcommerz'] ?? '0') == '1' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                        <div class="ml-3">
                            <span class="font-medium">SSLCommerz</span>
                            <p class="text-sm text-gray-500">Online payment gateway</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="hidden" name="settings[payment_method_stripe]" value="0">
                        <input type="checkbox" id="payment_stripe" name="settings[payment_method_stripe]" value="1" {{ ($settings['payment_method_stripe'] ?? '0') == '1' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                        <div class="ml-3">
                            <span class="font-medium">Stripe</span>
                            <p class="text-sm text-gray-500">Credit/Debit card payments</p>
                        </div>
                    </label>
                </div>

                <!-- aamarPay Configuration -->
                <div id="aamarpay_config" class="border-t pt-6 mb-6 {{ ($settings['payment_method_aamarpay'] ?? '1') == '1' ? '' : 'hidden' }}">
                    <h3 class="font-medium mb-4 flex items-center">
                        <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-2">
                            <i class="fas fa-money-bill-wave text-green-600"></i>
                        </span>
                        aamarPay Configuration
                    </h3>
                    <div class="bg-green-50 rounded-lg p-4 mb-4">
                        <p class="text-sm text-green-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            aamarPay supports bKash, Nagad, Rocket, Visa, Mastercard, and more. 
                            Get your credentials from <a href="https://portal.aamarpay.com" target="_blank" class="underline">portal.aamarpay.com</a>
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Store ID</label>
                            <input type="text" name="settings[aamarpay_store_id]" value="{{ $settings['aamarpay_store_id'] ?? '' }}" placeholder="aamarpaytest" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Signature Key</label>
                            <input type="password" name="settings[aamarpay_signature_key]" value="{{ $settings['aamarpay_signature_key'] ?? '' }}" placeholder="dbb74894e82415a2f7ff0ec3a97e4183" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mode</label>
                        <select name="settings[aamarpay_mode]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="sandbox" {{ ($settings['aamarpay_mode'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                            <option value="live" {{ ($settings['aamarpay_mode'] ?? 'sandbox') == 'live' ? 'selected' : '' }}>Live (Production)</option>
                        </select>
                    </div>
                </div>

                <!-- SSLCommerz Configuration -->
                <div id="sslcommerz_config" class="border-t pt-6 mb-6 {{ ($settings['payment_method_sslcommerz'] ?? '0') == '1' ? '' : 'hidden' }}">
                    <h3 class="font-medium mb-4">SSLCommerz Configuration</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Store ID</label>
                        <input type="text" name="settings[sslcommerz_store_id]" value="{{ $settings['sslcommerz_store_id'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Store Password</label>
                        <input type="password" name="settings[sslcommerz_store_password]" value="{{ $settings['sslcommerz_store_password'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="hidden" name="settings[sslcommerz_sandbox]" value="0">
                            <input type="checkbox" name="settings[sslcommerz_sandbox]" value="1" {{ ($settings['sslcommerz_sandbox'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                            <span class="ml-2">Sandbox Mode</span>
                        </label>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get checkbox elements
        const aamarpayCheckbox = document.getElementById('payment_aamarpay');
        const sslcommerzCheckbox = document.getElementById('payment_sslcommerz');
        const stripeCheckbox = document.getElementById('payment_stripe');
        
        // Get config sections
        const aamarpayConfig = document.getElementById('aamarpay_config');
        const sslcommerzConfig = document.getElementById('sslcommerz_config');
        
        // Toggle aamarPay config
        if (aamarpayCheckbox && aamarpayConfig) {
            aamarpayCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    aamarpayConfig.classList.remove('hidden');
                } else {
                    aamarpayConfig.classList.add('hidden');
                }
            });
        }
        
        // Toggle SSLCommerz config
        if (sslcommerzCheckbox && sslcommerzConfig) {
            sslcommerzCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    sslcommerzConfig.classList.remove('hidden');
                } else {
                    sslcommerzConfig.classList.add('hidden');
                }
            });
        }
    });
</script>
@endpush
