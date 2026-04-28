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
                <a href="{{ route('admin.settings.sms') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 {{ request()->routeIs('admin.settings.sms') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}"><i class="fas fa-sms w-6"></i> SMS Configuration</a>
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
            <form action="{{ route('admin.settings.update') }}" method="POST" id="shippingSettingsForm">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.shipping') }}">

                <!-- Free Shipping Toggle -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <label class="flex items-center cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="settings[free_shipping]" id="freeShippingToggle" value="1"
                                {{ ($settings['free_shipping'] ?? '0') == '1' ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </div>
                        <span class="ml-3 text-sm font-medium text-gray-900">Free Shipping</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-14">Enable free shipping for all orders. Other shipping options will be disabled.</p>
                </div>

                <!-- Shipping Cost Fields -->
                <div id="shippingCostFields" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Inside Dhaka (৳)</label>
                        <input type="number" name="settings[shipping_cost_inside_dhaka]" id="shippingInsideDhaka"
                            value="{{ $settings['shipping_cost_inside_dhaka'] ?? '60' }}" step="0.01" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Outside Dhaka (৳)</label>
                        <input type="number" name="settings[shipping_cost_outside_dhaka]" id="shippingOutsideDhaka"
                            value="{{ $settings['shipping_cost_outside_dhaka'] ?? '120' }}" step="0.01" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed transition-colors">
                    </div>
                </div>

                <!-- Free Shipping Threshold -->
                <div id="freeShippingThresholdWrapper" class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Free Shipping Threshold (৳)</label>
                    <input type="number" name="settings[free_shipping_threshold]" id="freeShippingThreshold"
                        value="{{ $settings['free_shipping_threshold'] ?? '1000' }}" step="0.01" min="0"
                        class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed transition-colors">
                    <p class="text-sm text-gray-500 mt-1">Orders above this amount get free shipping</p>
                </div>

                <!-- Enable Courier Tracking -->
                <div class="mb-6">
                    <label class="flex items-center cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="settings[enable_courier_tracking]" value="1"
                                {{ ($settings['enable_courier_tracking'] ?? '1') == '1' ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </div>
                        <span class="ml-3 text-sm font-medium text-gray-900">Enable Courier Tracking</span>
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

@push('scripts')
<script>
    (function () {
        const freeShippingToggle = document.getElementById('freeShippingToggle');
        const shippingInsideDhaka = document.getElementById('shippingInsideDhaka');
        const shippingOutsideDhaka = document.getElementById('shippingOutsideDhaka');
        const freeShippingThreshold = document.getElementById('freeShippingThreshold');

        function toggleShippingFields() {
            const isFreeShipping = freeShippingToggle.checked;
            shippingInsideDhaka.disabled = isFreeShipping;
            shippingOutsideDhaka.disabled = isFreeShipping;
            freeShippingThreshold.disabled = isFreeShipping;
        }

        freeShippingToggle.addEventListener('change', toggleShippingFields);
        toggleShippingFields();
    })();
</script>
@endpush
