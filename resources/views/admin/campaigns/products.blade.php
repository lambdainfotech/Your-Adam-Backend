@extends('admin.layouts.master')

@section('title', 'Campaign Products')
@section('page-title', 'Manage Campaign Products')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-lg font-semibold">{{ $campaign->name }}</h2>
                <p class="text-gray-500">Select products for this campaign</p>
            </div>
            <a href="{{ route('admin.campaigns.index') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-1"></i> Back to Campaigns
            </a>
        </div>
    </div>

    <form action="{{ route('admin.campaigns.products.update', $campaign) }}" method="POST" class="p-6">
        @csrf
        
        <div class="mb-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-medium">Selected Products</h3>
                <button type="button" onclick="document.getElementById('productModal').classList.remove('hidden')" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Add Products
                </button>
            </div>
            
            <div id="selectedProducts" class="space-y-2">
                @foreach($campaign->products as $product)
                <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg" data-product-id="{{ $product->id }}">
                    <input type="hidden" name="products[{{ $product->id }}][id]" value="{{ $product->id }}">
                    <div class="flex-1">
                        <p class="font-medium">{{ $product->name }}</p>
                        <p class="text-sm text-gray-500">Regular: ৳{{ number_format($product->base_price, 2) }}</p>
                    </div>
                    <div>
                        <input type="number" name="products[{{ $product->id }}][special_price]" 
                            value="{{ $product->pivot->special_price }}"
                            placeholder="Special Price" step="0.01" min="0"
                            class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <button type="button" onclick="removeProduct(this)" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Save Changes
            </button>
            <a href="{{ route('admin.campaigns.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
</div>

<!-- Product Selection Modal -->
<div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-h-[80vh] overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-semibold">Select Products</h3>
            <button onclick="document.getElementById('productModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-4 overflow-y-auto max-h-[60vh]">
            <input type="text" id="productSearch" placeholder="Search products..." 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-4">
            
            <div id="productList" class="space-y-2">
                @foreach($products as $product)
                <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer product-item">
                    <input type="checkbox" value="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->base_price }}"
                        class="product-checkbox w-4 h-4 text-blue-600 rounded">
                    <div class="ml-3 flex-1">
                        <p class="font-medium">{{ $product->name }}</p>
                        <p class="text-sm text-gray-500">৳{{ number_format($product->base_price, 2) }}</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        <div class="p-4 border-t border-gray-200 flex justify-end gap-2">
            <button onclick="document.getElementById('productModal').classList.add('hidden')" 
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
            <button onclick="addSelectedProducts()" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Selected</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedProductIds = new Set({{ $campaign->products->pluck('id') }});

function removeProduct(btn) {
    const row = btn.closest('[data-product-id]');
    selectedProductIds.delete(parseInt(row.dataset.productId));
    row.remove();
}

function addSelectedProducts() {
    const container = document.getElementById('selectedProducts');
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    
    checkboxes.forEach(checkbox => {
        const productId = checkbox.value;
        if (selectedProductIds.has(parseInt(productId))) return;
        
        selectedProductIds.add(parseInt(productId));
        const productName = checkbox.dataset.name;
        const productPrice = checkbox.dataset.price;
        
        const div = document.createElement('div');
        div.className = 'flex items-center gap-4 p-3 bg-gray-50 rounded-lg';
        div.dataset.productId = productId;
        div.innerHTML = `
            <input type="hidden" name="products[${productId}][id]" value="${productId}">
            <div class="flex-1">
                <p class="font-medium">${productName}</p>
                <p class="text-sm text-gray-500">Regular: ৳${parseFloat(productPrice).toFixed(2)}</p>
            </div>
            <div>
                <input type="number" name="products[${productId}][special_price]" 
                    placeholder="Special Price" step="0.01" min="0"
                    class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <button type="button" onclick="removeProduct(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(div);
    });
    
    document.getElementById('productModal').classList.add('hidden');
    checkbox.checked = false;
}

// Search functionality
document.getElementById('productSearch')?.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(item => {
        const name = item.querySelector('.font-medium').textContent.toLowerCase();
        item.style.display = name.includes(query) ? 'flex' : 'none';
    });
});
</script>
@endpush
@endsection
