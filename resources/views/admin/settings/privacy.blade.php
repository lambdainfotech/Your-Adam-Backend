@extends('admin.layouts.master')

@section('title', 'Privacy Policy Settings')
@section('page-title', 'Privacy Policy Settings')

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
                <a href="{{ route('admin.settings.privacy') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 bg-blue-50 text-blue-600">
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
                <input type="hidden" name="redirect" value="{{ route('admin.settings.privacy') }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Page Title</label>
                        <input type="text" name="settings[privacy_page_title]" value="{{ $settings['privacy_page_title'] ?? 'Privacy Policy' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                        <input type="text" name="settings[privacy_page_subtitle]" value="{{ $settings['privacy_page_subtitle'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                        <textarea name="settings[privacy_page_description]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['privacy_page_description'] ?? '' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hero Image URL</label>
                        <input type="text" name="settings[privacy_page_hero_image]" value="{{ $settings['privacy_page_hero_image'] ?? '' }}" placeholder="https://example.com/image.jpg or /storage/..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Leave empty to hide hero image</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Updated Date</label>
                        <input type="text" name="settings[privacy_page_last_updated]" value="{{ $settings['privacy_page_last_updated'] ?? now()->format('F d, Y') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-700">Show Last Updated</p>
                            <p class="text-sm text-gray-500">Display the last updated date on the page</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[privacy_page_show_last_updated]" value="0">
                            <input type="checkbox" name="settings[privacy_page_show_last_updated]" value="1" {{ ($settings['privacy_page_show_last_updated'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Page Info</button>
                </div>
            </form>
        </div>

        <!-- Privacy Sections -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Privacy Policy Sections</h2>
            <p class="text-sm text-gray-500 mb-4">Add, edit, or reorder the sections of your Privacy Policy page.</p>
            <form action="{{ route('admin.settings.update') }}" method="POST" id="sectionsForm">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.privacy') }}">
                <div id="sectionsContainer" class="space-y-4">
                    @forelse($privacyPageSections as $index => $section)
                    <div class="section-row border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-medium text-gray-400">Section {{ $index + 1 }}</span>
                            <button type="button" onclick="removeSection(this)" class="text-red-600 hover:bg-red-50 px-2 py-1 rounded text-sm">
                                <i class="fas fa-trash mr-1"></i>Remove
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Section Title</label>
                                <input type="text" name="section_titles[]" value="{{ $section['title'] ?? '' }}" placeholder="e.g., Information We Collect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Content</label>
                                <textarea name="section_contents[]" rows="6" placeholder="Write the section content here..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $section['content'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="section-row border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-medium text-gray-400">Section 1</span>
                            <button type="button" onclick="removeSection(this)" class="text-red-600 hover:bg-red-50 px-2 py-1 rounded text-sm">
                                <i class="fas fa-trash mr-1"></i>Remove
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Section Title</label>
                                <input type="text" name="section_titles[]" placeholder="e.g., Information We Collect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Content</label>
                                <textarea name="section_contents[]" rows="6" placeholder="Write the section content here..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>
                <input type="hidden" name="settings[privacy_page_sections]" id="sectionsJson">
                <button type="button" onclick="addSection()" class="mt-4 px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg border border-blue-200">
                    <i class="fas fa-plus mr-1"></i> Add Section
                </button>
                <div class="mt-4">
                    <button type="button" onclick="saveSections()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Sections</button>
                </div>
            </form>
        </div>

        <!-- Contact CTA Settings -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Contact CTA Settings</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.privacy') }}">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-700">Show Contact CTA</p>
                            <p class="text-sm text-gray-500">Display a call-to-action at the bottom of the page</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[privacy_page_show_contact_cta]" value="0">
                            <input type="checkbox" name="settings[privacy_page_show_contact_cta]" value="1" {{ ($settings['privacy_page_show_contact_cta'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CTA Text</label>
                        <input type="text" name="settings[privacy_page_contact_cta_text]" value="{{ $settings['privacy_page_contact_cta_text'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Text</label>
                        <input type="text" name="settings[privacy_page_contact_cta_button]" value="{{ $settings['privacy_page_contact_cta_button'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Link</label>
                        <input type="text" name="settings[privacy_page_contact_cta_link]" value="{{ $settings['privacy_page_contact_cta_link'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
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
                <input type="hidden" name="redirect" value="{{ route('admin.settings.privacy') }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                        <input type="text" name="settings[privacy_page_meta_title]" value="{{ $settings['privacy_page_meta_title'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                        <textarea name="settings[privacy_page_meta_description]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['privacy_page_meta_description'] ?? '' }}</textarea>
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
    function removeSection(button) {
        const row = button.closest('.section-row');
        const container = document.getElementById('sectionsContainer');
        if (container.children.length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input, textarea').forEach(input => input.value = '');
        }
        updateSectionNumbers();
    }

    function addSection() {
        const container = document.getElementById('sectionsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'section-row border border-gray-200 rounded-lg p-4';
        newRow.innerHTML = `
            <div class="flex justify-between items-center mb-3">
                <span class="text-xs font-medium text-gray-400 section-number">Section ${container.children.length + 1}</span>
                <button type="button" onclick="removeSection(this)" class="text-red-600 hover:bg-red-50 px-2 py-1 rounded text-sm">
                    <i class="fas fa-trash mr-1"></i>Remove
                </button>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Section Title</label>
                    <input type="text" name="section_titles[]" placeholder="e.g., Information We Collect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Content</label>
                    <textarea name="section_contents[]" rows="6" placeholder="Write the section content here..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
        `;
        container.appendChild(newRow);
    }

    function updateSectionNumbers() {
        document.querySelectorAll('#sectionsContainer .section-number').forEach((el, index) => {
            el.textContent = `Section ${index + 1}`;
        });
    }

    function saveSections() {
        const titles = document.querySelectorAll('input[name="section_titles[]"]');
        const contents = document.querySelectorAll('textarea[name="section_contents[]"]');
        const items = [];

        for (let i = 0; i < titles.length; i++) {
            if (titles[i].value && contents[i].value) {
                items.push({
                    title: titles[i].value,
                    content: contents[i].value,
                });
            }
        }

        document.getElementById('sectionsJson').value = JSON.stringify(items);
        document.getElementById('sectionsForm').submit();
    }
</script>
@endpush
