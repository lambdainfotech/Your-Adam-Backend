@extends('admin.layouts.master')

@section('title', 'Footer Settings')
@section('page-title', 'Footer Settings')

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
                <a href="{{ route('admin.settings.footer') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 bg-blue-50 text-blue-600">
                    <i class="fas fa-shoe-prints w-6"></i> Footer Settings
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
        <!-- Brand Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Brand Information</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Footer Description</label>
                        <textarea name="settings[footer_brand_description]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['footer_brand_description'] ?? 'Premium fashion meets custom expression. Design your own or choose from our curated collections.' }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Shown in the footer brand section</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Copyright Text</label>
                        <input type="text" name="settings[footer_copyright]" value="{{ $settings['footer_copyright'] ?? '' }}" placeholder="© 2025 Your Adam. All rights reserved." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Leave empty to auto-generate with current year</p>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Brand Info</button>
                </div>
            </form>
        </div>

        <!-- Support Links -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Support Links</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST" id="supportLinksForm">
                @csrf
                <div id="supportLinksContainer" class="space-y-3">
                    @forelse($footerSupportLinks as $index => $link)
                    <div class="flex gap-3 support-link-row">
                        <input type="text" name="support_names[]" value="{{ $link['name'] }}" placeholder="Link Name" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <input type="text" name="support_hrefs[]" value="{{ $link['href'] }}" placeholder="URL (e.g., /contact)" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <button type="button" onclick="removeRow(this)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @empty
                    <div class="flex gap-3 support-link-row">
                        <input type="text" name="support_names[]" placeholder="Link Name" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <input type="text" name="support_hrefs[]" placeholder="URL (e.g., /contact)" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <button type="button" onclick="removeRow(this)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @endforelse
                </div>
                <input type="hidden" name="settings[footer_support_links]" id="supportLinksJson">
                <button type="button" onclick="addSupportLink()" class="mt-3 px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg">
                    <i class="fas fa-plus mr-1"></i> Add Support Link
                </button>
                <div class="mt-4">
                    <button type="button" onclick="saveSupportLinks()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Support Links</button>
                </div>
            </form>
        </div>

        <!-- Company Links -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Company Links</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST" id="companyLinksForm">
                @csrf
                <div id="companyLinksContainer" class="space-y-3">
                    @forelse($footerCompanyLinks as $index => $link)
                    <div class="flex gap-3 company-link-row">
                        <input type="text" name="company_names[]" value="{{ $link['name'] }}" placeholder="Link Name" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <input type="text" name="company_hrefs[]" value="{{ $link['href'] }}" placeholder="URL (e.g., /about)" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <button type="button" onclick="removeRow(this)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @empty
                    <div class="flex gap-3 company-link-row">
                        <input type="text" name="company_names[]" placeholder="Link Name" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <input type="text" name="company_hrefs[]" placeholder="URL (e.g., /about)" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <button type="button" onclick="removeRow(this)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @endforelse
                </div>
                <input type="hidden" name="settings[footer_company_links]" id="companyLinksJson">
                <button type="button" onclick="addCompanyLink()" class="mt-3 px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg">
                    <i class="fas fa-plus mr-1"></i> Add Company Link
                </button>
                <div class="mt-4">
                    <button type="button" onclick="saveCompanyLinks()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Company Links</button>
                </div>
            </form>
        </div>

        <!-- Trust Badges -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Trust Badges</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST" id="trustBadgesForm">
                @csrf
                <div id="trustBadgesContainer" class="space-y-3">
                    @forelse($footerTrustBadges as $index => $badge)
                    <div class="flex gap-3 trust-badge-row">
                        <select name="badge_icons[]" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="Truck" {{ $badge['icon'] == 'Truck' ? 'selected' : '' }}>🚛 Truck (Shipping)</option>
                            <option value="Shield" {{ $badge['icon'] == 'Shield' ? 'selected' : '' }}>🛡️ Shield (Security)</option>
                            <option value="RotateCcw" {{ $badge['icon'] == 'RotateCcw' ? 'selected' : '' }}>↩️ RotateCcw (Returns)</option>
                            <option value="CreditCard" {{ $badge['icon'] == 'CreditCard' ? 'selected' : '' }}>💳 CreditCard (Payment)</option>
                            <option value="Headphones" {{ $badge['icon'] == 'Headphones' ? 'selected' : '' }}>🎧 Headphones (Support)</option>
                            <option value="Award" {{ $badge['icon'] == 'Award' ? 'selected' : '' }}>🏆 Award (Quality)</option>
                        </select>
                        <input type="text" name="badge_texts[]" value="{{ $badge['text'] }}" placeholder="Badge Text" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <button type="button" onclick="removeRow(this)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @empty
                    <div class="flex gap-3 trust-badge-row">
                        <select name="badge_icons[]" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="Truck">🚛 Truck (Shipping)</option>
                            <option value="Shield">🛡️ Shield (Security)</option>
                            <option value="RotateCcw">↩️ RotateCcw (Returns)</option>
                            <option value="CreditCard">💳 CreditCard (Payment)</option>
                            <option value="Headphones">🎧 Headphones (Support)</option>
                            <option value="Award">🏆 Award (Quality)</option>
                        </select>
                        <input type="text" name="badge_texts[]" placeholder="Badge Text" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <button type="button" onclick="removeRow(this)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @endforelse
                </div>
                <input type="hidden" name="settings[footer_trust_badges]" id="trustBadgesJson">
                <button type="button" onclick="addTrustBadge()" class="mt-3 px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg">
                    <i class="fas fa-plus mr-1"></i> Add Trust Badge
                </button>
                <div class="mt-4">
                    <button type="button" onclick="saveTrustBadges()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Trust Badges</button>
                </div>
            </form>
        </div>

        <!-- Payment Methods -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Payment Methods</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @php
                        $paymentMethods = ['Visa', 'Mastercard', 'bKash', 'Nagad', 'Rocket', 'PayPal', 'Stripe', 'Cash'];
                        $selectedMethods = $footerPaymentMethods ?? ['Visa', 'Mastercard', 'bKash', 'Nagad'];
                    @endphp
                    @foreach($paymentMethods as $method)
                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="settings[footer_payment_methods][]" value="{{ $method }}" {{ in_array($method, $selectedMethods) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm">{{ $method }}</span>
                    </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-2">Select payment methods to display in footer</p>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Payment Methods</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function removeRow(button) {
        const row = button.closest('.support-link-row, .company-link-row, .trust-badge-row');
        if (row) {
            // Check if it's the last row
            const container = row.parentElement;
            if (container.children.length > 1) {
                row.remove();
            } else {
                // Clear inputs instead of removing
                row.querySelectorAll('input').forEach(input => input.value = '');
            }
        }
    }

    function addSupportLink() {
        const container = document.getElementById('supportLinksContainer');
        const newRow = document.createElement('div');
        newRow.className = 'flex gap-3 support-link-row';
        newRow.innerHTML = `
            <input type="text" name="support_names[]" placeholder="Link Name" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <input type="text" name="support_hrefs[]" placeholder="URL (e.g., /contact)" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <button type="button" onclick="removeRow(this)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(newRow);
    }

    function addCompanyLink() {
        const container = document.getElementById('companyLinksContainer');
        const newRow = document.createElement('div');
        newRow.className = 'flex gap-3 company-link-row';
        newRow.innerHTML = `
            <input type="text" name="company_names[]" placeholder="Link Name" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <input type="text" name="company_hrefs[]" placeholder="URL (e.g., /about)" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <button type="button" onclick="removeRow(this)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(newRow);
    }

    function addTrustBadge() {
        const container = document.getElementById('trustBadgesContainer');
        const newRow = document.createElement('div');
        newRow.className = 'flex gap-3 trust-badge-row';
        newRow.innerHTML = `
            <select name="badge_icons[]" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="Truck">🚛 Truck (Shipping)</option>
                <option value="Shield">🛡️ Shield (Security)</option>
                <option value="RotateCcw">↩️ RotateCcw (Returns)</option>
                <option value="CreditCard">💳 CreditCard (Payment)</option>
                <option value="Headphones">🎧 Headphones (Support)</option>
                <option value="Award">🏆 Award (Quality)</option>
            </select>
            <input type="text" name="badge_texts[]" placeholder="Badge Text" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <button type="button" onclick="removeRow(this)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(newRow);
    }

    function saveSupportLinks() {
        const names = document.querySelectorAll('input[name="support_names[]"]');
        const hrefs = document.querySelectorAll('input[name="support_hrefs[]"]');
        const links = [];
        
        for (let i = 0; i < names.length; i++) {
            if (names[i].value && hrefs[i].value) {
                links.push({ name: names[i].value, href: hrefs[i].value });
            }
        }
        
        document.getElementById('supportLinksJson').value = JSON.stringify(links);
        document.getElementById('supportLinksForm').submit();
    }

    function saveCompanyLinks() {
        const names = document.querySelectorAll('input[name="company_names[]"]');
        const hrefs = document.querySelectorAll('input[name="company_hrefs[]"]');
        const links = [];
        
        for (let i = 0; i < names.length; i++) {
            if (names[i].value && hrefs[i].value) {
                links.push({ name: names[i].value, href: hrefs[i].value });
            }
        }
        
        document.getElementById('companyLinksJson').value = JSON.stringify(links);
        document.getElementById('companyLinksForm').submit();
    }

    function saveTrustBadges() {
        const icons = document.querySelectorAll('select[name="badge_icons[]"]');
        const texts = document.querySelectorAll('input[name="badge_texts[]"]');
        const badges = [];
        
        for (let i = 0; i < icons.length; i++) {
            if (icons[i].value && texts[i].value) {
                badges.push({ icon: icons[i].value, text: texts[i].value });
            }
        }
        
        document.getElementById('trustBadgesJson').value = JSON.stringify(badges);
        document.getElementById('trustBadgesForm').submit();
    }
</script>
@endpush
