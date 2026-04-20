@extends('admin.layouts.master')

@section('title', 'Create Attribute')
@section('page-title', 'Create New Attribute')

@section('content')
<div>
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.attributes.index') }}" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Back to Attributes
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                <i class="fas fa-tag text-blue-600 text-xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Create Attribute</h2>
                <p class="text-gray-500">Add a new product attribute with values</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.attributes.store') }}">
            @csrf

            <div class="space-y-6">
                <!-- Attribute Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Attribute Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., Color, Size, Material">
                    <p class="mt-1 text-xs text-gray-500">The display name for this attribute</p>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Attribute Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Attribute Code *</label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., color, size, material">
                    <p class="mt-1 text-xs text-gray-500">Unique code, lowercase with underscores (e.g., "shirt_color")</p>
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Attribute Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Attribute Type *</label>
                    <select name="type" id="type" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Type</option>
                        <option value="select" {{ old('type') == 'select' ? 'selected' : '' }}>Select (Dropdown)</option>
                        <option value="color" {{ old('type') == 'color' ? 'selected' : '' }}>Color Swatch</option>
                        <option value="size" {{ old('type') == 'size' ? 'selected' : '' }}>Size</option>
                        <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>Text</option>
                        <option value="number" {{ old('type') == 'number' ? 'selected' : '' }}>Number</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Options -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <input type="checkbox" name="is_filterable" id="is_filterable" value="1" {{ old('is_filterable', true) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <div class="ml-3">
                            <label for="is_filterable" class="block text-sm font-medium text-gray-700">Filterable</label>
                            <p class="text-xs text-gray-500">Show in product filters</p>
                        </div>
                    </div>
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <input type="checkbox" name="is_variation" id="is_variation" value="1" {{ old('is_variation', true) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <div class="ml-3">
                            <label for="is_variation" class="block text-sm font-medium text-gray-700">Use for Variations</label>
                            <p class="text-xs text-gray-500">Can be used to create product variants</p>
                        </div>
                    </div>
                </div>

                <!-- Sort Order -->
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                        class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Display order (0 = first)</p>
                </div>

                <!-- Attribute Values -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Attribute Values</label>
                    <p class="text-sm text-gray-500 mb-3">Add values for this attribute (e.g., Red, Blue, Green for Color)</p>
                    
                    <div id="valuesContainer" class="space-y-2">
                        <div class="flex items-center space-x-2 value-row">
                            <input type="text" name="values[]" 
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Value (e.g., Red)">
                            <input type="color" name="color_codes[]" 
                                class="w-12 h-10 px-1 py-1 border border-gray-300 rounded-lg cursor-pointer color-picker"
                                title="Pick color">
                            <button type="button" onclick="removeValue(this)" class="text-red-600 hover:text-red-800 px-2">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="button" onclick="addValue()" class="mt-3 text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-plus mr-1"></i>Add Another Value
                    </button>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-8 flex items-center justify-between">
                <a href="{{ route('admin.attributes.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Create Attribute
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const typeSelect = document.getElementById('type');
    const valuesContainer = document.getElementById('valuesContainer');

    function isColorType() {
        return typeSelect.value === 'color';
    }

    function toggleColorPickers() {
        const colorPickers = document.querySelectorAll('.color-picker');
        colorPickers.forEach(picker => {
            picker.style.display = isColorType() ? 'block' : 'none';
        });
    }

    function addValue() {
        const container = document.getElementById('valuesContainer');
        const div = document.createElement('div');
        div.className = 'flex items-center space-x-2 value-row';
        const showColor = isColorType();
        div.innerHTML = `
            <input type="text" name="values[]"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="Value (e.g., Red)">
            <input type="color" name="color_codes[]"
                class="w-12 h-10 px-1 py-1 border border-gray-300 rounded-lg cursor-pointer color-picker"
                title="Pick color"
                style="display: ${showColor ? 'block' : 'none'}">
            <button type="button" onclick="removeValue(this)" class="text-red-600 hover:text-red-800 px-2">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(div);
    }

    function removeValue(button) {
        const container = document.getElementById('valuesContainer');
        if (container.children.length > 1) {
            button.parentElement.remove();
        } else {
            // Clear the inputs instead of removing
            const inputs = button.parentElement.querySelectorAll('input');
            inputs.forEach(input => input.value = '');
        }
    }

    // Show/hide color pickers when type changes
    typeSelect.addEventListener('change', toggleColorPickers);

    // Initialize on page load
    toggleColorPickers();

    // Auto-generate code from name
    document.getElementById('name').addEventListener('blur', function() {
        const codeInput = document.getElementById('code');
        if (!codeInput.value && this.value) {
            codeInput.value = this.value.toLowerCase()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');
        }
    });
</script>
@endpush
