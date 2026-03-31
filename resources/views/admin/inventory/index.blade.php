@extends('admin.layouts.master')

@section('title', 'Inventory Management')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Inventory Management</h1>
            <p class="text-gray-500 text-sm mt-1">Manage stock levels and track inventory movements</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.inventory.low-stock') }}" class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg hover:bg-yellow-200">
                <i class="fas fa-exclamation-triangle mr-2"></i>Low Stock Alerts
            </a>
            <a href="{{ route('admin.inventory.valuation') }}" class="px-4 py-2 bg-blue-100 text-blue-800 rounded-lg hover:bg-blue-200">
                <i class="fas fa-chart-pie mr-2"></i>Valuation
            </a>
            <a href="{{ route('admin.bulk.stock') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-layer-group mr-2"></i>Bulk Update
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-boxes text-blue-600"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ $summary['total'] }}</div>
                    <div class="text-xs text-gray-500">Total Products</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ $summary['total'] - $summary['out_of_stock'] - $summary['low_stock'] }}</div>
                    <div class="text-xs text-gray-500">Well Stocked</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-exclamation-circle text-yellow-600"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ $summary['low_stock'] }}</div>
                    <div class="text-xs text-gray-500">Low Stock</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ $summary['out_of_stock'] }}</div>
                    <div class="text-xs text-gray-500">Out of Stock</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-dollar-sign text-purple-600"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">${{ number_format($summary['valuation']['total'], 0) }}</div>
                    <div class="text-xs text-gray-500">Inventory Value</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form action="{{ route('admin.inventory.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products or SKUs..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <select name="stock_status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Stock Status</option>
                    <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
            <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Clear
            </a>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU/Prefix</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            @if($product->mainImage)
                                <img src="{{ $product->mainImage->full_thumbnail_url }}" class="w-10 h-10 rounded object-cover mr-3">
                            @else
                                <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center mr-3">
                                    <i class="fas fa-image text-gray-400 text-sm"></i>
                                </div>
                            @endif
                            <div>
                                <div class="font-medium text-gray-900">{{ Str::limit($product->name, 40) }}</div>
                                <div class="text-xs text-gray-500">{{ $product->category?->name ?? 'No Category' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $product->product_type === 'simple' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ ucfirst($product->product_type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-mono">
                        {{ $product->sku ?? $product->sku_prefix ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="text-lg font-semibold {{ $product->is_low_stock ? 'text-yellow-600' : ($product->is_in_stock ? 'text-green-600' : 'text-red-600') }}">
                            {{ $product->total_stock }}
                        </div>
                        @if($product->product_type === 'variable')
                            <div class="text-xs text-gray-500">{{ $product->variants->count() }} variants</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($product->is_low_stock)
                            <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Low Stock</span>
                        @elseif($product->is_in_stock)
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">In Stock</span>
                        @else
                            <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Out of Stock</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.inventory.edit', $product) }}" class="text-blue-600 hover:text-blue-800 mr-3">
                            <i class="fas fa-edit"></i> Manage
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-boxes text-2xl text-gray-400"></i>
                        </div>
                        <p>No products found matching your criteria</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>
@endsection
