@extends('admin.layouts.master')

@section('title', 'Inventory Report')
@section('page-title', 'Inventory Report')

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Products</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($totalProducts) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Out of Stock</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($outOfStock) }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Low Stock</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ number_format($lowStock) }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Products -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Products Needing Attention</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Variant</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stock Level</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                        @foreach($product->variants as $variant)
                            <tr>
                                <td class="px-6 py-3 font-medium">{{ $product->name }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $product->category->name ?? 'N/A' }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $variant->sku }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center">
                                        <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                            @php
                                                $isOutOfStock = $variant->stock_quantity == 0;
                                                $isLowStock = $variant->manage_stock && $variant->stock_quantity > 0 && $variant->stock_quantity <= $variant->low_stock_threshold;
                                                $barColor = $isOutOfStock ? 'bg-red-500' : ($isLowStock ? 'bg-yellow-400' : 'bg-emerald-400');
                                                $barWidth = $isOutOfStock ? 0 : min(100, ($variant->stock_quantity / max($variant->low_stock_threshold * 4, 20)) * 100);
                                            @endphp
                                            <div class="h-2 rounded-full {{ $barColor }}" style="width: {{ $barWidth }}%"></div>
                                        </div>
                                        <span class="text-sm">{{ $variant->stock_quantity }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    @php
                                        $statusClass = $isOutOfStock ? 'bg-red-100 text-red-800' : ($isLowStock ? 'bg-yellow-100 text-yellow-800' : 'bg-emerald-100 text-emerald-800');
                                        $statusLabel = $isOutOfStock ? 'Out of Stock' : ($isLowStock ? 'Low Stock' : 'In Stock');
                                    @endphp
                                    <span class="px-2 py-1 text-xs rounded-full {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-check-circle text-green-500 text-4xl mb-2"></i>
                                    <p>All products have sufficient stock!</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
