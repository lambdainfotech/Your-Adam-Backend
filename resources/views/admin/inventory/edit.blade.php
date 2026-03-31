@extends('admin.layouts.master')

@section('title', 'Manage Stock')
@section('page-title', 'Manage Stock: ' . $product->name)

@section('content')
<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.inventory.index') }}" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
        </a>
        <div class="flex space-x-2">
            <a href="{{ route('admin.products.show', $product) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-eye mr-2"></i>View Product
            </a>
        </div>
    </div>
    
    <!-- Product Info -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
        <div class="flex items-center">
            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center mr-4">
                <i class="fas fa-box text-gray-400 text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $product->name }}</h2>
                <p class="text-gray-500">SKU: {{ $product->sku_prefix }}</p>
                <p class="text-sm text-gray-400">Category: {{ $product->category->name ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
    
    <!-- Stock Management Form -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Update Stock & Pricing</h3>
        
        <form method="POST" action="{{ route('admin.inventory.update', $product) }}">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Current Stock</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">New Stock</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($product->variants as $index => $variant)
                            <tr>
                                <td class="px-4 py-4">
                                    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                                    <span class="font-medium">{{ $variant->sku }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="px-2 py-1 text-sm rounded-full {{ $variant->stock_quantity === 0 ? 'bg-red-100 text-red-800' : ($variant->stock_quantity <= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                        {{ $variant->stock_quantity }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <input type="number" name="variants[{{ $index }}][stock_quantity]" 
                                        value="{{ $variant->stock_quantity }}" 
                                        min="0"
                                        class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </td>
                                <td class="px-4 py-4">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">$</span>
                                        </div>
                                        <input type="number" name="variants[{{ $index }}][price]" 
                                            value="{{ $variant->price }}" 
                                            step="0.01"
                                            min="0"
                                            class="w-32 pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 flex items-center justify-between">
                <a href="{{ route('admin.inventory.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Update Inventory
                </button>
            </div>
        </form>
    </div>
    
    <!-- Quick Stock Adjustment -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Stock Adjustment</h3>
        <p class="text-gray-500 mb-4">Quickly add or remove stock from a variant with a reason.</p>
        
        @foreach($product->variants as $variant)
            <form method="POST" action="{{ route('admin.inventory.adjust', $variant) }}" class="mb-4 p-4 bg-gray-50 rounded-lg">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Variant</label>
                        <p class="text-gray-800 font-medium">{{ $variant->sku }}</p>
                        <p class="text-sm text-gray-500">Current: {{ $variant->stock_quantity }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment</label>
                        <input type="number" name="adjustment" required
                            placeholder="+10 or -5"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Use + to add, - to remove</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                        <div class="flex space-x-2">
                            <input type="text" name="reason" required
                                placeholder="e.g., New stock arrived, Damaged goods, etc."
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                                Adjust
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endforeach
    </div>
</div>
@endsection
