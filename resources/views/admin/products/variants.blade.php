@extends('admin.layouts.master')

@section('title', $product->name . ' - Variants')

@push('styles')
<style>
    .variant-card {
        transition: all 0.2s;
    }
    .variant-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .attribute-selector {
        max-height: 200px;
        overflow-y: auto;
    }
    .variant-row {
        transition: background-color 0.2s;
    }
    .variant-row:hover {
        background-color: #f9fafb;
    }
    .inline-edit {
        border: 1px solid transparent;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .inline-edit:hover {
        border-color: #d1d5db;
        background-color: white;
    }
    .inline-edit:focus {
        border-color: #3b82f6;
        outline: none;
        background-color: white;
    }
    .sortable-ghost {
        opacity: 0.5;
        background-color: #e5e7eb;
    }
    .combination-preview {
        max-height: 300px;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-start mb-6">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.products.index') }}" class="hover:text-blue-600">Products</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <a href="{{ route('admin.products.edit', $product) }}" class="hover:text-blue-600">{{ Str::limit($product->name, 30) }}</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <span>Variants</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Product Variants</h1>
            <p class="text-gray-500 text-sm mt-1">Manage variations for {{ $product->name }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.products.edit', $product) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Back to Product
            </a>
            <button type="button" onclick="openGenerateModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-magic mr-2"></i>Generate Variants
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Variant Management -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="text-2xl font-bold text-gray-900">{{ $product->variants->count() }}</div>
                    <div class="text-sm text-gray-500">Total Variants</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="text-2xl font-bold text-green-600">{{ $product->variants->where('is_active', true)->count() }}</div>
                    <div class="text-sm text-gray-500">Active</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="text-2xl font-bold text-blue-600">
                        {{ $product->variants->sum('stock_quantity') }}
                    </div>
                    <div class="text-sm text-gray-500">Total Stock</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="text-2xl font-bold text-purple-600">
                        @if($priceRange['has_range'])
                            ৳{{ number_format($priceRange['min'], 2) }} - ৳{{ number_format($priceRange['max'], 2) }}
                        @else
                            ৳{{ number_format($priceRange['min'], 2) }}
                        @endif
                    </div>
                    <div class="text-sm text-gray-500">Price Range</div>
                </div>
            </div>

            <!-- Variants Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-list mr-2"></i>All Variants</h3>
                    <div class="flex space-x-2">
                        <button type="button" onclick="bulkToggleStatus(true)" class="text-sm px-3 py-1 text-green-600 hover:bg-green-50 rounded">
                            <i class="fas fa-check mr-1"></i>Activate All
                        </button>
                        <button type="button" onclick="bulkToggleStatus(false)" class="text-sm px-3 py-1 text-gray-600 hover:bg-gray-50 rounded">
                            <i class="fas fa-ban mr-1"></i>Deactivate All
                        </button>
                    </div>
                </div>

                @if($product->variants->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                                        <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variant</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">WS %</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="variantsTableBody" class="divide-y divide-gray-200">
                                @foreach($product->variants as $variant)
                                <tr class="variant-row" data-variant-id="{{ $variant->id }}">
                                    <td class="px-4 py-3">
                                        <input type="checkbox" class="variant-checkbox rounded border-gray-300" value="{{ $variant->id }}">
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            @if($variant->mainImage)
                                                <img src="{{ $variant->mainImage->full_thumbnail_url }}" class="w-10 h-10 rounded object-cover mr-3">
                                            @else
                                                <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center mr-3">
                                                    <i class="fas fa-image text-gray-400"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $variant->attribute_text_short }}</div>
                                                <div class="text-xs text-gray-500">{{ $variant->attribute_text }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-sm">{{ $variant->sku }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="inline-edit" contenteditable="true" 
                                            onblur="updateVariantField({{ $variant->id }}, 'price', this)"
                                            data-original="{{ $variant->price }}">
                                            ৳{{ number_format($variant->price ?: $product->base_price, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="inline-edit w-16 text-center" contenteditable="true" 
                                            onblur="updateVariantField({{ $variant->id }}, 'wholesale_percentage', this)"
                                            data-original="{{ $variant->wholesale_percentage }}">
                                            {{ $variant->wholesale_percentage ? $variant->wholesale_percentage . '%' : ($product->wholesale_percentage ? $product->wholesale_percentage . '%' : '-') }}
                                        </div>
                                        @if($variant->effective_wholesale_price)
                                            <div class="text-xs text-green-600 mt-1">
                                                ৳{{ number_format($variant->effective_wholesale_price, 2) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-edit w-16 text-center" contenteditable="true"
                                                onblur="updateVariantField({{ $variant->id }}, 'stock_quantity', this)"
                                                data-original="{{ $variant->stock_quantity }}">
                                                {{ $variant->stock_quantity }}
                                            </span>
                                            @if($variant->is_low_stock)
                                                <span class="stock-badge bg-yellow-100 text-yellow-800">Low</span>
                                            @elseif($variant->stock_quantity <= 0)
                                                <span class="stock-badge bg-red-100 text-red-800">Out</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <button type="button" onclick="toggleVariantStatus({{ $variant->id }}, this)" 
                                            class="stock-badge {{ $variant->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $variant->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button type="button" onclick="editVariant({{ $variant->id }})" 
                                                class="text-blue-600 hover:text-blue-800" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" onclick="deleteVariant({{ $variant->id }})" 
                                                class="text-red-600 hover:text-red-800" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-layer-group text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Variants Yet</h3>
                        <p class="text-gray-500 mb-4">Generate variants based on product attributes</p>
                        <button type="button" onclick="openGenerateModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-magic mr-2"></i>Generate Variants
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Attributes Configuration -->
        <div class="space-y-6">
            <!-- Current Attributes -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4"><i class="fas fa-tags mr-2"></i>Selected Attributes</h3>
                
                @if($product->productAttributes->count() > 0)
                    <div class="space-y-3">
                        @foreach($product->productAttributes as $prodAttr)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-tag text-blue-500 mr-3"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $prodAttr->attribute->name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $prodAttr->attribute->values->count() }} values available
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No attributes selected</p>
                @endif

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <button type="button" onclick="openAttributeModal()" class="w-full px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50">
                        <i class="fas fa-cog mr-2"></i>Configure Attributes
                    </button>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="bg-blue-50 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2"><i class="fas fa-lightbulb mr-2"></i>Quick Tips</h4>
                <ul class="text-sm text-blue-800 space-y-2">
                    <li><i class="fas fa-check mr-2 text-xs"></i>Click on price/stock to edit inline</li>
                    <li><i class="fas fa-check mr-2 text-xs"></i>Use Generate Variants to create all combinations</li>
                    <li><i class="fas fa-check mr-2 text-xs"></i>Deactivate variants instead of deleting if they have orders</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Generate Variants Modal -->
<div id="generateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Generate Variants</h3>
                <button type="button" onclick="closeGenerateModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="generateForm" action="{{ route('admin.products.variants.generate', $product) }}" method="POST">
                @csrf
                <div class="p-6">
                    <!-- Select Attributes -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Attributes to Combine</label>
                        <div class="space-y-3">
                            @foreach($attributes as $attribute)
                            <div class="border rounded-lg p-3">
                                <div class="flex items-center mb-2">
                                    <input type="checkbox" name="attributes[]" value="{{ $attribute->id }}" 
                                        id="attr_{{ $attribute->id }}"
                                        class="attribute-checkbox rounded border-gray-300 mr-3"
                                        {{ in_array($attribute->id, $productAttributeIds) ? 'checked' : '' }}>
                                    <label for="attr_{{ $attribute->id }}" class="font-medium text-gray-900">{{ $attribute->name }}</label>
                                </div>
                                <div class="ml-6 attribute-values {{ in_array($attribute->id, $productAttributeIds) ? '' : 'hidden' }}">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($attribute->values as $value)
                                        <label class="inline-flex items-center px-3 py-1 bg-gray-100 rounded-full text-sm cursor-pointer hover:bg-gray-200">
                                            <input type="checkbox" name="attribute_values_{{ $attribute->id }}[]" value="{{ $value->id }}"
                                                class="rounded border-gray-300 mr-2">
                                            {{ $value->value }}
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Auto-generation Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                            <div class="text-sm text-blue-700">
                                <p class="font-medium mb-1">SKU & Barcode Auto-Generation</p>
                                <p class="mb-1">• SKU Format: <code>{{ $product->sku_prefix ?: 'PREFIX' }}-XXXX-XXX-ATTR</code></p>
                                <p class="mb-1">• Barcode Format: <code>200{{ str_pad($product->id, 7, '0', STR_PAD_LEFT) }}XX</code> (EAN-13)</p>
                                <p class="text-xs text-blue-600 mt-2">All variants of this product will have related barcodes for easy identification.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Default Settings -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Default Settings</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Base Price</label>
                                <input type="number" name="base_price" step="0.01" value="{{ $product->base_price }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Default Stock</label>
                                <input type="number" name="default_stock" value="0" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div id="combinationPreview" class="hidden">
                        <h4 class="font-medium text-gray-900 mb-2">Preview (<span id="previewCount">0</span> variants)</h4>
                        <div class="combination-preview bg-gray-50 rounded-lg p-3 text-sm">
                            <ul id="previewList" class="space-y-1"></ul>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="closeGenerateModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Generate Variants
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Variant Modal -->
<div id="editVariantModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Edit Variant</h3>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="editVariantForm" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div class="flex items-center mb-4">
                        <div id="variantImagePreview" class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                        <div>
                            <div id="variantAttributes" class="font-medium text-gray-900"></div>
                            <div id="variantSku" class="text-sm text-gray-500 font-mono"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                            <input type="text" name="sku" id="edit_sku" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">Auto-generated, can be edited</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                            <input type="text" name="barcode" id="edit_barcode" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">EAN-13, related to product (200XXXXXXX)</p>
                        </div>
                    </div>

                    <!-- Cost, Regular Price Row -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price (৳)</label>
                            <input type="number" name="cost_price" id="edit_cost_price" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Regular Price (৳)</label>
                            <input type="number" name="price" id="edit_price" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>

                    <!-- Discount Section -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Discount</label>
                        <div class="grid grid-cols-3 gap-4">
                            <!-- Discount Type -->
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Discount Type</label>
                                <select name="discount_type" id="edit_discount_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="">No Discount</option>
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="flat">Flat (৳)</option>
                                </select>
                            </div>
                            
                            <!-- Discount Value -->
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Discount Value</label>
                                <input type="number" name="discount_value" id="edit_discount_value" step="0.01" min="0" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="0">
                            </div>
                            
                            <!-- Sale Price (Auto-calculated) -->
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Sale Price <span class="text-gray-400">(Auto)</span></label>
                                <div class="relative">
                                    <input type="number" name="sale_price" id="edit_sale_price" step="0.01" min="0" 
                                        class="w-full px-3 py-2 border border-gray-200 bg-gray-100 rounded-lg text-gray-600"
                                        placeholder="0.00" readonly>
                                    <span id="variantDiscountBadge" class="absolute right-3 top-2 text-xs font-medium hidden"></span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1" id="variantSavingsText">No discount</p>
                            </div>
                        </div>
                    </div>

                    <!-- Wholesale Percentage Section -->
                    <div class="bg-blue-50 rounded-lg p-4 mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Wholesale Pricing</label>
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Wholesale Percentage -->
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Wholesale Discount %</label>
                                <input type="number" name="wholesale_percentage" id="edit_wholesale_percentage" step="0.01" min="0" max="99.99"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="0">
                                <p class="text-xs text-gray-500 mt-1">% off regular price. Leave empty to use product's wholesale %.</p>
                            </div>
                            
                            <!-- Calculated Wholesale Price -->
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Calculated Wholesale Price <span class="text-gray-400">(Auto)</span></label>
                                <div class="relative">
                                    <input type="text" id="edit_calculated_wholesale_price"
                                        class="w-full px-3 py-2 border border-gray-200 bg-gray-100 rounded-lg text-gray-600"
                                        placeholder="0.00" readonly>
                                </div>
                                <p class="text-xs text-gray-500 mt-1" id="wholesaleCalcText">Based on regular price minus wholesale %</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                            <input type="number" name="stock_quantity" id="edit_stock_quantity" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
                            <select name="stock_status" id="edit_stock_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="in_stock">In Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                                <option value="on_backorder">On Backorder</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="manage_stock" id="edit_manage_stock" value="1" class="rounded border-gray-300 mr-2">
                            <span class="text-sm text-gray-700">Manage Stock</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-gray-300 mr-2">
                            <span class="text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Modal functions
    function openGenerateModal() {
        document.getElementById('generateModal').classList.remove('hidden');
    }

    function closeGenerateModal() {
        document.getElementById('generateModal').classList.add('hidden');
    }

    function openEditModal() {
        document.getElementById('editVariantModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editVariantModal').classList.add('hidden');
    }

    // Attribute checkbox handling
    document.querySelectorAll('.attribute-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const valuesDiv = this.closest('.border').querySelector('.attribute-values');
            if (this.checked) {
                valuesDiv.classList.remove('hidden');
            } else {
                valuesDiv.classList.add('hidden');
                valuesDiv.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            }
            updatePreview();
        });
    });

    // Update preview when attribute values change
    document.querySelectorAll('.attribute-values input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', updatePreview);
    });

    function updatePreview() {
        const selectedAttrs = [];
        document.querySelectorAll('.attribute-checkbox:checked').forEach(attr => {
            const attrId = attr.value;
            const checkedValues = [];
            document.querySelectorAll(`input[name="attribute_values_${attrId}[]"]:checked`).forEach(v => {
                checkedValues.push(v.parentElement.textContent.trim());
            });
            if (checkedValues.length > 0) {
                selectedAttrs.push(checkedValues);
            }
        });

        if (selectedAttrs.length === 0) {
            document.getElementById('combinationPreview').classList.add('hidden');
            return;
        }

        // Calculate combinations
        const combinations = cartesianProduct(selectedAttrs);
        
        document.getElementById('previewCount').textContent = combinations.length;
        const list = document.getElementById('previewList');
        list.innerHTML = combinations.slice(0, 10).map(combo => 
            `<li class="text-gray-600">• ${combo.join(' / ')}</li>`
        ).join('');
        
        if (combinations.length > 10) {
            list.innerHTML += `<li class="text-gray-400 italic">... and ${combinations.length - 10} more</li>`;
        }
        
        document.getElementById('combinationPreview').classList.remove('hidden');
    }

    function cartesianProduct(arrays) {
        return arrays.reduce((acc, curr) => {
            return acc.flatMap(a => curr.map(c => [...(Array.isArray(a) ? a : [a]), c]));
        });
    }

    // Edit variant
    function editVariant(variantId) {
        fetch(`{{ url('admin/variants') }}/${variantId}/edit`)
            .then(response => response.json())
            .then(data => {
                const form = document.getElementById('editVariantForm');
                form.action = `{{ url('admin/variants') }}/${variantId}`;
                
                document.getElementById('edit_sku').value = data.variant.sku;
                document.getElementById('edit_barcode').value = data.variant.barcode || '';
                document.getElementById('edit_price').value = data.variant.price || '';
                document.getElementById('edit_wholesale_price').value = data.variant.wholesale_price || '';
                document.getElementById('edit_compare_price').value = data.variant.compare_price || '';
                document.getElementById('edit_cost_price').value = data.variant.cost_price || '';
                document.getElementById('edit_stock_quantity').value = data.variant.stock_quantity;
                document.getElementById('edit_stock_status').value = data.variant.stock_status;
                document.getElementById('edit_manage_stock').checked = data.variant.manage_stock;
                document.getElementById('edit_is_active').checked = data.variant.is_active;
                
                // Discount fields
                document.getElementById('edit_discount_type').value = data.variant.discount_type || '';
                document.getElementById('edit_discount_value').value = data.variant.discount_value || '';
                document.getElementById('edit_sale_price').value = data.variant.sale_price || data.variant.price || '';
                
                // Wholesale percentage field
                document.getElementById('edit_wholesale_percentage').value = data.variant.wholesale_percentage || '';
                
                // Calculate and display savings
                calculateVariantSalePrice();
                calculateVariantWholesalePrice();

                document.getElementById('variantAttributes').textContent = data.attributes.map(a => a.attribute_name + ': ' + a.value).join(', ');
                document.getElementById('variantSku').textContent = data.variant.sku;
                
                openEditModal();
            });
    }
    
    // Calculate variant sale price
    function calculateVariantSalePrice() {
        const basePrice = parseFloat(document.getElementById('edit_price')?.value) || 0;
        const discountType = document.getElementById('edit_discount_type')?.value;
        const discountValue = parseFloat(document.getElementById('edit_discount_value')?.value) || 0;
        const salePriceInput = document.getElementById('edit_sale_price');
        const discountBadge = document.getElementById('variantDiscountBadge');
        const savingsText = document.getElementById('variantSavingsText');
        
        // Calculate sale price
        let salePrice = basePrice;
        let savings = 0;
        
        if (discountValue > 0 && discountType) {
            if (discountType === 'percentage') {
                const percentage = Math.min(99, Math.max(1, discountValue));
                savings = basePrice * (percentage / 100);
                salePrice = basePrice - savings;
            } else if (discountType === 'flat') {
                savings = Math.min(discountValue, basePrice - 0.01);
                salePrice = basePrice - savings;
            }
        }
        
        salePrice = Math.max(0, salePrice);
        
        // Update sale price
        salePriceInput.value = salePrice.toFixed(2);
        
        // Update badge and savings text
        if (discountValue > 0 && discountType && basePrice > 0) {
            discountBadge.classList.remove('hidden');
            if (discountType === 'percentage') {
                discountBadge.innerHTML = `<span class="text-green-600">-${discountValue}%</span>`;
            } else {
                discountBadge.innerHTML = `<span class="text-green-600">-৳${discountValue.toFixed(2)}</span>`;
            }
            savingsText.innerHTML = `<span class="text-green-600 font-medium">Save ৳${savings.toFixed(2)}</span>`;
        } else {
            discountBadge.classList.add('hidden');
            savingsText.textContent = 'No discount applied';
        }
    }
    
    // Add event listeners for variant discount calculation
    document.getElementById('edit_price')?.addEventListener('input', calculateVariantSalePrice);
    document.getElementById('edit_discount_value')?.addEventListener('input', calculateVariantSalePrice);
    document.getElementById('edit_discount_type')?.addEventListener('change', function() {
        const discountValueInput = document.getElementById('edit_discount_value');
        if (this.value === '') {
            discountValueInput.value = '';
            discountValueInput.disabled = true;
        } else {
            discountValueInput.disabled = false;
        }
        calculateVariantSalePrice();
    });
    
    // Calculate variant wholesale price from percentage
    function calculateVariantWholesalePrice() {
        const basePrice = parseFloat(document.getElementById('edit_price')?.value) || 0;
        const percentage = parseFloat(document.getElementById('edit_wholesale_percentage')?.value) || 0;
        const calculatedInput = document.getElementById('edit_calculated_wholesale_price');
        const calcText = document.getElementById('wholesaleCalcText');
        
        if (basePrice > 0 && percentage > 0) {
            const wholesalePrice = basePrice * (1 - percentage / 100);
            calculatedInput.value = '৳' + wholesalePrice.toFixed(2) + ' (' + percentage + '% off ৳' + basePrice.toFixed(2) + ')';
            calcText.innerHTML = `<span class="text-green-600 font-medium">Save ৳${(basePrice - wholesalePrice).toFixed(2)}</span>`;
        } else {
            calculatedInput.value = '';
            calcText.textContent = percentage > 0 ? 'Enter a regular price first' : 'No wholesale pricing set';
        }
    }
    
    document.getElementById('edit_price')?.addEventListener('input', calculateVariantWholesalePrice);
    document.getElementById('edit_wholesale_percentage')?.addEventListener('input', calculateVariantWholesalePrice);

    // Update variant field inline
    function updateVariantField(variantId, field, element) {
        const value = element.textContent.replace('৳', '').replace('%', '').trim();
        const original = element.dataset.original;
        
        if (value === original) return;

        fetch(`{{ url('admin/variants') }}/${variantId}/quick-update`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ field, value })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                element.dataset.original = value;
                element.classList.add('bg-green-50');
                setTimeout(() => element.classList.remove('bg-green-50'), 500);
            }
        });
    }

    // Toggle variant status
    function toggleVariantStatus(variantId, button) {
        fetch(`{{ url('admin/variants') }}/${variantId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.is_active) {
                button.classList.remove('bg-gray-100', 'text-gray-800');
                button.classList.add('bg-green-100', 'text-green-800');
                button.textContent = 'Active';
            } else {
                button.classList.remove('bg-green-100', 'text-green-800');
                button.classList.add('bg-gray-100', 'text-gray-800');
                button.textContent = 'Inactive';
            }
        });
    }

    // Delete variant
    function deleteVariant(variantId) {
        if (!confirm('Are you sure you want to delete this variant?')) return;

        fetch(`{{ url('admin/variants') }}/${variantId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
        .then(() => {
            document.querySelector(`tr[data-variant-id="${variantId}"]`).remove();
        });
    }

    // Select all checkbox
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.variant-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    // Bulk toggle status
    function bulkToggleStatus(status) {
        const selected = Array.from(document.querySelectorAll('.variant-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one variant');
            return;
        }
        
        selected.forEach(id => toggleVariantStatus(id, document.querySelector(`tr[data-variant-id="${id}"] button`)));
    }

    // Close modals on outside click
    window.addEventListener('click', function(e) {
        if (e.target.id === 'generateModal') closeGenerateModal();
        if (e.target.id === 'editVariantModal') closeEditModal();
    });
</script>
@endpush
