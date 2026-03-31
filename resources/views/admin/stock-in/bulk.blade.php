@extends('admin.layouts.master')

@section('title', 'Stock In')
@section('page-title', 'Stock In')

@section('content')
<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.inventory.index') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
            </a>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                <i class="fas fa-warehouse mr-2"></i>View Inventory
            </a>
        </div>
    </div>

    <!-- Stock In Form -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                <i class="fas fa-list-alt text-green-600 text-xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Stock In</h2>
                <p class="text-gray-500">Add stock to multiple products</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.stock-in.bulk.store') }}" id="bulkStockInForm">
            @csrf

            <!-- Header Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                    <input type="date" name="date" id="date" value="{{ date('Y-m-d') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="reference_no" class="block text-sm font-medium text-gray-700 mb-2">Reference No</label>
                    <input type="text" name="reference_no" id="reference_no" placeholder="e.g., PO-12345"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <input type="text" name="notes" id="notes" placeholder="Any notes..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Items Table -->
            <div class="border rounded-lg overflow-hidden">
                <table class="w-full" id="itemsTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Variant</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Current Stock</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Qty to Add</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Unit Cost</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <!-- Items will be added here -->
                    </tbody>
                </table>
                
                <!-- Empty State -->
                <div id="emptyState" class="py-12 text-center text-gray-400">
                    <i class="fas fa-boxes text-4xl mb-3"></i>
                    <p>No items added yet. Click "Add Item" to start.</p>
                </div>
            </div>

            <!-- Add Item Button -->
            <div class="mt-4">
                <button type="button" onclick="addItem()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-plus mr-2"></i>Add Item
                </button>
            </div>

            <!-- Summary -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Total Items:</span>
                    <span class="font-bold text-lg" id="totalItems">0</span>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-8 flex items-center justify-between">
                <a href="{{ route('admin.inventory.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <div class="flex space-x-3">
                    <button type="reset" onclick="clearAllItems()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-trash mr-2"></i>Clear All
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" id="submitBtn" disabled>
                        <i class="fas fa-save mr-2"></i>Save Stock In
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Product data from server
    const productsData = @json($products);
    let itemCount = 0;

    function addItem() {
        itemCount++;
        const tbody = document.getElementById('itemsBody');
        const emptyState = document.getElementById('emptyState');
        
        emptyState.style.display = 'none';

        const row = document.createElement('tr');
        row.className = 'border-t';
        row.id = `itemRow_${itemCount}`;
        
        // Build product options
        let productOptions = '<option value="">Select Product</option>';
        productsData.forEach(product => {
            productOptions += `<option value="${product.id}">${escapeHtml(product.name)} (${product.sku_prefix})</option>`;
        });
        
        row.innerHTML = `
            <td class="px-4 py-3">
                <select name="items[${itemCount}][product_id]" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg product-select"
                    onchange="loadVariants(this, ${itemCount})">
                    ${productOptions}
                </select>
            </td>
            <td class="px-4 py-3">
                <select name="items[${itemCount}][variant_id]" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg variant-select"
                    id="variant_${itemCount}"
                    onchange="updateCurrentStock(${itemCount})">
                    <option value="">Select Variant</option>
                </select>
            </td>
            <td class="px-4 py-3">
                <span class="text-sm text-gray-600 current-stock" id="currentStock_${itemCount}">-</span>
            </td>
            <td class="px-4 py-3">
                <input type="number" name="items[${itemCount}][quantity]" required min="1" value="1"
                    class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-center">
            </td>
            <td class="px-4 py-3">
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                    <input type="number" name="items[${itemCount}][unit_cost]" step="0.01" min="0"
                        class="w-28 pl-6 pr-3 py-2 border border-gray-300 rounded-lg">
                </div>
            </td>
            <td class="px-4 py-3 text-center">
                <button type="button" onclick="removeItem(${itemCount})" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
        updateSummary();
    }

    function loadVariants(productSelect, itemId) {
        const productId = productSelect.value;
        const variantSelect = document.getElementById(`variant_${itemId}`);
        const currentStock = document.getElementById(`currentStock_${itemId}`);
        
        variantSelect.innerHTML = '<option value="">Select Variant</option>';
        currentStock.textContent = '-';
        
        if (!productId) return;
        
        const product = productsData.find(p => p.id == productId);
        if (product && product.variants && product.variants.length > 0) {
            product.variants.forEach(variant => {
                const option = document.createElement('option');
                option.value = variant.id;
                option.textContent = variant.sku;
                option.setAttribute('data-stock', variant.stock_quantity);
                variantSelect.appendChild(option);
            });
        }
    }

    function updateCurrentStock(itemId) {
        const variantSelect = document.getElementById(`variant_${itemId}`);
        const currentStock = document.getElementById(`currentStock_${itemId}`);
        const selectedOption = variantSelect.options[variantSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const stock = selectedOption.getAttribute('data-stock');
            currentStock.textContent = stock;
        } else {
            currentStock.textContent = '-';
        }
    }

    function removeItem(itemId) {
        const row = document.getElementById(`itemRow_${itemId}`);
        if (row) {
            row.remove();
        }
        
        // Check if no items left
        const tbody = document.getElementById('itemsBody');
        if (tbody.children.length === 0) {
            document.getElementById('emptyState').style.display = 'block';
        }
        
        updateSummary();
    }

    function clearAllItems() {
        const tbody = document.getElementById('itemsBody');
        tbody.innerHTML = '';
        document.getElementById('emptyState').style.display = 'block';
        itemCount = 0;
        updateSummary();
    }

    function updateSummary() {
        const tbody = document.getElementById('itemsBody');
        const totalItems = tbody.children.length;
        document.getElementById('totalItems').textContent = totalItems;
        document.getElementById('submitBtn').disabled = totalItems === 0;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Add first item automatically on page load
    document.addEventListener('DOMContentLoaded', function() {
        addItem();
    });
</script>
@endpush
