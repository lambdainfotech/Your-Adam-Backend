@extends('admin.layouts.master')

@section('title', 'Edit Brand Value')
@section('page-title', 'Edit Brand Value')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">Edit Brand Value</h2>
        
        <form method="POST" action="{{ route('admin.brand-values.update', $brandValue) }}">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Icon *</label>
                    <select name="icon" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('icon') border-red-500 @enderror">
                        <option value="">Select an icon</option>
                        <option value="Palette" {{ old('icon', $brandValue->icon) == 'Palette' ? 'selected' : '' }}>🎨 Palette (Design)</option>
                        <option value="Shirt" {{ old('icon', $brandValue->icon) == 'Shirt' ? 'selected' : '' }}>👕 Shirt (Quality)</option>
                        <option value="Truck" {{ old('icon', $brandValue->icon) == 'Truck' ? 'selected' : '' }}>🚛 Truck (Delivery)</option>
                        <option value="Leaf" {{ old('icon', $brandValue->icon) == 'Leaf' ? 'selected' : '' }}>🍃 Leaf (Sustainable)</option>
                        <option value="Shield" {{ old('icon', $brandValue->icon) == 'Shield' ? 'selected' : '' }}>🛡️ Shield (Security)</option>
                        <option value="Clock" {{ old('icon', $brandValue->icon) == 'Clock' ? 'selected' : '' }}>🕐 Clock (Returns)</option>
                        <option value="Award" {{ old('icon', $brandValue->icon) == 'Award' ? 'selected' : '' }}>🏆 Award (Quality)</option>
                        <option value="Heart" {{ old('icon', $brandValue->icon) == 'Heart' ? 'selected' : '' }}>❤️ Heart (Care)</option>
                        <option value="Headphones" {{ old('icon', $brandValue->icon) == 'Headphones' ? 'selected' : '' }}>🎧 Headphones (Support)</option>
                        <option value="Star" {{ old('icon', $brandValue->icon) == 'Star' ? 'selected' : '' }}>⭐ Star (Rating)</option>
                    </select>
                    @error('icon')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $brandValue->title) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                    <input type="text" name="description" value="{{ old('description', $brandValue->description) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $brandValue->sort_order) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('sort_order') border-red-500 @enderror">
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer mt-6">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $brandValue->is_active) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Update Brand Value
                </button>
                <a href="{{ route('admin.brand-values.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
