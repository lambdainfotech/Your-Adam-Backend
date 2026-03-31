@extends('admin.layouts.master')

@section('title', 'Products Report')
@section('page-title', 'Products Report')

@section('content')
<div class="space-y-6">
    <!-- Top Selling Products -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Top Selling Products</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Units Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($topProducts as $index => $product)
                        <tr>
                            <td class="px-6 py-3">{{ $index + 1 }}</td>
                            <td class="px-6 py-3 font-medium">{{ $product->name }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $product->sku_prefix }}</td>
                            <td class="px-6 py-3">
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">{{ $product->total_sold }}</span>
                            </td>
                            <td class="px-6 py-3 font-medium">${{ number_format($product->total_revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">No sales data found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Low Stock Products -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Low Stock Alert</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stock Level</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($lowStockProducts as $product)
                        @foreach($product->variants as $variant)
                            <tr>
                                <td class="px-6 py-3 font-medium">{{ $product->name }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $variant->sku }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $variant->stock_quantity == 0 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $variant->stock_quantity }} in stock
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-400">All products have sufficient stock</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
