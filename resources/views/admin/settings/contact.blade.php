@extends('admin.layouts.master')

@section('title', 'Contact Page Settings')
@section('page-title', 'Contact Page Settings')

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
                <a href="{{ route('admin.settings.contact') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 bg-blue-50 text-blue-600">
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
                <input type="hidden" name="redirect" value="{{ route('admin.settings.contact') }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Page Title</label>
                        <input type="text" name="settings[contact_page_title]" value="{{ $settings['contact_page_title'] ?? 'Contact Us' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                        <input type="text" name="settings[contact_page_subtitle]" value="{{ $settings['contact_page_subtitle'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="settings[contact_page_description]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['contact_page_description'] ?? '' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hero Image URL</label>
                        <input type="text" name="settings[contact_page_hero_image]" value="{{ $settings['contact_page_hero_image'] ?? '' }}" placeholder="https://example.com/image.jpg or /storage/..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Leave empty to hide hero image</p>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Page Info</button>
                </div>
            </form>
        </div>

        <!-- Contact Form Settings -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Contact Form Settings</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.contact') }}">
                <div class="flex items-center gap-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="hidden" name="settings[contact_page_form_enabled]" value="0">
                        <input type="checkbox" name="settings[contact_page_form_enabled]" value="1" {{ ($settings['contact_page_form_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                        <span class="ml-3 text-sm font-medium text-gray-700">Enable Contact Form</span>
                    </label>
                </div>
                <p class="text-xs text-gray-500 mt-2">When enabled, visitors can submit messages through the contact form</p>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Form Settings</button>
                </div>
            </form>
        </div>

        <!-- Map Settings -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Map Settings</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.contact') }}">
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="hidden" name="settings[contact_page_show_map]" value="0">
                            <input type="checkbox" name="settings[contact_page_show_map]" value="1" {{ ($settings['contact_page_show_map'] ?? '1') == '1' ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                            <span class="ml-3 text-sm font-medium text-gray-700">Show Map on Contact Page</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Google Maps Embed URL</label>
                        <input type="text" name="settings[contact_page_map_embed_url]" value="{{ $settings['contact_page_map_embed_url'] ?? '' }}" placeholder="https://www.google.com/maps/embed?pb=..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Paste the embed URL from Google Maps (iframe src)</p>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Map Settings</button>
                </div>
            </form>
        </div>

        <!-- Office Locations -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Office Locations</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST" id="locationsForm">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.contact') }}">
                <div id="locationsContainer" class="space-y-4">
                    @forelse($contactPageLocations as $index => $location)
                    <div class="location-row border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Location Name</label>
                                <input type="text" name="location_names[]" value="{{ $location['name'] ?? '' }}" placeholder="e.g., Head Office" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                                <input type="text" name="location_phones[]" value="{{ $location['phone'] ?? '' }}" placeholder="+880 1234-567890" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                                <input type="email" name="location_emails[]" value="{{ $location['email'] ?? '' }}" placeholder="support@youradam.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Business Hours</label>
                                <input type="text" name="location_hours[]" value="{{ $location['hours'] ?? '' }}" placeholder="Sat - Thu: 9:00 AM - 8:00 PM" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                                <textarea name="location_addresses[]" rows="2" placeholder="Full address" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $location['address'] ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="mt-3 text-right">
                            <button type="button" onclick="removeLocation(this)" class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                                <i class="fas fa-trash mr-1"></i>Remove
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="location-row border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Location Name</label>
                                <input type="text" name="location_names[]" placeholder="e.g., Head Office" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                                <input type="text" name="location_phones[]" placeholder="+880 1234-567890" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                                <input type="email" name="location_emails[]" placeholder="support@youradam.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Business Hours</label>
                                <input type="text" name="location_hours[]" placeholder="Sat - Thu: 9:00 AM - 8:00 PM" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                                <textarea name="location_addresses[]" rows="2" placeholder="Full address" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                        </div>
                        <div class="mt-3 text-right">
                            <button type="button" onclick="removeLocation(this)" class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                                <i class="fas fa-trash mr-1"></i>Remove
                            </button>
                        </div>
                    </div>
                    @endforelse
                </div>
                <input type="hidden" name="settings[contact_page_locations]" id="locationsJson">
                <button type="button" onclick="addLocation()" class="mt-4 px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg border border-blue-200">
                    <i class="fas fa-plus mr-1"></i> Add Location
                </button>
                <div class="mt-4">
                    <button type="button" onclick="saveLocations()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Locations</button>
                </div>
            </form>
        </div>

        <!-- FAQs -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Frequently Asked Questions</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST" id="faqsForm">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.contact') }}">
                <div id="faqsContainer" class="space-y-4">
                    @forelse($contactPageFaqs as $index => $faq)
                    <div class="faq-row border border-gray-200 rounded-lg p-4">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Question</label>
                                <input type="text" name="faq_questions[]" value="{{ $faq['question'] ?? '' }}" placeholder="Enter question" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Answer</label>
                                <textarea name="faq_answers[]" rows="2" placeholder="Enter answer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $faq['answer'] ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="mt-3 text-right">
                            <button type="button" onclick="removeFaq(this)" class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                                <i class="fas fa-trash mr-1"></i>Remove
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="faq-row border border-gray-200 rounded-lg p-4">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Question</label>
                                <input type="text" name="faq_questions[]" placeholder="Enter question" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Answer</label>
                                <textarea name="faq_answers[]" rows="2" placeholder="Enter answer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                        </div>
                        <div class="mt-3 text-right">
                            <button type="button" onclick="removeFaq(this)" class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                                <i class="fas fa-trash mr-1"></i>Remove
                            </button>
                        </div>
                    </div>
                    @endforelse
                </div>
                <input type="hidden" name="settings[contact_page_faqs]" id="faqsJson">
                <button type="button" onclick="addFaq()" class="mt-4 px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg border border-blue-200">
                    <i class="fas fa-plus mr-1"></i> Add FAQ
                </button>
                <div class="mt-4">
                    <button type="button" onclick="saveFaqs()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save FAQs</button>
                </div>
            </form>
        </div>

        <!-- SEO Settings -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">SEO Settings</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.contact') }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                        <input type="text" name="settings[contact_page_meta_title]" value="{{ $settings['contact_page_meta_title'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                        <textarea name="settings[contact_page_meta_description]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['contact_page_meta_description'] ?? '' }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save SEO Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function removeLocation(button) {
        const row = button.closest('.location-row');
        const container = document.getElementById('locationsContainer');
        if (container.children.length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input, textarea').forEach(input => input.value = '');
        }
    }

    function addLocation() {
        const container = document.getElementById('locationsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'location-row border border-gray-200 rounded-lg p-4';
        newRow.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Location Name</label>
                    <input type="text" name="location_names[]" placeholder="e.g., Head Office" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                    <input type="text" name="location_phones[]" placeholder="+880 1234-567890" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                    <input type="email" name="location_emails[]" placeholder="support@youradam.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Business Hours</label>
                    <input type="text" name="location_hours[]" placeholder="Sat - Thu: 9:00 AM - 8:00 PM" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                    <textarea name="location_addresses[]" rows="2" placeholder="Full address" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
            <div class="mt-3 text-right">
                <button type="button" onclick="removeLocation(this)" class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                    <i class="fas fa-trash mr-1"></i>Remove
                </button>
            </div>
        `;
        container.appendChild(newRow);
    }

    function saveLocations() {
        const names = document.querySelectorAll('input[name="location_names[]"]');
        const phones = document.querySelectorAll('input[name="location_phones[]"]');
        const emails = document.querySelectorAll('input[name="location_emails[]"]');
        const hours = document.querySelectorAll('input[name="location_hours[]"]');
        const addresses = document.querySelectorAll('textarea[name="location_addresses[]"]');
        const locations = [];

        for (let i = 0; i < names.length; i++) {
            if (names[i].value && addresses[i].value) {
                locations.push({
                    name: names[i].value,
                    phone: phones[i].value,
                    email: emails[i].value,
                    hours: hours[i].value,
                    address: addresses[i].value,
                });
            }
        }

        document.getElementById('locationsJson').value = JSON.stringify(locations);
        document.getElementById('locationsForm').submit();
    }

    function removeFaq(button) {
        const row = button.closest('.faq-row');
        const container = document.getElementById('faqsContainer');
        if (container.children.length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input, textarea').forEach(input => input.value = '');
        }
    }

    function addFaq() {
        const container = document.getElementById('faqsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'faq-row border border-gray-200 rounded-lg p-4';
        newRow.innerHTML = `
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Question</label>
                    <input type="text" name="faq_questions[]" placeholder="Enter question" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Answer</label>
                    <textarea name="faq_answers[]" rows="2" placeholder="Enter answer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
            <div class="mt-3 text-right">
                <button type="button" onclick="removeFaq(this)" class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                    <i class="fas fa-trash mr-1"></i>Remove
                </button>
            </div>
        `;
        container.appendChild(newRow);
    }

    function saveFaqs() {
        const questions = document.querySelectorAll('input[name="faq_questions[]"]');
        const answers = document.querySelectorAll('textarea[name="faq_answers[]"]');
        const faqs = [];

        for (let i = 0; i < questions.length; i++) {
            if (questions[i].value && answers[i].value) {
                faqs.push({
                    question: questions[i].value,
                    answer: answers[i].value,
                });
            }
        }

        document.getElementById('faqsJson').value = JSON.stringify(faqs);
        document.getElementById('faqsForm').submit();
    }
</script>
@endpush
