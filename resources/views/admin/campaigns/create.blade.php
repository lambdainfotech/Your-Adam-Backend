@extends('admin.layouts.master')

@section('title', 'Create Campaign')
@section('page-title', 'Create Campaign')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.campaigns.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Campaign Name & Discount Type -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Campaign Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Discount Type *</label>
                <select name="discount_type" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    @foreach($discountTypes as $key => $label)
                        <option value="{{ $key }}" {{ old('discount_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Discount Value & Banner Image -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Discount Value *</label>
                <input type="number" name="discount_value" value="{{ old('discount_value') }}" required 
                    step="0.01" min="0" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Banner Image</label>
                <input type="file" name="banner_image" accept="image/*"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Max size: 2MB (JPEG, PNG, JPG, WebP)</p>
            </div>
        </div>

        <!-- Start Date & End Date -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Description -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description" rows="3" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
        </div>

        <!-- Apply To Section -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <label class="block text-sm font-medium text-gray-700 mb-3">Apply To *</label>
            
            <div class="flex flex-wrap gap-4 mb-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="apply_type" value="all" 
                        {{ old('apply_type', 'all') == 'all' ? 'checked' : '' }}
                        class="apply-type-radio w-4 h-4 text-blue-600">
                    <span class="ml-2">All Products</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="apply_type" value="products" 
                        {{ old('apply_type') == 'products' ? 'checked' : '' }}
                        class="apply-type-radio w-4 h-4 text-blue-600">
                    <span class="ml-2">Selected Products</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="apply_type" value="categories" 
                        {{ old('apply_type') == 'categories' ? 'checked' : '' }}
                        class="apply-type-radio w-4 h-4 text-blue-600">
                    <span class="ml-2">Selected Categories</span>
                </label>
            </div>

            <!-- Selected Products Section -->
            <div id="productsSection" class="{{ old('apply_type') == 'products' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Products</label>
                <div class="max-h-60 overflow-y-auto border border-gray-300 rounded-lg p-3 bg-white">
                    @foreach($products as $product)
                        <label class="flex items-center py-1 hover:bg-gray-50">
                            <input type="checkbox" name="selected_products[]" value="{{ $product->id }}"
                                {{ in_array($product->id, old('selected_products', [])) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 mr-2">
                            <span class="text-sm">{{ $product->name }} (৳{{ number_format($product->base_price, 2) }})</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Selected Categories Section -->
            <div id="categoriesSection" class="{{ old('apply_type') == 'categories' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Categories</label>
                <div class="max-h-60 overflow-y-auto border border-gray-300 rounded-lg p-3 bg-white">
                    @foreach($categories as $category)
                        <label class="flex items-center py-1 hover:bg-gray-50">
                            <input type="checkbox" name="selected_categories[]" value="{{ $category->id }}"
                                {{ in_array($category->id, old('selected_categories', [])) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 mr-2">
                            <span class="text-sm">{{ $category->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Active Status -->
        <div class="flex items-center gap-4 mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" checked 
                    class="w-4 h-4 text-blue-600 rounded">
                <span class="ml-2 text-sm">Active</span>
            </label>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center gap-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Create Campaign
            </button>
            <a href="{{ route('admin.campaigns.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Toggle sections based on apply type
    document.querySelectorAll('.apply-type-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('productsSection').classList.add('hidden');
            document.getElementById('categoriesSection').classList.add('hidden');
            
            if (this.value === 'products') {
                document.getElementById('productsSection').classList.remove('hidden');
            } else if (this.value === 'categories') {
                document.getElementById('categoriesSection').classList.remove('hidden');
            }
        });
    });
</script>
@endpush
