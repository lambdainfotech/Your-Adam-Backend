@extends('admin.layouts.master')

@section('title', 'Edit Campaign')
@section('page-title', 'Edit Campaign')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.campaigns.update', $campaign) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Campaign Name *</label>
                <input type="text" name="name" value="{{ old('name', $campaign->name) }}" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Discount Type *</label>
                <select name="discount_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    @foreach($discountTypes as $key => $label)
                        <option value="{{ $key }}" {{ old('discount_type', $campaign->discount_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Discount Value *</label>
                <input type="number" name="discount_value" value="{{ old('discount_value', $campaign->discount_value) }}" required step="0.01" min="0"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Banner Image URL</label>
                <input type="text" name="banner_image" value="{{ old('banner_image', $campaign->banner_image) }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                <input type="datetime-local" name="starts_at" required
                    value="{{ old('starts_at', $campaign->starts_at->format('Y-m-d\TH:i')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                <input type="datetime-local" name="ends_at" required
                    value="{{ old('ends_at', $campaign->ends_at->format('Y-m-d\TH:i')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description" rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('description', $campaign->description) }}</textarea>
        </div>

        <div class="flex items-center gap-6 mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="apply_to_all" value="1" {{ old('apply_to_all', $campaign->apply_to_all) ? 'checked' : '' }} 
                    class="w-4 h-4 text-blue-600 rounded">
                <span class="ml-2">Apply to all products</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $campaign->is_active) ? 'checked' : '' }} 
                    class="w-4 h-4 text-blue-600 rounded">
                <span class="ml-2">Active</span>
            </label>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Update Campaign
            </button>
            <a href="{{ route('admin.campaigns.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
