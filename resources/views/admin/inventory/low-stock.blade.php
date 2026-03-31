@extends('admin.layouts.master')

@section('title', 'Low Stock Alerts')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Low Stock Alerts</h1>
            <p class="text-gray-500 text-sm mt-1">Items that need to be restocked soon</p>
        </div>
        <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
        </a>
    </div>

    <!-- Alert Banner -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
            <div>
                <h3 class="font-medium text-yellow-900">Attention Required</h3>
                <p class="text-yellow-800 text-sm mt-1">
                    The following items are running low on stock. Consider placing a new order with your suppliers.
                </p>
            </div>
        </div>
    </div>

    <!-- Low Stock Items Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Current Stock</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Threshold</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($lowStockItems as $item)
                <tr class="hover:bg-gray-50 {{ $item['stock'] <= 0 ? 'bg-red-50' : '' }}">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-900">{{ $item['name'] }}</div>
                        <div class="text-sm text-gray-500 font-mono">{{ $item['sku'] }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $item['type'] === 'product' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ ucfirst($item['type']) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $item['category'] ?? 'Uncategorized' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-lg font-semibold {{ $item['stock'] <= 0 ? 'text-red-600' : 'text-yellow-600' }}">
                            {{ $item['stock'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">
                        {{ $item['threshold'] }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($item['type'] === 'product')
                            <a href="{{ route('admin.inventory.edit', $item['id']) }}" class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-edit mr-1"></i>Update Stock
                            </a>
                        @else
                            <a href="{{ route('admin.products.variants', $item['product_id']) }}" class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-edit mr-1"></i>Manage Variant
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check text-2xl text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">All Stock Levels Normal</h3>
                        <p class="text-gray-500">No items are currently below their low stock threshold.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Export/Print Section -->
    @if(count($lowStockItems) > 0)
    <div class="mt-6 flex justify-end space-x-3">
        <button type="button" onclick="window.print()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
            <i class="fas fa-print mr-2"></i>Print List
        </button>
        <button type="button" onclick="exportCSV()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-download mr-2"></i>Export CSV
        </button>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function exportCSV() {
        // Simple CSV export
        let csv = 'Item,Type,Category,Current Stock,Threshold,SKU\n';
        @json($lowStockItems).forEach(item => {
            csv += `"${item.name}",${item.type},${item.category || ''},${item.stock},${item.threshold},${item.sku}\n`;
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'low-stock-' + new Date().toISOString().split('T')[0] + '.csv';
        a.click();
    }
</script>
@endpush
