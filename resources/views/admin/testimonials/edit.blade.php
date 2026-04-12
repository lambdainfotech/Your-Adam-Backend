@extends('admin.layouts.master')

@section('title', 'Edit Testimonial')
@section('page-title', 'Edit Testimonial')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">Edit Testimonial</h2>
        
        <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Customer Name *</label>
                    <input type="text" name="name" value="{{ old('name', $testimonial->name) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role / Title</label>
                    <input type="text" name="role" value="{{ old('role', $testimonial->role) }}" placeholder="e.g., Regular Customer, Business Owner"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('role') border-red-500 @enderror">
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Avatar URL</label>
                    <div class="flex items-center gap-3">
                        @if($testimonial->avatar)
                            <img src="{{ $testimonial->avatar }}" alt="" class="w-12 h-12 rounded-full object-cover">
                        @endif
                        <input type="url" name="avatar" value="{{ old('avatar', $testimonial->avatar) }}" placeholder="https://example.com/avatar.jpg"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('avatar') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Leave empty for default avatar</p>
                    @error('avatar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating *</label>
                    <select name="rating" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('rating') border-red-500 @enderror">
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ old('rating', $testimonial->rating) == $i ? 'selected' : '' }}>{{ $i }} Stars</option>
                        @endfor
                    </select>
                    @error('rating')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Testimonial Content *</label>
                    <textarea name="content" rows="4" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('content') border-red-500 @enderror">{{ old('content', $testimonial->content) }}</textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $testimonial->sort_order) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('sort_order') border-red-500 @enderror">
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer mt-6">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $testimonial->is_active) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Update Testimonial
                </button>
                <a href="{{ route('admin.testimonials.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
