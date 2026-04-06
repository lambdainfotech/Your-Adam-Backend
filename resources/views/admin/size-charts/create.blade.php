@extends('admin.layouts.master')

@section('title', 'Create Size Chart')
@section('page-title', 'Create Size Chart')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.size-charts.store') }}" method="POST" id="sizeChartForm">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Chart Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g., Men's T-Shirt Sizes">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                <select name="category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Unit *</label>
                <select name="unit" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    @foreach($units as $key => $label)
                        <option value="{{ $key }}" {{ old('unit', 'inch') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Size Type *</label>
                <div class="flex items-center gap-4 mt-2">
                    @foreach($sizeTypes as $key => $label)
                        <label class="inline-flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ old('size_type', 'asian') == $key ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                            <input type="radio" name="size_type" value="{{ $key }}" 
                                {{ old('size_type', 'asian') == $key ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                onchange="updateSizeTypeStyle(this)">
                            <span class="ml-2 text-sm font-medium {{ old('size_type', 'asian') == $key ? 'text-blue-700' : 'text-gray-700' }}">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('size_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mb-6">
            <div class="flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label class="ml-2 text-sm font-medium text-gray-700">Active</label>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description" rows="2" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="Optional description">{{ old('description') }}</textarea>
        </div>

        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <label class="block text-sm font-medium text-gray-700">Size Measurements *</label>
                <button type="button" onclick="addRow()" class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                    <i class="fas fa-plus mr-1"></i>Add Size
                </button>
            </div>
            
            <div id="rowsContainer" class="space-y-3">
                <!-- Rows will be added here -->
            </div>
            
            @error('rows')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Create Size Chart
            </button>
            <a href="{{ route('admin.size-charts.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
let rowCount = 0;

function addRow() {
    rowCount++;
    const container = document.getElementById('rowsContainer');
    const row = document.createElement('div');
    row.className = 'flex gap-3 items-start p-4 bg-gray-50 rounded-lg';
    row.innerHTML = `
        <div class="flex-1">
            <input type="text" name="rows[${rowCount}][size_name]" required
                placeholder="e.g., S, M, L, XL" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mb-2">
            <div class="measurements-container space-y-2">
                <div class="flex gap-2">
                    <input type="text" name="rows[${rowCount}][measurements][chest]" 
                        placeholder="Chest (e.g., 38-40)" 
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <input type="text" name="rows[${rowCount}][measurements][length]" 
                        placeholder="Length (e.g., 28)" 
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div class="flex gap-2">
                    <input type="text" name="rows[${rowCount}][measurements][shoulder]" 
                        placeholder="Shoulder" 
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <input type="text" name="rows[${rowCount}][measurements][sleeve]" 
                        placeholder="Sleeve" 
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
        </div>
        <button type="button" onclick="removeRow(this)" class="text-red-600 hover:text-red-800 mt-1">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(row);
}

function removeRow(btn) {
    btn.closest('.flex').remove();
}

// Add first row by default
addRow();

// Update size type radio button styles
function updateSizeTypeStyle(radio) {
    document.querySelectorAll('input[name="size_type"]').forEach(input => {
        const label = input.closest('label');
        if (input.checked) {
            label.classList.remove('border-gray-300');
            label.classList.add('border-blue-500', 'bg-blue-50');
            label.querySelector('span').classList.remove('text-gray-700');
            label.querySelector('span').classList.add('text-blue-700');
        } else {
            label.classList.remove('border-blue-500', 'bg-blue-50');
            label.classList.add('border-gray-300');
            label.querySelector('span').classList.remove('text-blue-700');
            label.querySelector('span').classList.add('text-gray-700');
        }
    });
}
</script>
@endpush
@endsection
