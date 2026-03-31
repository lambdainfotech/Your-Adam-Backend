@extends('admin.layouts.master')

@section('title', 'Edit Size Chart')
@section('page-title', 'Edit Size Chart')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.size-charts.update', $sizeChart) }}" method="POST" id="sizeChartForm">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Chart Name *</label>
                <input type="text" name="name" value="{{ old('name', $sizeChart->name) }}" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                <select name="category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $sizeChart->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Unit *</label>
                <select name="unit" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    @foreach($units as $key => $label)
                        <option value="{{ $key }}" {{ old('unit', $sizeChart->unit) == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-center pt-8">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $sizeChart->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label class="ml-2 text-sm font-medium text-gray-700">Active</label>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description" rows="2" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('description', $sizeChart->description) }}</textarea>
        </div>

        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <label class="block text-sm font-medium text-gray-700">Size Measurements *</label>
                <button type="button" onclick="addRow()" class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                    <i class="fas fa-plus mr-1"></i>Add Size
                </button>
            </div>
            
            <div id="rowsContainer" class="space-y-3">
                @foreach($sizeChart->rows as $index => $row)
                <div class="flex gap-3 items-start p-4 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <input type="text" name="rows[{{ $index }}][size_name]" required
                            value="{{ $row->size_name }}"
                            placeholder="e.g., S, M, L, XL" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mb-2">
                        <div class="measurements-container space-y-2">
                            <div class="flex gap-2">
                                <input type="text" name="rows[{{ $index }}][measurements][chest]" 
                                    value="{{ $row->measurements['chest'] ?? '' }}"
                                    placeholder="Chest (e.g., 38-40)" 
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <input type="text" name="rows[{{ $index }}][measurements][length]" 
                                    value="{{ $row->measurements['length'] ?? '' }}"
                                    placeholder="Length (e.g., 28)" 
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div class="flex gap-2">
                                <input type="text" name="rows[{{ $index }}][measurements][shoulder]" 
                                    value="{{ $row->measurements['shoulder'] ?? '' }}"
                                    placeholder="Shoulder" 
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <input type="text" name="rows[{{ $index }}][measurements][sleeve]" 
                                    value="{{ $row->measurements['sleeve'] ?? '' }}"
                                    placeholder="Sleeve" 
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="removeRow(this)" class="text-red-600 hover:text-red-800 mt-1">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Update Size Chart
            </button>
            <a href="{{ route('admin.size-charts.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
let rowCount = {{ $sizeChart->rows->count() }};

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
</script>
@endpush
@endsection
