@extends('admin.layouts.master')

@section('title', 'About Page Settings')
@section('page-title', 'About Page Settings')

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
                <a href="{{ route('admin.settings.about') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 bg-blue-50 text-blue-600">
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
                <input type="hidden" name="redirect" value="{{ route('admin.settings.about') }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Page Title</label>
                        <input type="text" name="settings[about_page_title]" value="{{ $settings['about_page_title'] ?? 'About Us' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                        <input type="text" name="settings[about_page_subtitle]" value="{{ $settings['about_page_subtitle'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                        <textarea name="settings[about_page_description]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['about_page_description'] ?? '' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hero Image URL</label>
                        <input type="text" name="settings[about_page_hero_image]" value="{{ $settings['about_page_hero_image'] ?? '' }}" placeholder="https://example.com/image.jpg or /storage/..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Leave empty to hide hero image</p>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Page Info</button>
                </div>
            </form>
        </div>

        <!-- Story / Mission / Vision -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Story, Mission & Vision</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.about') }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Story Title</label>
                        <input type="text" name="settings[about_page_story_title]" value="{{ $settings['about_page_story_title'] ?? 'Our Story' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Story Content</label>
                        <textarea name="settings[about_page_story_content]" rows="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['about_page_story_content'] ?? '' }}</textarea>
                    </div>
                    <div class="border-t border-gray-200 pt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mission Title</label>
                        <input type="text" name="settings[about_page_mission_title]" value="{{ $settings['about_page_mission_title'] ?? 'Our Mission' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mission Content</label>
                        <textarea name="settings[about_page_mission_content]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['about_page_mission_content'] ?? '' }}</textarea>
                    </div>
                    <div class="border-t border-gray-200 pt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vision Title</label>
                        <input type="text" name="settings[about_page_vision_title]" value="{{ $settings['about_page_vision_title'] ?? 'Our Vision' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vision Content</label>
                        <textarea name="settings[about_page_vision_content]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['about_page_vision_content'] ?? '' }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Content</button>
                </div>
            </form>
        </div>

        <!-- Core Values -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Core Values</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST" id="valuesForm">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.about') }}">
                <div id="valuesContainer" class="space-y-4">
                    @forelse($aboutPageValues as $index => $item)
                    <div class="value-row border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Icon</label>
                                <input type="text" name="value_icons[]" value="{{ $item['icon'] ?? '' }}" placeholder="e.g., Gem, Heart" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Title</label>
                                <input type="text" name="value_titles[]" value="{{ $item['title'] ?? '' }}" placeholder="Title" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-end">
                                <button type="button" onclick="removeValue(this)" class="px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                                    <i class="fas fa-trash mr-1"></i>Remove
                                </button>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                                <textarea name="value_descriptions[]" rows="2" placeholder="Description" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $item['description'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="value-row border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Icon</label>
                                <input type="text" name="value_icons[]" placeholder="e.g., Gem, Heart" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Title</label>
                                <input type="text" name="value_titles[]" placeholder="Title" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-end">
                                <button type="button" onclick="removeValue(this)" class="px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                                    <i class="fas fa-trash mr-1"></i>Remove
                                </button>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                                <textarea name="value_descriptions[]" rows="2" placeholder="Description" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>
                <input type="hidden" name="settings[about_page_values]" id="valuesJson">
                <button type="button" onclick="addValue()" class="mt-4 px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg border border-blue-200">
                    <i class="fas fa-plus mr-1"></i> Add Value
                </button>
                <div class="mt-4">
                    <button type="button" onclick="saveValues()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Values</button>
                </div>
            </form>
        </div>

        <!-- Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Company Stats</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST" id="statsForm">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.about') }}">
                <div id="statsContainer" class="space-y-3">
                    @forelse($aboutPageStats as $index => $item)
                    <div class="stat-row flex gap-3 items-start">
                        <div class="w-1/3">
                            <input type="text" name="stat_values[]" value="{{ $item['value'] ?? '' }}" placeholder="e.g., 50K+" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="flex-1">
                            <input type="text" name="stat_labels[]" value="{{ $item['label'] ?? '' }}" placeholder="Label e.g., Happy Customers" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="button" onclick="removeStat(this)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @empty
                    <div class="stat-row flex gap-3 items-start">
                        <div class="w-1/3">
                            <input type="text" name="stat_values[]" placeholder="e.g., 50K+" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="flex-1">
                            <input type="text" name="stat_labels[]" placeholder="Label e.g., Happy Customers" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="button" onclick="removeStat(this)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @endforelse
                </div>
                <input type="hidden" name="settings[about_page_stats]" id="statsJson">
                <button type="button" onclick="addStat()" class="mt-4 px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg border border-blue-200">
                    <i class="fas fa-plus mr-1"></i> Add Stat
                </button>
                <div class="mt-4">
                    <button type="button" onclick="saveStats()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Stats</button>
                </div>
            </form>
        </div>

        <!-- Milestones -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Milestones / Timeline</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST" id="milestonesForm">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.about') }}">
                <div id="milestonesContainer" class="space-y-4">
                    @forelse($aboutPageMilestones as $index => $item)
                    <div class="milestone-row border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Year</label>
                                <input type="text" name="milestone_years[]" value="{{ $item['year'] ?? '' }}" placeholder="e.g., 2020" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Title</label>
                                <input type="text" name="milestone_titles[]" value="{{ $item['title'] ?? '' }}" placeholder="Milestone title" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-end">
                                <button type="button" onclick="removeMilestone(this)" class="px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                                    <i class="fas fa-trash mr-1"></i>Remove
                                </button>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                                <textarea name="milestone_descriptions[]" rows="2" placeholder="Description" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $item['description'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="milestone-row border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Year</label>
                                <input type="text" name="milestone_years[]" placeholder="e.g., 2020" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Title</label>
                                <input type="text" name="milestone_titles[]" placeholder="Milestone title" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-end">
                                <button type="button" onclick="removeMilestone(this)" class="px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                                    <i class="fas fa-trash mr-1"></i>Remove
                                </button>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                                <textarea name="milestone_descriptions[]" rows="2" placeholder="Description" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>
                <input type="hidden" name="settings[about_page_milestones]" id="milestonesJson">
                <button type="button" onclick="addMilestone()" class="mt-4 px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg border border-blue-200">
                    <i class="fas fa-plus mr-1"></i> Add Milestone
                </button>
                <div class="mt-4">
                    <button type="button" onclick="saveMilestones()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Milestones</button>
                </div>
            </form>
        </div>

        <!-- Page Features -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Page Features</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.about') }}">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-700">Team Section</p>
                            <p class="text-sm text-gray-500">Show team members on the about page</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[about_page_show_team]" value="0">
                            <input type="checkbox" name="settings[about_page_show_team]" value="1" {{ ($settings['about_page_show_team'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-700">Milestones Timeline</p>
                            <p class="text-sm text-gray-500">Show company milestones on the about page</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[about_page_show_milestones]" value="0">
                            <input type="checkbox" name="settings[about_page_show_milestones]" value="1" {{ ($settings['about_page_show_milestones'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Features</button>
                </div>
            </form>
        </div>

        <!-- CTA Settings -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Bottom CTA Section</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.about') }}">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-700">Enable CTA</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[about_page_cta_enabled]" value="0">
                            <input type="checkbox" name="settings[about_page_cta_enabled]" value="1" {{ ($settings['about_page_cta_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CTA Title</label>
                        <input type="text" name="settings[about_page_cta_title]" value="{{ $settings['about_page_cta_title'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CTA Text</label>
                        <textarea name="settings[about_page_cta_text]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['about_page_cta_text'] ?? '' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Text</label>
                        <input type="text" name="settings[about_page_cta_button]" value="{{ $settings['about_page_cta_button'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Link</label>
                        <input type="text" name="settings[about_page_cta_link]" value="{{ $settings['about_page_cta_link'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save CTA</button>
                </div>
            </form>
        </div>

        <!-- SEO Settings -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">SEO Settings</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.about') }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                        <input type="text" name="settings[about_page_meta_title]" value="{{ $settings['about_page_meta_title'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                        <textarea name="settings[about_page_meta_description]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $settings['about_page_meta_description'] ?? '' }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save SEO</button>
                </div>
            </form>
        </div>

        <!-- Quick Links -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Manage Team Members</h2>
            <p class="text-sm text-gray-500 mb-4">Add, edit, or organize your team members displayed on the about page.</p>
            <a href="{{ route('admin.team-members.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-users mr-2"></i>Team Members
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function removeValue(button) {
        const row = button.closest('.value-row');
        const container = document.getElementById('valuesContainer');
        if (container.children.length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input, textarea').forEach(input => input.value = '');
        }
    }

    function addValue() {
        const container = document.getElementById('valuesContainer');
        const newRow = document.createElement('div');
        newRow.className = 'value-row border border-gray-200 rounded-lg p-4';
        newRow.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Icon</label>
                    <input type="text" name="value_icons[]" placeholder="e.g., Gem, Heart" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Title</label>
                    <input type="text" name="value_titles[]" placeholder="Title" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="removeValue(this)" class="px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                        <i class="fas fa-trash mr-1"></i>Remove
                    </button>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                    <textarea name="value_descriptions[]" rows="2" placeholder="Description" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
        `;
        container.appendChild(newRow);
    }

    function saveValues() {
        const icons = document.querySelectorAll('input[name="value_icons[]"]');
        const titles = document.querySelectorAll('input[name="value_titles[]"]');
        const descriptions = document.querySelectorAll('textarea[name="value_descriptions[]"]');
        const items = [];

        for (let i = 0; i < titles.length; i++) {
            if (titles[i].value && descriptions[i].value) {
                items.push({
                    icon: icons[i].value,
                    title: titles[i].value,
                    description: descriptions[i].value,
                });
            }
        }

        document.getElementById('valuesJson').value = JSON.stringify(items);
        document.getElementById('valuesForm').submit();
    }

    function removeStat(button) {
        const row = button.closest('.stat-row');
        const container = document.getElementById('statsContainer');
        if (container.children.length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input').forEach(input => input.value = '');
        }
    }

    function addStat() {
        const container = document.getElementById('statsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'stat-row flex gap-3 items-start';
        newRow.innerHTML = `
            <div class="w-1/3">
                <input type="text" name="stat_values[]" placeholder="e.g., 50K+" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex-1">
                <input type="text" name="stat_labels[]" placeholder="Label e.g., Happy Customers" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="button" onclick="removeStat(this)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(newRow);
    }

    function saveStats() {
        const values = document.querySelectorAll('input[name="stat_values[]"]');
        const labels = document.querySelectorAll('input[name="stat_labels[]"]');
        const items = [];

        for (let i = 0; i < values.length; i++) {
            if (values[i].value && labels[i].value) {
                items.push({
                    value: values[i].value,
                    label: labels[i].value,
                });
            }
        }

        document.getElementById('statsJson').value = JSON.stringify(items);
        document.getElementById('statsForm').submit();
    }

    function removeMilestone(button) {
        const row = button.closest('.milestone-row');
        const container = document.getElementById('milestonesContainer');
        if (container.children.length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input, textarea').forEach(input => input.value = '');
        }
    }

    function addMilestone() {
        const container = document.getElementById('milestonesContainer');
        const newRow = document.createElement('div');
        newRow.className = 'milestone-row border border-gray-200 rounded-lg p-4';
        newRow.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Year</label>
                    <input type="text" name="milestone_years[]" placeholder="e.g., 2020" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Title</label>
                    <input type="text" name="milestone_titles[]" placeholder="Milestone title" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="removeMilestone(this)" class="px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                        <i class="fas fa-trash mr-1"></i>Remove
                    </button>
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                    <textarea name="milestone_descriptions[]" rows="2" placeholder="Description" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
        `;
        container.appendChild(newRow);
    }

    function saveMilestones() {
        const years = document.querySelectorAll('input[name="milestone_years[]"]');
        const titles = document.querySelectorAll('input[name="milestone_titles[]"]');
        const descriptions = document.querySelectorAll('textarea[name="milestone_descriptions[]"]');
        const items = [];

        for (let i = 0; i < titles.length; i++) {
            if (years[i].value && titles[i].value && descriptions[i].value) {
                items.push({
                    year: years[i].value,
                    title: titles[i].value,
                    description: descriptions[i].value,
                });
            }
        }

        document.getElementById('milestonesJson').value = JSON.stringify(items);
        document.getElementById('milestonesForm').submit();
    }
</script>
@endpush
