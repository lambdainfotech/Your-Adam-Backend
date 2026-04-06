@extends('admin.layouts.master')

@section('title', isset($slider) ? 'Edit Slider' : 'Create Slider')
@section('page-title', isset($slider) ? 'Edit Slider' : 'Create Slider')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ isset($slider) ? route('admin.sliders.update', $slider) : route('admin.sliders.store') }}" 
          method="POST" 
          enctype="multipart/form-data">
        @csrf
        @if(isset($slider))
            @method('PUT')
        @endif
        
        <!-- Banner Image -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Banner Image *</label>
            <input type="file" name="banner_image" accept="image/*"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('banner_image') border-red-500 @enderror"
                {{ !isset($slider) ? 'required' : '' }}>
            <p class="text-xs text-gray-500 mt-1">Recommended size: 1920x600px. Max size: 2MB (JPEG, PNG, JPG, WebP)</p>
            @error('banner_image')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            
            @if(isset($slider) && $slider->banner_image_url)
                <div class="mt-4">
                    <p class="text-sm text-gray-600 mb-2">Current Image:</p>
                    <img src="{{ $slider->banner_image_url }}" alt="{{ $slider->title }}" 
                        class="h-32 object-cover rounded-lg">
                </div>
            @endif
        </div>

        <!-- Title & Subtitle -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                <input type="text" name="title" value="{{ old('title', $slider->title ?? '') }}" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror"
                    placeholder="e.g., Summer Sale"
                    autocomplete="off">
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                <input type="text" name="subtitle" value="{{ old('subtitle', $slider->subtitle ?? '') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('subtitle') border-red-500 @enderror"
                    placeholder="e.g., Up to 50% off on all products"
                    autocomplete="off">
                @error('subtitle')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Title & Subtitle Colors -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Title Color</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="title_color" 
                        value="{{ old('title_color', $slider->title_color ?? '#FFFFFF') }}"
                        class="h-10 w-16 border border-gray-300 rounded cursor-pointer">
                    <input type="text" 
                        value="{{ old('title_color', $slider->title_color ?? '#FFFFFF') }}"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        id="title_color_text"
                        placeholder="#FFFFFF"
                        autocomplete="off">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle Color</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="subtitle_color" 
                        value="{{ old('subtitle_color', $slider->subtitle_color ?? '#FFFFFF') }}"
                        class="h-10 w-16 border border-gray-300 rounded cursor-pointer">
                    <input type="text" 
                        value="{{ old('subtitle_color', $slider->subtitle_color ?? '#FFFFFF') }}"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        id="subtitle_color_text"
                        placeholder="#FFFFFF"
                        autocomplete="off">
                </div>
            </div>
        </div>

        <!-- Button Configuration -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <label class="block text-sm font-medium text-gray-700 mb-3">
                <input type="checkbox" id="has_button" {{ old('button_text', $slider->button_text ?? '') ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 rounded mr-2">
                Add Call-to-Action Button
            </label>

            <div id="buttonSection" class="{{ old('button_text', $slider->button_text ?? '') ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Text</label>
                        <input type="text" name="button_text" value="{{ old('button_text', $slider->button_text ?? '') }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., Shop Now"
                            autocomplete="off">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Link URL</label>
                        <input type="text" name="button_url" value="{{ old('button_url', $slider->button_url ?? '') }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., /products or https://example.com"
                            autocomplete="off">
                    </div>
                </div>

                <!-- Color Pickers -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Text Color</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="button_text_color" 
                                value="{{ old('button_text_color', $slider->button_text_color ?? '#FFFFFF') }}"
                                class="h-10 w-16 border border-gray-300 rounded cursor-pointer">
                            <input type="text" 
                                value="{{ old('button_text_color', $slider->button_text_color ?? '#FFFFFF') }}"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                id="button_text_color_text"
                                placeholder="#FFFFFF"
                                autocomplete="off">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Background Color</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="button_bg_color" 
                                value="{{ old('button_bg_color', $slider->button_bg_color ?? '#3B82F6') }}"
                                class="h-10 w-16 border border-gray-300 rounded cursor-pointer">
                            <input type="text" 
                                value="{{ old('button_bg_color', $slider->button_bg_color ?? '#3B82F6') }}"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                id="button_bg_color_text"
                                placeholder="#3B82F6"
                                autocomplete="off">
                        </div>
                    </div>
                </div>

                <!-- Button Preview -->
                <div class="mt-4 p-4 bg-white rounded-lg border border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preview:</label>
                    <button type="button" id="buttonPreview" 
                        class="px-6 py-2 rounded-lg transition-all duration-200 hover:opacity-90">
                        <span id="buttonPreviewText">Shop Now</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Active Status -->
        <div class="flex items-center gap-4 mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" 
                    {{ old('is_active', $slider->is_active ?? true) ? 'checked' : '' }} 
                    class="w-4 h-4 text-blue-600 rounded">
                <span class="ml-2 text-sm">Active</span>
            </label>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center gap-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                {{ isset($slider) ? 'Update Slider' : 'Create Slider' }}
            </button>
            <a href="{{ route('admin.sliders.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle button section
        const hasButtonCheckbox = document.getElementById('has_button');
        const buttonSection = document.getElementById('buttonSection');
        
        if (hasButtonCheckbox && buttonSection) {
            hasButtonCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    buttonSection.classList.remove('hidden');
                } else {
                    buttonSection.classList.add('hidden');
                    // Clear button fields when unchecked
                    const buttonTextInput = document.querySelector('input[name="button_text"]');
                    const buttonUrlInput = document.querySelector('input[name="button_url"]');
                    if (buttonTextInput) buttonTextInput.value = '';
                    if (buttonUrlInput) buttonUrlInput.value = '';
                    updateButtonPreview();
                }
            });
        }

        // Update button preview
        function updateButtonPreview() {
            const textColorInput = document.querySelector('input[name="button_text_color"]');
            const bgColorInput = document.querySelector('input[name="button_bg_color"]');
            const buttonTextInput = document.querySelector('input[name="button_text"]');
            const preview = document.getElementById('buttonPreview');
            const previewText = document.getElementById('buttonPreviewText');
            
            if (!textColorInput || !bgColorInput || !preview || !previewText) return;
            
            const textColor = textColorInput.value;
            const bgColor = bgColorInput.value;
            const buttonText = buttonTextInput && buttonTextInput.value ? buttonTextInput.value : 'Shop Now';
            
            preview.style.color = textColor;
            preview.style.backgroundColor = bgColor;
            previewText.textContent = buttonText;
        }

        // Sync button color inputs with text inputs
        const textColorInput = document.querySelector('input[name="button_text_color"]');
        const textColorText = document.getElementById('button_text_color_text');
        const bgColorInput = document.querySelector('input[name="button_bg_color"]');
        const bgColorText = document.getElementById('button_bg_color_text');
        const buttonTextInput = document.querySelector('input[name="button_text"]');

        if (textColorInput && textColorText) {
            textColorInput.addEventListener('input', function() {
                textColorText.value = this.value;
                updateButtonPreview();
            });
            textColorText.addEventListener('input', function() {
                textColorInput.value = this.value;
                updateButtonPreview();
            });
        }

        if (bgColorInput && bgColorText) {
            bgColorInput.addEventListener('input', function() {
                bgColorText.value = this.value;
                updateButtonPreview();
            });
            bgColorText.addEventListener('input', function() {
                bgColorInput.value = this.value;
                updateButtonPreview();
            });
        }

        if (buttonTextInput) {
            buttonTextInput.addEventListener('input', updateButtonPreview);
        }

        // Sync title color inputs
        const titleColorInput = document.querySelector('input[name="title_color"]');
        const titleColorText = document.getElementById('title_color_text');

        if (titleColorInput && titleColorText) {
            titleColorInput.addEventListener('input', function() {
                titleColorText.value = this.value;
            });
            titleColorText.addEventListener('input', function() {
                titleColorInput.value = this.value;
            });
        }

        // Sync subtitle color inputs
        const subtitleColorInput = document.querySelector('input[name="subtitle_color"]');
        const subtitleColorText = document.getElementById('subtitle_color_text');

        if (subtitleColorInput && subtitleColorText) {
            subtitleColorInput.addEventListener('input', function() {
                subtitleColorText.value = this.value;
            });
            subtitleColorText.addEventListener('input', function() {
                subtitleColorInput.value = this.value;
            });
        }

        // Initial preview update
        updateButtonPreview();
    });
</script>
@endpush
