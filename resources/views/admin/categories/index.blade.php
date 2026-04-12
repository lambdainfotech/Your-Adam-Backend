@extends('admin.layouts.master')

@section('title', 'Categories')
@section('page-title', 'Categories Management')

@section('content')
<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">All Categories</h3>
            <p class="text-sm text-gray-500 mt-1">Manage product categories and subcategories</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span class="w-3 h-3 bg-gray-50 border border-gray-200 rounded"></span>
                <span>Subcategory</span>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Add Category
            </a>
        </div>
    </div>
    
    <!-- Categories Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Products</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $displayedIds = [];
                    @endphp
                    
                    @forelse($categories as $category)
                        {{-- Skip if already displayed as a child --}}
                        @if(in_array($category->id, $displayedIds))
                            @continue
                        @endif
                        
                        {{-- Display parent/root category --}}
                        @if(!$category->parent_id)
                            @php $displayedIds[] = $category->id; @endphp
                            <tr class="hover:bg-gray-50 bg-white">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 overflow-hidden">
                                            @if($category->image)
                                                <img src="{{ $category->image }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-folder text-blue-500"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800">{{ $category->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $category->slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium">
                                        <i class="fas fa-folder mr-1"></i>Parent
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">
                                        {{ $category->products_count + $category->children->sum('products_count') }} products
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $category->sort_order }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}" class="text-green-600 hover:text-green-800" title="Add Subcategory">
                                            <i class="fas fa-folder-plus"></i>
                                        </a>
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="{{ $category->is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}" title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas {{ $category->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?{{ $category->children->count() > 0 ? ' This will also affect ' . $category->children->count() . ' subcategory(s).' : '' }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            
                            {{-- Display children of this category --}}
                            @foreach($categories->where('parent_id', $category->id) as $child)
                                @php $displayedIds[] = $child->id; @endphp
                                <tr class="hover:bg-gray-50 bg-gray-50/50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center pl-8">
                                            <i class="fas fa-level-up-alt text-gray-400 mr-2 transform rotate-90 text-xs"></i>
                                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3 overflow-hidden">
                                                @if($child->image)
                                                    <img src="{{ $child->image }}" alt="{{ $child->name }}" class="w-full h-full object-cover">
                                                @else
                                                    <i class="fas fa-folder text-gray-400 text-sm"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-700">{{ $child->name }}</p>
                                                <p class="text-xs text-gray-400">{{ $child->slug }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">
                                            <i class="fas fa-folder-open mr-1"></i>Subcategory
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-blue-50 text-blue-700 text-xs px-2 py-1 rounded">
                                            {{ $child->products_count }} products
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">{{ $child->sort_order }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $child->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $child->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('admin.categories.edit', $child) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.categories.toggle-status', $child) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="{{ $child->is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}" title="{{ $child->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="fas {{ $child->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.categories.destroy', $child) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this subcategory?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <i class="fas fa-tags text-4xl mb-3"></i>
                                    <p>No categories found</p>
                                    <a href="{{ route('admin.categories.create') }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">Create your first category</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Quick Tips -->
    <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
        <div class="flex items-start">
            <i class="fas fa-lightbulb text-blue-600 mt-1 mr-3"></i>
            <div>
                <h4 class="text-sm font-medium text-blue-800">Quick Tips</h4>
                <ul class="text-sm text-blue-700 mt-1 space-y-1">
                    <li>• Create <strong>Parent Categories</strong> to organize your main product types</li>
                    <li>• Use <strong>Subcategories</strong> for more specific product groupings</li>
                    <li>• Products can be assigned to any category level</li>
                    <li>• Deactivating a parent category won't deactivate its subcategories</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
