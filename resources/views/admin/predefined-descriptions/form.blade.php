@extends('admin.layouts.master')

@section('title', isset($predefinedDescription) ? 'Edit Predefined Description' : 'Create Predefined Description')

@push('styles')
<style>
    .ck-editor__editable {
        min-height: 300px !important;
    }
    .ck.ck-editor__main > .ck-editor__editable {
        min-height: 300px;
    }
</style>
@endpush

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.predefined-descriptions.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
            <i class="fas fa-arrow-left mr-1"></i>Back to List
        </a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">
            {{ isset($predefinedDescription) ? 'Edit Predefined Description' : 'Create Predefined Description' }}
        </h1>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm p-6 max-w-3xl">
        <form method="POST" 
            action="{{ isset($predefinedDescription) ? route('admin.predefined-descriptions.update', $predefinedDescription) : route('admin.predefined-descriptions.store') }}">
            @csrf
            @if(isset($predefinedDescription))
                @method('PUT')
            @endif

            <!-- Type -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Type <span class="text-red-500">*</span></label>
                <div class="flex gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="type" value="description" 
                            {{ old('type', $predefinedDescription->type ?? request('type', 'description')) == 'description' ? 'checked' : '' }}
                            class="text-blue-600 focus:ring-blue-500">
                        <span class="ml-2">Full Description</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="type" value="short_description"
                            {{ old('type', $predefinedDescription->type ?? '') == 'short_description' ? 'checked' : '' }}
                            class="text-blue-600 focus:ring-blue-500">
                        <span class="ml-2">Short Description</span>
                    </label>
                </div>
                @error('type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" 
                    value="{{ old('name', $predefinedDescription->name ?? '') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    placeholder="e.g., Electronics Standard Description" required>
                <p class="text-xs text-gray-500 mt-1">A recognizable name for easy identification</p>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Content -->
            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                    Content <span class="text-red-500">*</span>
                </label>
                <textarea name="content" id="content" rows="8"
                    class="ckeditor w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('content') border-red-500 @enderror"
                    placeholder="Enter the description content here...">{{ old('content', $predefinedDescription->content ?? '') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">This content will be used when the predefined description is selected</p>
                @error('content')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Status -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" 
                        {{ old('is_active', $predefinedDescription->is_active ?? true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
                <p class="text-xs text-gray-500 mt-1">Inactive descriptions won't appear in the dropdown</p>
            </div>

            <!-- Preview -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                <div id="contentPreview" class="text-sm text-gray-600 bg-white p-3 rounded border border-gray-200 min-h-[60px]">
                    {{ old('content', $predefinedDescription->content ?? 'Preview will appear here...') }}
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center gap-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" onclick="syncTinyMCEContent()">
                    <i class="fas fa-save mr-2"></i>{{ isset($predefinedDescription) ? 'Update' : 'Create' }}
                </button>
                <a href="{{ route('admin.predefined-descriptions.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
<script>
    let contentEditor;
    
    // Initialize CKEditor
    ClassicEditor
        .create(document.querySelector('#content'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'underline', 'link', '|', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
        })
        .then(editor => {
            contentEditor = editor;
            
            // Update preview on change
            editor.model.document.on('change:data', () => {
                const preview = document.getElementById('contentPreview');
                preview.innerHTML = editor.getData() || 'Preview will appear here...';
            });
        })
        .catch(error => {
            console.error(error);
        });

    // Sync editor content before form submission
    document.querySelector('form').addEventListener('submit', function(e) {
        if (contentEditor) {
            document.querySelector('#content').value = contentEditor.getData();
        }
    });

    // Update name placeholder based on type
    document.querySelectorAll('input[name="type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const nameInput = document.getElementById('name');
            if (this.value === 'short_description') {
                nameInput.placeholder = 'e.g., Welcome Offer';
            } else {
                nameInput.placeholder = 'e.g., Electronics Standard Description';
            }
        });
    });
</script>
@endpush
