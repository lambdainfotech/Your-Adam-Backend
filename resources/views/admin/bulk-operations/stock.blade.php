@extends('admin.layouts.master')

@section('title', 'Bulk Stock Update')

@push('styles')
<style>
    .product-card {
        transition: all 0.2s;
    }
    .product-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .variant-row {
        transition: background-color 0.2s;
    }
    .variant-row:hover {
        background-color: #f3f4f6;
    }
    .quantity-input {
        width: 80px;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Bulk Stock Update</h1>
            <p class="text-gray-500 text-sm mt-1">Update stock quantities for multiple products and variants at once</p>
        </div>
        <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
        </a>
    </div>

    <!-- Operation Settings -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4"><i class="fas fa-cog mr-2 text-blue-500"></i>Operation Settings</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Operation Type</label>
                <select id="operationType" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="add">Add Stock (+)</option>
                    <option value="subtract">Subtract Stock (-)</option>
                    <option value="set">Set to Value (=)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                <input type="text" id="operationReason" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                    placeholder="e.g., New shipment received, Stock correction">
            </div>
            <div class="flex items-end">
                <button type="button" onclick="applyOperation()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-check mr-2"></i>Apply Updates
                </button>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="searchInput" placeholder="Search products or SKUs..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <select id="filterType" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Products</option>
                    <option value="simple">Simple Products</option>
                    <option value="variable">Variable Products</option>
                </select>
            </div>
            <div>
                <select id="filterStock" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Stock Status</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low">Low Stock</option>
                    <option value="out">Out of Stock</option>
                </select>
            </div>
            <button type="button" onclick="selectAllVisible()" class="px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                Select All Visible
            </button>
            <button type="button" onclick="clearSelection()" class="px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-lg">
                Clear Selection
            </button>
        </div>
    </div>

    <!-- Products List -->
    <div class="space-y-4" id="productsList">
        @foreach($products as $product)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 product-card" 
             data-product-id="{{ $product->id }}"
             data-product-type="{{ $product->product_type }}"
             data-name="{{ strtolower($product->name) }}"
             data-sku="{{ strtolower($product->sku ?? $product->sku_prefix ?? '') }}">
            
            <!-- Product Header -->
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" class="product-checkbox rounded border-gray-300 mr-4 h-5 w-5" 
                        value="{{ $product->id }}" data-type="{{ $product->product_type }}">
                    
                    @if($product->mainImage)
                        <img src="{{ $product->mainImage->full_thumbnail_url }}" class="w-12 h-12 rounded object-cover mr-4">
                    @else
                        <div class="w-12 h-12 bg-gray-100 rounded flex items-center justify-center mr-4">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                    @endif
                    
                    <div>
                        <div class="flex items-center">
                            <h4 class="font-medium text-gray-900">{{ $product->name }}</h4>
                            <span class="ml-2 px-2 py-0.5 text-xs rounded-full {{ $product->product_type === 'simple' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ ucfirst($product->product_type) }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-500">
                            SKU: {{ $product->sku ?? $product->sku_prefix ?? 'N/A' }}
                            @if($product->product_type === 'variable')
                                • {{ $product->variants->count() }} variants
                            @endif
                        </div>
                    </div>
                </div>
                
                @if($product->product_type === 'simple')
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Current Stock</div>
                        <div class="font-semibold {{ $product->stock_quantity <= $product->low_stock_threshold ? 'text-yellow-600' : 'text-green-600' }}">
                            {{ $product->stock_quantity }}
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="adjustQuantity(this, -1)" class="w-8 h-8 bg-gray-100 rounded hover:bg-gray-200">
                            <i class="fas fa-minus text-xs"></i>
                        </button>
                        <input type="number" class="quantity-input border border-gray-300 rounded px-2 py-1" 
                            value="0" min="0" data-product-id="{{ $product->id }}" data-type="product">
                        <button type="button" onclick="adjustQuantity(this, 1)" class="w-8 h-8 bg-gray-100 rounded hover:bg-gray-200">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </div>
                </div>
                @else
                <button type="button" onclick="toggleVariants({{ $product->id }})" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-chevron-down mr-1"></i>Show Variants
                </button>
                @endif
            </div>

            <!-- Variants List (for variable products) -->
            @if($product->product_type === 'variable')
            <div id="variants-{{ $product->id }}" class="hidden bg-gray-50">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-10">
                                <input type="checkbox" class="select-all-variants rounded border-gray-300" data-product-id="{{ $product->id }}">
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Variant</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">SKU</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Current</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Update</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($product->variants as $variant)
                        <tr class="variant-row" data-variant-id="{{ $variant->id }}">
                            <td class="px-4 py-3">
                                <input type="checkbox" class="variant-checkbox rounded border-gray-300" 
                                    value="{{ $variant->id }}" data-type="variant" data-product-id="{{ $product->id }}">
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-sm text-gray-900">{{ $variant->attribute_text_short }}</div>
                                <div class="text-xs text-gray-500">{{ $variant->attribute_text }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono">{{ $variant->sku }}</td>
                            <td class="px-4 py-3">
                                <span class="{{ $variant->is_low_stock ? 'text-yellow-600' : ($variant->stock_quantity <= 0 ? 'text-red-600' : 'text-green-600') }} font-medium">
                                    {{ $variant->stock_quantity }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-2">
                                    <button type="button" onclick="adjustQuantity(this, -1)" class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    <input type="number" class="quantity-input border border-gray-300 rounded px-2 py-1 text-sm" 
                                        value="0" min="0" data-variant-id="{{ $variant->id }}" data-type="variant">
                                    <button type="button" onclick="adjustQuantity(this, 1)" class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Empty State -->
    @if($products->isEmpty())
    <div class="text-center py-12">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-boxes text-2xl text-gray-400"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900">No Products Found</h3>
        <p class="text-gray-500 mt-2">Add some products first to use bulk stock operations</p>
    </div>
    @endif
</div>

<!-- Results Modal -->
<div id="resultsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Bulk Update Results</h3>
            </div>
            <div class="p-6">
                <div id="resultsContent"></div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
                <button type="button" onclick="closeResultsModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Search and filter
    document.getElementById('searchInput')?.addEventListener('input', filterProducts);
    document.getElementById('filterType')?.addEventListener('change', filterProducts);
    document.getElementById('filterStock')?.addEventListener('change', filterProducts);

    function filterProducts() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const type = document.getElementById('filterType').value;
        const stock = document.getElementById('filterStock').value;

        document.querySelectorAll('.product-card').forEach(card => {
            const name = card.dataset.name;
            const sku = card.dataset.sku;
            const productType = card.dataset.productType;
            const productId = card.dataset.productId;
            
            let show = true;

            // Text search
            if (search && !name.includes(search) && !sku.includes(search)) {
                show = false;
            }

            // Type filter
            if (type !== 'all' && productType !== type) {
                show = false;
            }

            // Stock filter (simplified)
            if (stock !== 'all') {
                // This would need actual stock data to filter properly
                // For now, we'll just show all
            }

            card.style.display = show ? '' : 'none';
        });
    }

    // Toggle variants display
    function toggleVariants(productId) {
        const container = document.getElementById(`variants-${productId}`);
        container.classList.toggle('hidden');
    }

    // Adjust quantity input
    function adjustQuantity(button, delta) {
        const input = button.parentElement.querySelector('.quantity-input');
        let value = parseInt(input.value) || 0;
        value = Math.max(0, value + delta);
        input.value = value;
        
        // Auto-check the checkbox
        const row = button.closest('tr') || button.closest('.product-card');
        const checkbox = row.querySelector('.variant-checkbox, .product-checkbox');
        if (checkbox && value > 0) {
            checkbox.checked = true;
        }
    }

    // Select all variants
    document.querySelectorAll('.select-all-variants').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const variants = document.querySelectorAll(`.variant-checkbox[data-product-id="${productId}"]`);
            variants.forEach(cb => cb.checked = this.checked);
        });
    });

    // Select all visible products
    function selectAllVisible() {
        document.querySelectorAll('.product-card:not([style*="display: none"]) .product-checkbox').forEach(cb => {
            cb.checked = true;
        });
    }

    // Clear selection
    function clearSelection() {
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.value = 0;
        });
    }

    // Apply bulk operation
    function applyOperation() {
        const operation = document.getElementById('operationType').value;
        const reason = document.getElementById('operationReason').value;

        if (!reason) {
            alert('Please provide a reason for this stock adjustment');
            return;
        }

        const updates = [];

        // Collect product updates
        document.querySelectorAll('.product-checkbox:checked').forEach(cb => {
            const card = cb.closest('.product-card');
            const input = card.querySelector('.quantity-input');
            const quantity = parseInt(input.value) || 0;

            if (quantity > 0) {
                updates.push({
                    id: cb.value,
                    type: 'product',
                    quantity: quantity
                });
            }
        });

        // Collect variant updates
        document.querySelectorAll('.variant-checkbox:checked').forEach(cb => {
            const row = cb.closest('tr');
            const input = row.querySelector('.quantity-input');
            const quantity = parseInt(input.value) || 0;

            if (quantity > 0) {
                updates.push({
                    id: cb.value,
                    type: 'variant',
                    quantity: quantity
                });
            }
        });

        if (updates.length === 0) {
            alert('Please select at least one product/variant and specify a quantity');
            return;
        }

        if (!confirm(`Are you sure you want to ${operation} stock for ${updates.length} item(s)?`)) {
            return;
        }

        // Send request
        fetch('{{ route("admin.bulk.stock.process") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                operation,
                reason,
                updates
            })
        })
        .then(response => response.json())
        .then(data => {
            showResults(data);
        });
    }

    // Show results modal
    function showResults(data) {
        const modal = document.getElementById('resultsModal');
        const content = document.getElementById('resultsContent');

        let html = `
            <div class="flex items-center justify-center mb-6">
                <div class="w-16 h-16 ${data.failed === 0 ? 'bg-green-100' : 'bg-yellow-100'} rounded-full flex items-center justify-center">
                    <i class="fas ${data.failed === 0 ? 'fa-check text-green-600' : 'fa-exclamation text-yellow-600'} text-2xl"></i>
                </div>
            </div>
            <div class="text-center mb-6">
                <div class="text-2xl font-bold text-gray-900">${data.success} Successful</div>
                ${data.failed > 0 ? `<div class="text-red-600">${data.failed} Failed</div>` : ''}
            </div>
        `;

        if (data.details?.failed?.length > 0) {
            html += `<div class="bg-red-50 rounded-lg p-4 mb-4">
                <h4 class="font-medium text-red-800 mb-2">Failed Updates:</h4>
                <ul class="text-sm text-red-700 space-y-1">`;
            data.details.failed.forEach(item => {
                html += `<li>• ${item.type === 'product' ? 'Product' : 'Variant'} #${item.id}: ${item.reason}</li>`;
            });
            html += `</ul></div>`;
        }

        content.innerHTML = html;
        modal.classList.remove('hidden');
    }

    function closeResultsModal() {
        document.getElementById('resultsModal').classList.add('hidden');
        // Reload to show updated values
        window.location.reload();
    }
</script>
@endpush
