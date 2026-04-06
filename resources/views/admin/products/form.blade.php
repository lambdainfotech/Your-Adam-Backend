@extends('admin.layouts.master')

@section('title', isset($product) ? 'Edit Product' : 'Create Product')

@push('styles')
<style>
    /* Modern Tab Navigation */
    .product-tabs-wrapper {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    
    .product-tabs-nav {
        display: flex;
        background: linear-gradient(to bottom, #fafbfc, #f3f4f6);
        border-bottom: 1px solid #e5e7eb;
        padding: 0 4px;
        position: relative;
    }
    
    .product-tab {
        position: relative;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 14px 20px;
        font-size: 14px;
        font-weight: 500;
        color: #6b7280;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    
    .product-tab:hover {
        color: #374151;
        background: rgba(255,255,255,0.6);
    }
    
    .product-tab.active {
        color: #2563eb;
        background: #fff;
        border-bottom-color: #2563eb;
        font-weight: 600;
    }
    
    .product-tab.active::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: #2563eb;
    }
    
    .product-tab i {
        font-size: 16px;
        width: 20px;
        text-align: center;
    }
    
    .product-tab .tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        font-size: 11px;
        font-weight: 600;
        background: #e5e7eb;
        color: #6b7280;
        border-radius: 9px;
        margin-left: 4px;
    }
    
    .product-tab.active .tab-badge {
        background: #dbeafe;
        color: #2563eb;
    }
    
    /* Tab Content Panels */
    .product-tab-panel {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    
    .product-tab-panel.active {
        display: block;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Tab Content Styling */
    .tab-content-wrapper {
        padding: 24px;
        background: #fff;
    }
    
    .section-title {
        display: flex;
        align-items: center;
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .section-title i {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        border-radius: 8px;
        margin-right: 12px;
        font-size: 14px;
    }
    
    /* Form Card Styling */
    .form-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .form-card-title {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
    }
    
    .form-card-title i {
        margin-right: 8px;
        color: #6b7280;
    }
    
    /* Variant Preview Table */
    .variant-preview-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .variant-preview-table th,
    .variant-preview-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .variant-preview-table th {
        background: #f9fafb;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        color: #6b7280;
    }
    
    .variant-preview-table tr:hover {
        background: #f9fafb;
    }
    
    .variant-input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
    }
    
    .variant-input:focus {
        outline: none;
        border-color: #2563eb;
        ring: 2px;
        ring-color: #bfdbfe;
    }
    
    /* CKEditor Height */
    .ck-editor__editable {
        min-height: 250px !important;
    }
    .ck.ck-editor__main > .ck-editor__editable {
        min-height: 250px;
    }
    
    /* Responsive Tabs */
    @media (max-width: 768px) {
        .product-tabs-nav {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .product-tabs-nav::-webkit-scrollbar {
            display: none;
        }
        .product-tab {
            padding: 12px 16px;
            font-size: 13px;
        }
    }
    
    /* Attribute Selector */
    .attribute-selector-card {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .attribute-selector-card:hover {
        border-color: #bfdbfe;
        background: #f8fafc;
    }
    
    .attribute-selector-card.selected {
        border-color: #2563eb;
        background: #eff6ff;
    }
    
    .attribute-values-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 8px;
    }
    
    .value-tag {
        padding: 4px 10px;
        background: #dbeafe;
        color: #1e40af;
        border-radius: 4px;
        font-size: 12px;
    }
</style>
@endpush

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                {{ isset($product) ? 'Edit Product' : 'Create Product' }}
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                {{ isset($product) ? 'Update product details, variants, and inventory' : 'Add a new product to your catalog' }}
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.products.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
            <button type="submit" form="productForm" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>{{ isset($product) ? 'Update' : 'Create' }} Product
            </button>
        </div>
    </div>

    <form id="productForm" 
          action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @if(isset($product))
            @method('PUT')
        @endif

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Main Content -->
            <div class="flex-1">
                <!-- Product Type Selector -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Product Type</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all product-type-option {{ old('product_type', isset($product) ? $product->product_type : 'simple') === 'simple' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" name="product_type" value="simple" class="sr-only product-type-radio" 
                                {{ old('product_type', isset($product) ? $product->product_type : 'simple') === 'simple' ? 'checked' : '' }}>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-box text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">Simple Product</div>
                                    <div class="text-xs text-gray-500">Single SKU, no variations</div>
                                </div>
                            </div>
                        </label>
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all product-type-option {{ old('product_type', isset($product) ? $product->product_type : '') === 'variable' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" name="product_type" value="variable" class="sr-only product-type-radio"
                                {{ old('product_type', isset($product) ? $product->product_type : '') === 'variable' ? 'checked' : '' }}>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-boxes text-purple-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">Variable Product</div>
                                    <div class="text-xs text-gray-500">Multiple variants (size, color, etc.)</div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Modern Tab Navigation -->
                <div class="product-tabs-wrapper">
                    <nav class="product-tabs-nav">
                        <button type="button" class="product-tab active" data-tab="general">
                            <i class="fas fa-info-circle"></i>
                            <span>General</span>
                        </button>
                        <button type="button" class="product-tab" data-tab="inventory">
                            <i class="fas fa-warehouse"></i>
                            <span>Inventory</span>
                        </button>
                        <button type="button" class="product-tab {{ old('product_type', isset($product) ? $product->product_type : '') === 'variable' ? '' : 'hidden' }}" data-tab="variants" id="variantsTab">
                            <i class="fas fa-layer-group"></i>
                            <span>Variants</span>
                            <span class="tab-badge variant-count-badge hidden">0</span>
                        </button>
                        <button type="button" class="product-tab" data-tab="images">
                            <i class="fas fa-images"></i>
                            <span>Images</span>
                        </button>
                        <button type="button" class="product-tab" data-tab="seo">
                            <i class="fas fa-search"></i>
                            <span>SEO</span>
                        </button>
                    </nav>

                    <!-- Tab Panels -->
                    <div class="tab-content-wrapper">
                        <!-- General Tab -->
                        <div id="general" class="product-tab-panel active">
                            <div class="section-title">
                                <i class="fas fa-info-circle"></i>
                                <span>Basic Information</span>
                            </div>
                            
                            <div class="form-card">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="productName" value="{{ old('name', $product->name ?? '') }}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Enter product name" required>
                                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>

                            <!-- Description Section -->
                            <div class="form-card">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="text-sm font-medium text-gray-700">Description</label>
                                    <div class="flex items-center gap-2 text-sm">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="desc_source" value="predefined" 
                                                {{ old('desc_source', ($product->predefined_description_id ?? false) ? 'predefined' : '') == 'predefined' ? 'checked' : '' }}
                                                class="desc-source-radio text-blue-600">
                                            <span class="ml-1">Predefined</span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer ml-3">
                                            <input type="radio" name="desc_source" value="custom" 
                                                {{ old('desc_source', ($product->predefined_description_id ?? false) ? '' : 'custom') == 'custom' ? 'checked' : 'checked' }}
                                                class="desc-source-radio text-blue-600">
                                            <span class="ml-1">Custom</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Predefined Description Dropdown -->
                                <div id="predefinedDescSection" class="mb-3 {{ old('desc_source', ($product->predefined_description_id ?? false) ? 'predefined' : '') == 'predefined' ? '' : 'hidden' }}">
                                    <select name="predefined_description_id" id="predefinedDescriptionId" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select a predefined description...</option>
                                        @foreach($predefinedDescriptions as $desc)
                                            <option value="{{ $desc->id }}" 
                                                data-content="{{ e($desc->content) }}"
                                                {{ old('predefined_description_id', $product->predefined_description_id ?? '') == $desc->id ? 'selected' : '' }}>
                                                {{ $desc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Custom Description Editor -->
                                <textarea name="description" id="descriptionField" rows="4" 
                                    class="tinymce-editor w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Enter product description">{{ old('description', $product->description ?? '') }}</textarea>
                            </div>

                            <!-- Short Description Section -->
                            <div class="form-card">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="text-sm font-medium text-gray-700">Short Description</label>
                                    <div class="flex items-center gap-2 text-sm">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="short_desc_source" value="predefined" 
                                                {{ old('short_desc_source', ($product->predefined_short_description_id ?? false) ? 'predefined' : '') == 'predefined' ? 'checked' : '' }}
                                                class="short-desc-source-radio text-blue-600">
                                            <span class="ml-1">Predefined</span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer ml-3">
                                            <input type="radio" name="short_desc_source" value="custom" 
                                                {{ old('short_desc_source', ($product->predefined_short_description_id ?? false) ? '' : 'custom') == 'custom' ? 'checked' : 'checked' }}
                                                class="short-desc-source-radio text-blue-600">
                                            <span class="ml-1">Custom</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Predefined Short Description Dropdown -->
                                <div id="predefinedShortDescSection" class="mb-3 {{ old('short_desc_source', ($product->predefined_short_description_id ?? false) ? 'predefined' : '') == 'predefined' ? '' : 'hidden' }}">
                                    <select name="predefined_short_description_id" id="predefinedShortDescriptionId" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select a predefined short description...</option>
                                        @foreach($predefinedShortDescriptions as $desc)
                                            <option value="{{ $desc->id }}" 
                                                data-content="{{ e($desc->content) }}"
                                                {{ old('predefined_short_description_id', $product->predefined_short_description_id ?? '') == $desc->id ? 'selected' : '' }}>
                                                {{ $desc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Custom Short Description Textarea -->
                                <textarea name="short_description" id="shortDescriptionField" rows="2" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Brief product summary">{{ old('short_description', $product->short_description ?? '') }}</textarea>
                            </div>

                            <div class="form-card">
                                <div class="form-card-title">
                                    <i class="fas fa-tag"></i>
                                    <span>Pricing</span>
                                </div>
                                
                                <!-- Cost, Regular Price Row -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Cost per Item (৳)</label>
                                        <input type="number" name="cost_price" id="costPrice" step="0.01" min="0"
                                            value="{{ old('cost_price', $product->cost_price ?? '') }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            placeholder="0.00">
                                        <p class="text-xs text-gray-500 mt-1">Your cost for profit calculations</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Regular Price (৳) <span class="text-red-500">*</span></label>
                                        <input type="number" name="base_price" id="basePrice" step="0.01" min="0"
                                            value="{{ old('base_price', $product->base_price ?? '') }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            placeholder="0.00" required>
                                    </div>
                                </div>

                                <!-- Discount Section -->
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Discount</label>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <!-- Discount Type -->
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Discount Type</label>
                                            <div class="flex gap-4">
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="discount_type" value="percentage" 
                                                        {{ old('discount_type', $product->discount_type ?? '') == 'percentage' ? 'checked' : '' }}
                                                        class="discount-type-radio text-blue-600">
                                                    <span class="ml-2 text-sm">Percentage (%)</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="discount_type" value="flat"
                                                        {{ old('discount_type', $product->discount_type ?? '') == 'flat' ? 'checked' : '' }}
                                                        class="discount-type-radio text-blue-600">
                                                    <span class="ml-2 text-sm">Flat (৳)</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="discount_type" value=""
                                                        {{ old('discount_type', $product->discount_type ?? '') == '' ? 'checked' : 'checked' }}
                                                        class="discount-type-radio text-blue-600">
                                                    <span class="ml-2 text-sm">No Discount</span>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <!-- Discount Value -->
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Discount Value</label>
                                            <input type="number" name="discount_value" id="discountValue" step="0.01" min="0"
                                                value="{{ old('discount_value', $product->discount_value ?? '') }}"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                                placeholder="0">
                                            <p class="text-xs text-gray-500 mt-1" id="discountHint">Enter percentage (1-99) or flat amount</p>
                                        </div>
                                        
                                        <!-- Sale Price (Auto-calculated, Read-only) -->
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Sale Price <span class="text-gray-400">(Auto-calculated)</span></label>
                                            <div class="relative">
                                                <input type="number" name="sale_price" id="salePrice" step="0.01" min="0"
                                                    value="{{ old('sale_price', $product->sale_price ?? '') }}"
                                                    class="w-full px-4 py-2 border border-gray-200 bg-gray-50 rounded-lg text-gray-600"
                                                    placeholder="0.00" readonly>
                                                <span id="discountBadge" class="absolute right-3 top-2 text-xs font-medium {{ ($product->discount_value ?? 0) > 0 ? '' : 'hidden' }}">
                                                    @if(($product->discount_type ?? '') == 'percentage')
                                                        <span class="text-green-600">-{{ $product->discount_value }}%</span>
                                                    @elseif(($product->discount_type ?? '') == 'flat')
                                                        <span class="text-green-600">-৳{{ number_format($product->discount_value, 2) }}</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1" id="savingsText">
                                                @if(($product->discount_value ?? 0) > 0 && ($product->base_price ?? 0) > 0)
                                                    Save ৳{{ number_format($product->discount_amount, 2) }}
                                                @else
                                                    No discount applied
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sale Schedule -->
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="text-sm font-medium text-gray-700">Sale Schedule <span class="text-gray-400 font-normal">(Optional)</span></label>
                                        <button type="button" id="toggleSaleSchedule" class="text-sm text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-calendar-alt mr-1"></i>Toggle Schedule
                                        </button>
                                    </div>
                                    <div class="sale-schedule hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <input type="datetime-local" name="sale_start_date"
                                                value="{{ old('sale_start_date', isset($product) && $product->sale_start_date ? $product->sale_start_date->format('Y-m-d\TH:i') : '') }}"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <p class="text-xs text-gray-500 mt-1">Sale Start Date</p>
                                        </div>
                                        <div>
                                            <input type="datetime-local" name="sale_end_date"
                                                value="{{ old('sale_end_date', isset($product) && $product->sale_end_date ? $product->sale_end_date->format('Y-m-d\TH:i') : '') }}"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <p class="text-xs text-gray-500 mt-1">Sale End Date</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-card">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                                <select name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Inventory Tab -->
                        <div id="inventory" class="product-tab-panel">
                            <!-- SKU Section -->
                            <div class="form-card">
                                <div class="form-card-title">
                                    <i class="fas fa-barcode"></i>
                                    <span>SKU Configuration</span>
                                </div>
                                
                                <div id="simpleSkuSection" class="{{ old('product_type', isset($product) ? $product->product_type : 'simple') === 'simple' ? '' : 'hidden' }}">
                                    <div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <div class="flex items-start gap-2">
                                            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                                            <div class="text-sm text-blue-700">
                                                <p class="font-medium mb-1">Auto-Generation Enabled</p>
                                                <p class="mb-1">• SKU Format: <code>CATEGORY-YYYYMMDD-XXXX</code></p>
                                                <p>• Barcode Format: <code>200XXXXXXXXXC</code> (EAN-13)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">SKU <span class="text-xs text-gray-500 font-normal">(Optional)</span></label>
                                            <input type="text" name="sku" 
                                                value="{{ old('sku', $product->sku ?? '') }}"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                                placeholder="Leave empty for auto-generation">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Barcode <span class="text-xs text-gray-500 font-normal">(Optional)</span></label>
                                            <input type="text" name="barcode" 
                                                value="{{ old('barcode', $product->barcode ?? '') }}"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                                placeholder="Leave empty for auto-generation">
                                        </div>
                                    </div>
                                </div>

                                <div id="variableSkuSection" class="{{ old('product_type', isset($product) ? $product->product_type : '') === 'variable' ? '' : 'hidden' }}">
                                    <div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <div class="flex items-start gap-2">
                                            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                                            <div class="text-sm text-blue-700">
                                                <p class="font-medium mb-1">Auto-Generation for Variants</p>
                                                <p class="mb-1">• SKU Prefix: Auto-generated from category if empty</p>
                                                <p class="mb-1">• Variant SKU: <code>PREFIX-XXXX-XXX-ATTR</code></p>
                                                <p>• Variant Barcode: <code>200{PRODUCT_ID}{SEQ}C</code> (Related to product)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU Prefix <span class="text-xs text-gray-500 font-normal">(Optional)</span></label>
                                        <input type="text" name="sku_prefix" id="skuPrefix"
                                            value="{{ old('sku_prefix', $product->sku_prefix ?? '') }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            placeholder="Leave empty for auto-generation (e.g., SHIRT)">
                                        <p class="text-xs text-gray-500 mt-1">Each variant SKU will be: PREFIX-PROD_ID-VARIANT_SEQ-ATTR</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Stock Management (Simple Product Only) -->
                            <div id="simpleStockSection" class="form-card {{ old('product_type', isset($product) ? $product->product_type : 'simple') === 'simple' ? '' : 'hidden' }}">
                                <div class="form-card-title">
                                    <i class="fas fa-boxes"></i>
                                    <span>Stock Management</span>
                                </div>
                                
                                <div class="flex items-center mb-4">
                                    <input type="checkbox" name="manage_stock" id="manage_stock" value="1" 
                                        {{ old('manage_stock', $product->manage_stock ?? true) ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <label for="manage_stock" class="ml-2 text-sm font-medium text-gray-700">Manage Stock</label>
                                </div>

                                <div id="stockFields" class="grid grid-cols-1 md:grid-cols-3 gap-4 {{ old('manage_stock', $product->manage_stock ?? true) ? '' : 'opacity-50' }}">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                                        <input type="number" name="stock_quantity" min="0"
                                            value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            {{ old('manage_stock', $product->manage_stock ?? true) ? '' : 'disabled' }}>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
                                        <select name="stock_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="in_stock" {{ old('stock_status', $product->stock_status ?? '') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                            <option value="out_of_stock" {{ old('stock_status', $product->stock_status ?? '') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                            <option value="on_backorder" {{ old('stock_status', $product->stock_status ?? '') === 'on_backorder' ? 'selected' : '' }}>On Backorder</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Low Stock Threshold</label>
                                        <input type="number" name="low_stock_threshold" min="0"
                                            value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 10) }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <p class="text-xs text-gray-500 mt-1">Alert when stock falls below</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Stock Management Info (Variable Product) -->
                            <div id="variableStockSection" class="form-card {{ old('product_type', isset($product) ? $product->product_type : '') === 'variable' ? '' : 'hidden' }}">
                                <div class="form-card-title">
                                    <i class="fas fa-boxes"></i>
                                    <span>Stock Management</span>
                                </div>
                                
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                    <div class="flex items-start">
                                        <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                                        <div>
                                            <h4 class="font-medium text-blue-900">Variant-Level Stock Management</h4>
                                            <p class="text-sm text-blue-800 mt-1">
                                                For variable products, stock is managed individually for each variant. Go to the <strong>Variants</strong> tab to configure.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>

                        <!-- Variants Tab - WITH GENERATION DURING CREATION -->
                        <div id="variants" class="product-tab-panel">
                            <div class="section-title">
                                <i class="fas fa-layer-group"></i>
                                <span>Product Variants</span>
                            </div>
                            
                            @if($attributes->count() > 0)
                                <!-- Step 1: Select Attributes -->
                                <div class="form-card" id="variantAttributesSelector">
                                    <div class="form-card-title">
                                        <i class="fas fa-check-square"></i>
                                        <span>Step 1: Select Attributes</span>
                                    </div>
                                    <p class="text-gray-500 text-sm mb-4">Choose attributes to create variant combinations:</p>
                                    
                                    <div class="space-y-3">
                                        @foreach($attributes as $attribute)
                                            <div class="attribute-selector-card" data-attribute-id="{{ $attribute->id }}" onclick="toggleAttributeSelection(this)">
                                                <div class="flex items-start">
                                                    <input type="checkbox" name="attributes[]" value="{{ $attribute->id }}" class="attribute-checkbox mt-1 mr-3 h-4 w-4 text-blue-600" {{ in_array($attribute->id, old('attributes', $selectedAttributeIds ?? [])) ? 'checked' : '' }}>
                                                    <div class="flex-1">
                                                        <div class="flex items-center justify-between">
                                                            <span class="font-medium text-gray-900">{{ $attribute->name }}</span>
                                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $attribute->values->count() }} values</span>
                                                        </div>
                                                        <p class="text-sm text-gray-500 mt-1">{{ $attribute->code }}</p>
                                                        <div class="attribute-values-preview">
                                                            @foreach($attribute->values as $value)
                                                                <span class="value-tag">{{ $value->value }}</span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <div class="mt-4 flex justify-end">
                                        <button type="button" onclick="generateVariantPreview()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                            <i class="fas fa-magic mr-2"></i>Generate Variants
                                        </button>
                                    </div>
                                </div>

                                <!-- Step 2: Variant Preview & Configuration -->
                                <div class="form-card hidden" id="variantConfiguration">
                                    <div class="form-card-title">
                                        <i class="fas fa-edit"></i>
                                        <span>Step 2: Configure Variants</span>
                                    </div>
                                    
                                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Base Price: ৳<span id="displayBasePrice">0.00</span></span>
                                            <button type="button" onclick="applyBasePriceToAll()" class="text-sm text-blue-600 hover:text-blue-800">
                                                Apply to all variants
                                            </button>
                                        </div>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="variant-preview-table">
                                            <thead>
                                                <tr>
                                                    <th>Variant</th>
                                                    <th>SKU</th>
                                                    <th>Price (৳)</th>
                                                    <th>Stock Qty</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="variantPreviewBody">
                                                <!-- Generated by JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="mt-4 flex justify-between">
                                        <button type="button" onclick="backToAttributeSelection()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                            <i class="fas fa-arrow-left mr-2"></i>Back to Attributes
                                        </button>
                                        <button type="button" onclick="regenerateVariants()" class="px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                                            <i class="fas fa-redo mr-2"></i>Regenerate
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="form-card text-center py-12">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-tags text-2xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Attributes Found</h3>
                                    <p class="text-gray-500 mb-4">Create attributes first to generate variants.</p>
                                    <a href="{{ route('admin.attributes.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        <i class="fas fa-plus mr-2"></i>Create Attributes
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Images Tab -->
                        <div id="images" class="product-tab-panel">
                            <div class="section-title">
                                <i class="fas fa-images"></i>
                                <span>Product Images</span>
                            </div>
                            
                            <div class="form-card">
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 transition-colors">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                                    <p class="text-gray-600 mb-2">Drag and drop images here, or click to browse</p>
                                    <p class="text-gray-400 text-sm mb-4">Supports: JPG, PNG, WebP (Max 5MB each)</p>
                                    <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" 
                                        class="hidden" id="imageInput">
                                    <button type="button" onclick="document.getElementById('imageInput').click()" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        Select Images
                                    </button>
                                </div>

                                <div id="imagePreviewContainer" class="grid grid-cols-4 gap-4 mt-4">
                                    @if(isset($product) && $product->images->count() > 0)
                                        @foreach($product->images as $image)
                                            <div class="relative group">
                                                <img src="{{ $image->full_image_url }}" class="w-full h-32 object-cover rounded-lg">
                                                @if($image->is_main)
                                                    <span class="absolute top-2 left-2 px-2 py-1 bg-blue-600 text-white text-xs rounded">Main</span>
                                                @endif
                                                <button type="button" class="absolute top-2 right-2 w-8 h-8 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- SEO Tab -->
                        <div id="seo" class="product-tab-panel">
                            <div class="section-title">
                                <i class="fas fa-search"></i>
                                <span>Search Engine Optimization</span>
                            </div>
                            
                            <div class="form-card">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">SEO Title</label>
                                        <input type="text" name="seo_title" 
                                            value="{{ old('seo_title', $product->seo_title ?? '') }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            placeholder="Page title for search engines">
                                        <p class="text-xs text-gray-500 mt-1">Recommended: 50-60 characters</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">SEO Description</label>
                                        <textarea name="seo_description" rows="3"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            placeholder="Meta description for search engines">{{ old('seo_description', $product->seo_description ?? '') }}</textarea>
                                        <p class="text-xs text-gray-500 mt-1">Recommended: 150-160 characters</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">URL Slug</label>
                                        <div class="flex items-center">
                                            <span class="px-3 py-2 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg text-gray-500 text-sm">
                                                {{ url('/products/') }}/
                                            </span>
                                            <input type="text" name="slug" 
                                                value="{{ old('slug', $product->slug ?? '') }}"
                                                class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-blue-500"
                                                placeholder="product-url-slug">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="w-full lg:w-80 space-y-6">
                <!-- Publish Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4"><i class="fas fa-publish mr-2"></i>Publish</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Status</span>
                            <select name="is_active" class="text-sm border border-gray-300 rounded px-2 py-1">
                                <option value="1" {{ old('is_active', $product->is_active ?? true) ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active', $product->is_active ?? true) ? '' : 'selected' }}>Draft</option>
                            </select>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_featured" id="is_featured" value="1"
                                {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="is_featured" class="ml-2 text-sm text-gray-700">Featured Product</label>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-200 flex space-x-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ isset($product) ? 'Update' : 'Publish' }}
                        </button>
                    </div>
                </div>

                @if(isset($product))
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4"><i class="fas fa-chart-bar mr-2"></i>Quick Stats</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Product ID</span>
                            <span class="text-sm font-medium">#{{ $product->id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Type</span>
                            <span class="text-sm font-medium capitalize">{{ $product->product_type }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Total Stock</span>
                            <span class="text-sm font-medium">{{ $product->total_stock }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
<script>
    // Store attributes data for JavaScript
    const attributesData = @json($attributes->keyBy('id'));
    let generatedVariants = [];

    // Tab switching
    document.querySelectorAll('.product-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = this.dataset.tab;
            document.querySelectorAll('.product-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.product-tab-panel').forEach(p => p.classList.remove('active'));
            document.getElementById(targetId).classList.add('active');
        });
    });

    // Product type selector
    document.querySelectorAll('.product-type-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            const isVariable = this.value === 'variable';
            
            document.querySelectorAll('.product-type-option').forEach(opt => {
                opt.classList.remove('border-blue-500', 'bg-blue-50');
                opt.classList.add('border-gray-200');
            });
            this.closest('.product-type-option').classList.remove('border-gray-200');
            this.closest('.product-type-option').classList.add('border-blue-500', 'bg-blue-50');
            
            document.getElementById('simpleSkuSection').classList.toggle('hidden', isVariable);
            document.getElementById('variableSkuSection').classList.toggle('hidden', !isVariable);
            document.getElementById('simpleStockSection').classList.toggle('hidden', isVariable);
            document.getElementById('variableStockSection').classList.toggle('hidden', !isVariable);
            
            const variantsTab = document.getElementById('variantsTab');
            if (isVariable) {
                variantsTab.classList.remove('hidden');
            } else {
                variantsTab.classList.add('hidden');
                if (document.querySelector('.product-tab[data-tab="variants"]').classList.contains('active')) {
                    document.querySelector('[data-tab="general"]').click();
                }
            }
        });
    });

    // Toggle attribute selection
    function toggleAttributeSelection(card) {
        const checkbox = card.querySelector('.attribute-checkbox');
        checkbox.checked = !checkbox.checked;
        card.classList.toggle('selected', checkbox.checked);
    }

    // Initialize selected attributes
    document.querySelectorAll('.attribute-checkbox:checked').forEach(cb => {
        cb.closest('.attribute-selector-card').classList.add('selected');
    });

    // Generate variant preview
    function generateVariantPreview() {
        const selectedAttributes = [];
        document.querySelectorAll('.attribute-checkbox:checked').forEach(cb => {
            const attrId = cb.value;
            const attr = attributesData[attrId];
            if (attr && attr.values) {
                selectedAttributes.push({
                    id: attrId,
                    name: attr.name,
                    values: attr.values
                });
            }
        });

        if (selectedAttributes.length === 0) {
            alert('Please select at least one attribute');
            return;
        }

        // Generate all combinations
        const combinations = generateCombinations(selectedAttributes);
        
        // Update badge
        document.querySelector('.variant-count-badge').textContent = combinations.length;
        document.querySelector('.variant-count-badge').classList.remove('hidden');

        // Generate table rows
        const tbody = document.getElementById('variantPreviewBody');
        tbody.innerHTML = '';
        generatedVariants = [];

        const basePrice = parseFloat(document.getElementById('basePrice').value) || 0;
        const skuPrefix = document.getElementById('skuPrefix').value || 'SKU';

        combinations.forEach((combo, index) => {
            const variantName = combo.map(c => c.value).join(' / ');
            const sku = generateVariantSku(skuPrefix, combo, index);
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="font-medium text-gray-900">${variantName}</div>
                    <div class="text-xs text-gray-500">${combo.map(c => c.attrName + ': ' + c.value).join(', ')}</div>
                    <input type="hidden" name="variants[${index}][attribute_values]" value="${combo.map(c => c.valueId).join(',')}">
                </td>
                <td>
                    <input type="text" name="variants[${index}][sku]" value="${sku}" class="variant-input" required>
                </td>
                <td>
                    <input type="number" name="variants[${index}][price]" value="${basePrice.toFixed(2)}" step="0.01" min="0" class="variant-input variant-price" required>
                </td>
                <td>
                    <input type="number" name="variants[${index}][stock_quantity]" value="0" min="0" class="variant-input variant-stock" required>
                </td>
                <td>
                    <select name="variants[${index}][is_active]" class="variant-input">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </td>
            `;
            tbody.appendChild(row);

            generatedVariants.push({
                index,
                name: variantName,
                sku,
                combo
            });
        });

        // Show configuration panel
        document.getElementById('variantAttributesSelector').classList.add('hidden');
        document.getElementById('variantConfiguration').classList.remove('hidden');
        document.getElementById('displayBasePrice').textContent = basePrice.toFixed(2);
    }

    // Generate all combinations of attribute values
    function generateCombinations(attributes) {
        if (attributes.length === 0) return [];
        if (attributes.length === 1) {
            return attributes[0].values.map(v => [{ 
                attrId: attributes[0].id, 
                attrName: attributes[0].name, 
                valueId: v.id, 
                value: v.value 
            }]);
        }

        const first = attributes[0];
        const rest = generateCombinations(attributes.slice(1));
        const result = [];

        for (const value of first.values) {
            for (const combo of rest) {
                result.push([{ 
                    attrId: first.id, 
                    attrName: first.name, 
                    valueId: value.id, 
                    value: value.value 
                }, ...combo]);
            }
        }

        return result;
    }

    // Generate SKU for variant
    function generateVariantSku(prefix, combo, index) {
        const valueCodes = combo.map(c => c.value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 3));
        return prefix + '-' + valueCodes.join('-');
    }

    // Apply base price to all variants
    function applyBasePriceToAll() {
        const basePrice = document.getElementById('basePrice').value || 0;
        document.querySelectorAll('.variant-price').forEach(input => {
            input.value = parseFloat(basePrice).toFixed(2);
        });
    }

    // Back to attribute selection
    function backToAttributeSelection() {
        document.getElementById('variantConfiguration').classList.add('hidden');
        document.getElementById('variantAttributesSelector').classList.remove('hidden');
    }

    // Regenerate variants
    function regenerateVariants() {
        if (confirm('This will reset all variant configurations. Continue?')) {
            generateVariantPreview();
        }
    }

    // Sale schedule toggle
    document.getElementById('toggleSaleSchedule')?.addEventListener('click', function() {
        document.querySelectorAll('.sale-schedule').forEach(el => {
            el.classList.toggle('hidden');
        });
        this.classList.toggle('text-blue-600');
    });

    // Auto-generate slug from name
    document.getElementById('productName')?.addEventListener('blur', function() {
        const slugInput = document.querySelector('input[name="slug"]');
        if (!slugInput.value) {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
        }
    });

    // Image preview
    document.getElementById('imageInput')?.addEventListener('change', function(e) {
        const container = document.getElementById('imagePreviewContainer');
        Array.from(e.target.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative group';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg">
                    <button type="button" class="absolute top-2 right-2 w-8 h-8 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                container.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });

    // Manage stock toggle
    document.getElementById('manage_stock')?.addEventListener('change', function() {
        const stockFields = document.getElementById('stockFields');
        const inputs = stockFields.querySelectorAll('input, select');
        if (this.checked) {
            stockFields.classList.remove('opacity-50');
            inputs.forEach(input => input.disabled = false);
        } else {
            stockFields.classList.add('opacity-50');
            inputs.forEach(input => input.disabled = true);
        }
    });

    // ========== DISCOUNT CALCULATION ==========
    function calculateSalePrice() {
        const basePrice = parseFloat(document.getElementById('basePrice')?.value) || 0;
        const discountType = document.querySelector('input[name="discount_type"]:checked')?.value;
        const discountValue = parseFloat(document.getElementById('discountValue')?.value) || 0;
        const salePriceInput = document.getElementById('salePrice');
        const discountBadge = document.getElementById('discountBadge');
        const savingsText = document.getElementById('savingsText');
        const discountHint = document.getElementById('discountHint');
        
        // Update hint based on discount type
        if (discountType === 'percentage') {
            discountHint.textContent = 'Enter percentage (1-99%)';
        } else if (discountType === 'flat') {
            discountHint.textContent = 'Enter flat amount in dollars';
        } else {
            discountHint.textContent = 'Select a discount type first';
        }
        
        // Calculate sale price
        let salePrice = basePrice;
        let savings = 0;
        
        if (discountValue > 0 && discountType) {
            if (discountType === 'percentage') {
                // Limit to 1-99%
                const percentage = Math.min(99, Math.max(1, discountValue));
                savings = basePrice * (percentage / 100);
                salePrice = basePrice - savings;
            } else if (discountType === 'flat') {
                // Ensure flat discount is less than base price
                savings = Math.min(discountValue, basePrice - 0.01);
                salePrice = basePrice - savings;
            }
        }
        
        // Ensure non-negative
        salePrice = Math.max(0, salePrice);
        
        // Update sale price input
        salePriceInput.value = salePrice.toFixed(2);
        
        // Update discount badge
        if (discountValue > 0 && discountType && basePrice > 0) {
            discountBadge.classList.remove('hidden');
            if (discountType === 'percentage') {
                discountBadge.innerHTML = `<span class="text-green-600">-${discountValue}%</span>`;
            } else {
                discountBadge.innerHTML = `<span class="text-green-600">-৳{discountValue.toFixed(2)}</span>`;
            }
        } else {
            discountBadge.classList.add('hidden');
        }
        
        // Update savings text
        if (savings > 0) {
            savingsText.innerHTML = `<span class="text-green-600 font-medium">Save ৳{savings.toFixed(2)}</span>`;
        } else {
            savingsText.textContent = 'No discount applied';
        }
    }
    
    // Add event listeners for discount calculation
    document.getElementById('basePrice')?.addEventListener('input', calculateSalePrice);
    document.getElementById('discountValue')?.addEventListener('input', calculateSalePrice);
    
    // Radio button change listeners
    document.querySelectorAll('input[name="discount_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const discountValueInput = document.getElementById('discountValue');
            if (this.value === '') {
                discountValueInput.value = '';
                discountValueInput.disabled = true;
            } else {
                discountValueInput.disabled = false;
            }
            calculateSalePrice();
        });
    });
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        calculateSalePrice();
        
        // Disable discount value if no discount type selected
        const selectedType = document.querySelector('input[name="discount_type"]:checked')?.value;
        const discountValueInput = document.getElementById('discountValue');
        if (selectedType === '' || !selectedType) {
            discountValueInput.disabled = true;
        }
    });

    // ========== PREDEFINED DESCRIPTIONS ==========
    
    // Description source toggle
    document.querySelectorAll('input[name="desc_source"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const predefinedSection = document.getElementById('predefinedDescSection');
            const predefinedSelect = document.getElementById('predefinedDescriptionId');
            
            if (this.value === 'predefined') {
                predefinedSection.classList.remove('hidden');
                // Set CKEditor to readonly mode
                if (descriptionEditor) {
                    descriptionEditor.enableReadOnlyMode('predefined');
                    // Auto-populate if a value is already selected
                    if (predefinedSelect.value) {
                        const selectedOption = predefinedSelect.options[predefinedSelect.selectedIndex];
                        descriptionEditor.setData(selectedOption.dataset.content || '');
                    }
                }
            } else {
                predefinedSection.classList.add('hidden');
                // Set CKEditor to editable mode
                if (descriptionEditor) {
                    descriptionEditor.disableReadOnlyMode('predefined');
                }
                predefinedSelect.value = '';
            }
        });
    });
    
    // Predefined description selection
    document.getElementById('predefinedDescriptionId')?.addEventListener('change', function() {
        if (descriptionEditor) {
            const selectedOption = this.options[this.selectedIndex];
            descriptionEditor.setData(selectedOption.dataset.content || '');
        }
    });
    
    // Short description source toggle
    document.querySelectorAll('input[name="short_desc_source"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const predefinedSection = document.getElementById('predefinedShortDescSection');
            const shortDescField = document.getElementById('shortDescriptionField');
            const predefinedSelect = document.getElementById('predefinedShortDescriptionId');
            
            if (this.value === 'predefined') {
                predefinedSection.classList.remove('hidden');
                shortDescField.readOnly = true;
                shortDescField.classList.add('bg-gray-100');
                // Auto-populate if a value is already selected
                if (predefinedSelect.value) {
                    const selectedOption = predefinedSelect.options[predefinedSelect.selectedIndex];
                    shortDescField.value = selectedOption.dataset.content || '';
                }
            } else {
                predefinedSection.classList.add('hidden');
                shortDescField.readOnly = false;
                shortDescField.classList.remove('bg-gray-100');
                predefinedSelect.value = '';
            }
        });
    });
    
    // Predefined short description selection
    document.getElementById('predefinedShortDescriptionId')?.addEventListener('change', function() {
        const shortDescField = document.getElementById('shortDescriptionField');
        const selectedOption = this.options[this.selectedIndex];
        shortDescField.value = selectedOption.dataset.content || '';
    });
    
    // Initialize description states on page load
    function initializeDescriptionStates() {
        const descSource = document.querySelector('input[name="desc_source"]:checked')?.value;
        const shortDescSource = document.querySelector('input[name="short_desc_source"]:checked')?.value;
        const shortDescField = document.getElementById('shortDescriptionField');
        
        if (descSource === 'predefined') {
            document.getElementById('predefinedDescSection').classList.remove('hidden');
        }
        
        if (shortDescSource === 'predefined') {
            document.getElementById('predefinedShortDescSection').classList.remove('hidden');
            shortDescField.readOnly = true;
            shortDescField.classList.add('bg-gray-100');
        }
    }
    
    // Call after CKEditor is initialized
    document.addEventListener('DOMContentLoaded', function() {
        // Wait a bit for CKEditor to initialize
        setTimeout(initializeDescriptionStates, 500);
    });

    // Initialize CKEditor
    let descriptionEditor;
    
    ClassicEditor
        .create(document.querySelector('#descriptionField'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'underline', 'link', '|', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
        })
        .then(editor => {
            descriptionEditor = editor;
            
            // Check initial state
            const descSource = document.querySelector('input[name="desc_source"]:checked')?.value;
            if (descSource === 'predefined') {
                editor.enableReadOnlyMode('predefined');
            }
        })
        .catch(error => {
            console.error(error);
        });
    
    // Sync CKEditor content before form submission
    document.getElementById('productForm').addEventListener('submit', function(e) {
        if (descriptionEditor) {
            document.querySelector('#descriptionField').value = descriptionEditor.getData();
        }
    });
    
    // Update description editor when predefined is selected
    document.getElementById('predefinedDescriptionId')?.addEventListener('change', function() {
        if (descriptionEditor) {
            const selectedOption = this.options[this.selectedIndex];
            const content = selectedOption.dataset.content || '';
            descriptionEditor.setContent(content);
        }
    });
</script>
@endpush
