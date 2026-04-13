@extends('admin.layouts.master')

@section('title', 'Product Details')
@section('page-title', 'Product Details')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.products.index') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Products
            </a>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.products.images', $product) }}" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                <i class="fas fa-images mr-2"></i>Manage Images
            </a>
            <a href="{{ route('admin.products.edit', $product) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 {{ $product->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} text-white rounded-lg">
                    <i class="fas {{ $product->is_active ? 'fa-ban' : 'fa-check' }} mr-2"></i>{{ $product->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        </div>
    </div>
    
    <!-- Product Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $product->name }}</h2>
                    <p class="text-gray-500 mt-1">SKU: {{ $product->sku_prefix }}</p>
                </div>
                <div class="flex flex-col items-end space-y-2">
                    <span class="px-3 py-1 text-sm rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($product->is_featured)
                        <span class="px-3 py-1 text-sm rounded-full bg-yellow-100 text-yellow-800">Featured</span>
                    @endif
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Category</p>
                    <p class="font-medium text-gray-800">{{ $product->category->name ?? 'N/A' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Base Price</p>
                    <p class="font-medium text-gray-800">৳{{ number_format($product->base_price, 2) }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Wholesale Discount</p>
                    <p class="font-medium text-gray-800">{{ $product->wholesale_percentage ? $product->wholesale_percentage . '% off' : 'N/A' }}</p>
                    @if($product->effective_wholesale_price)
                        <p class="text-xs text-green-600 mt-1">৳{{ number_format($product->effective_wholesale_price, 2) }}</p>
                    @endif
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Created</p>
                    <p class="font-medium text-gray-800">{{ $product->created_at->format('M d, Y') }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Updated</p>
                    <p class="font-medium text-gray-800">{{ $product->updated_at->format('M d, Y') }}</p>
                </div>
            </div>
            
            @if($product->short_description)
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-800 mb-2">Short Description</h3>
                    <p class="text-gray-600">{{ $product->short_description }}</p>
                </div>
            @endif
            
            @if($product->description)
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">Full Description</h3>
                    <div class="text-gray-600 prose max-w-none">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Images -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800">Product Images</h3>
                    <span class="text-sm text-gray-500">{{ $product->images->count() }} image(s)</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    @forelse($product->images->take(4) as $image)
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden relative group">
                            <img src="{{ $image->thumbnail_url ?? $image->image_url }}" alt="{{ $image->alt_text }}" class="w-full h-full object-cover">
                            @if($image->is_main)
                                <div class="absolute top-1 left-1 bg-yellow-500 text-white text-xs px-1.5 py-0.5 rounded">
                                    <i class="fas fa-star"></i>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-2 aspect-square bg-gray-100 rounded-lg flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas fa-image text-gray-400 text-3xl mb-2"></i>
                                <p class="text-sm text-gray-400">No images</p>
                            </div>
                        </div>
                    @endforelse
                </div>
                @if($product->images->count() > 4)
                    <p class="text-center text-sm text-gray-500 mt-2">+{{ $product->images->count() - 4 }} more</p>
                @endif
                <a href="{{ route('admin.products.images', $product) }}" class="mt-4 block w-full text-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
                    <i class="fas fa-images mr-2"></i>Manage Images
                </a>
            </div>
            
            <!-- Variants Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-semibold text-gray-800 mb-4">Variants Summary</h3>
                @php
                    $totalStock = $product->variants->sum('stock_quantity');
                    $variantCount = $product->variants->count();
                @endphp
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Variants</span>
                        <span class="font-medium">{{ $variantCount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Stock</span>
                        <span class="font-medium {{ $totalStock <= 10 ? 'text-red-600' : '' }}">{{ $totalStock }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Variants Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Product Variants</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($product->variants as $variant)
                        <tr>
                            <td class="px-6 py-4 font-medium">{{ $variant->sku }}</td>
                            <td class="px-6 py-4">৳{{ number_format($variant->price, 2) }}</td>
                            <td class="px-6 py-4 {{ $variant->stock_quantity <= 10 ? 'text-red-600 font-medium' : '' }}">{{ $variant->stock_quantity }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $variant->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $variant->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">No variants found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
