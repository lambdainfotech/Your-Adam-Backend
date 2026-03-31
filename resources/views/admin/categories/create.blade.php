@extends('admin.layouts.master')

@section('title', 'Add Category')
@section('page-title', 'Add New Category')

@section('content')
<div>
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" id="categoryForm">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-info-circle text-blue-600"></i>
                            </span>
                            Basic Information
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                                    placeholder="Enter category name">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                                <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('slug') border-red-500 @enderror"
                                    placeholder="auto-generated-if-empty">
                                <p class="mt-1 text-xs text-gray-500">Leave empty to auto-generate from name</p>
                                @error('slug')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">Parent Category</label>
                                <select name="parent_id" id="parent_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('parent_id') border-red-500 @enderror">
                                    <option value="">None (Root Category)</option>
                                    @foreach($parentCategories as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea name="description" id="description" rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                                    placeholder="Category description">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Category Image -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-image text-purple-600"></i>
                            </span>
                            Category Image
                        </h3>
                        
                        <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-purple-400 hover:bg-purple-50 transition-all cursor-pointer relative">
                            <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden">
                            
                            <!-- Default State -->
                            <div id="dropZoneDefault">
                                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-cloud-upload-alt text-purple-600 text-2xl"></i>
                                </div>
                                <p class="text-gray-600 text-sm mb-2">Upload Category Image</p>
                                <p class="text-gray-400 text-xs mb-3">Drag and drop or click to browse</p>
                                <button type="button" id="browseButton" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm">
                                    <i class="fas fa-folder-open mr-2"></i>Select Image
                                </button>
                                <p class="text-xs text-gray-400 mt-2">JPG, PNG, WebP | Max 2MB</p>
                            </div>

                            <!-- Preview State -->
                            <div id="dropZonePreview" class="hidden">
                                <div class="relative inline-block">
                                    <img id="imagePreview" src="" alt="Preview" class="max-h-48 rounded-lg shadow-md">
                                    <button type="button" id="removeImage" class="absolute -top-2 -right-2 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 shadow-lg">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <p class="text-sm text-gray-600 mt-2" id="imageName"></p>
                            </div>

                            <!-- Drag Overlay -->
                            <div id="dragOverlay" class="absolute inset-0 bg-purple-600 bg-opacity-90 rounded-xl flex items-center justify-center hidden">
                                <div class="text-white text-center">
                                    <i class="fas fa-cloud-upload-alt text-4xl mb-2"></i>
                                    <p class="font-medium">Drop image here</p>
                                </div>
                            </div>
                        </div>

                        @error('image')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Settings -->
                    <div class="bg-gray-50 rounded-xl p-5">
                        <h3 class="font-semibold text-gray-800 mb-4">Settings</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('sort_order') border-red-500 @enderror">
                                @error('sort_order')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800 text-sm">Active Status</p>
                                    <p class="text-xs text-gray-500">Category will be visible</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                        <div class="space-y-3">
                            <button type="submit" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors">
                                <i class="fas fa-save mr-2"></i>Create Category
                            </button>
                            <a href="{{ route('admin.categories.index') }}" class="w-full px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors flex items-center justify-center">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Drop Zone Elements
    const dropZone = document.getElementById('dropZone');
    const imageInput = document.getElementById('imageInput');
    const dropZoneDefault = document.getElementById('dropZoneDefault');
    const dropZonePreview = document.getElementById('dropZonePreview');
    const imagePreview = document.getElementById('imagePreview');
    const imageName = document.getElementById('imageName');
    const dragOverlay = document.getElementById('dragOverlay');
    const maxSize = 2 * 1024 * 1024; // 2MB

    // Click on drop zone (but not on buttons)
    dropZone.addEventListener('click', (e) => {
        if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
            return;
        }
        imageInput.click();
    });

    // Browse button click
    document.getElementById('browseButton').addEventListener('click', (e) => {
        e.stopPropagation();
        imageInput.click();
    });

    // Drag & Drop Events
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dragOverlay.classList.remove('hidden');
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            if (e.target === dropZone || !dropZone.contains(e.relatedTarget)) {
                dragOverlay.classList.add('hidden');
                dropZone.classList.remove('dragover');
            }
        }, false);
    });

    // Handle file drop
    dropZone.addEventListener('drop', (e) => {
        dragOverlay.classList.add('hidden');
        dropZone.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    }, false);

    // Handle file select
    imageInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFile(e.target.files[0]);
        }
    });

    function handleFile(file) {
        if (file.size > maxSize) {
            alert('Image is too large. Maximum size is 2MB.');
            imageInput.value = '';
            return;
        }
        
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file.');
            imageInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.src = e.target.result;
            imageName.textContent = file.name;
            dropZoneDefault.classList.add('hidden');
            dropZonePreview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }

    // Remove image
    document.getElementById('removeImage').addEventListener('click', (e) => {
        e.stopPropagation();
        imageInput.value = '';
        imagePreview.src = '';
        dropZonePreview.classList.add('hidden');
        dropZoneDefault.classList.remove('hidden');
    });

    // Auto-generate slug
    document.getElementById('name').addEventListener('blur', function() {
        const slugField = document.getElementById('slug');
        if (slugField.value === '' && this.value !== '') {
            slugField.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        }
    });
</script>
@endpush
