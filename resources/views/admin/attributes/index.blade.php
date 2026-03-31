@extends('admin.layouts.master')

@section('title', 'Attributes')
@section('page-title', 'Product Attributes')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Product Attributes</h3>
            <p class="text-sm text-gray-500">Manage attributes like Color, Size, Material, etc.</p>
        </div>
        <a href="{{ route('admin.attributes.create') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Attribute
        </a>
    </div>

    <!-- Attributes Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Attribute</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase">Values</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase">Filterable</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase">Variation</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($attributes as $attribute)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800">{{ $attribute->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $attribute->code }}</code>
                            </td>
                            <td class="px-6 py-4">
                                <span class="capitalize">{{ $attribute->type }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">{{ $attribute->values_count }} values</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($attribute->is_filterable)
                                    <i class="fas fa-check text-green-600"></i>
                                @else
                                    <i class="fas fa-times text-gray-400"></i>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($attribute->is_variation)
                                    <i class="fas fa-check text-green-600"></i>
                                @else
                                    <i class="fas fa-times text-gray-400"></i>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.attributes.show', $attribute->id) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.attributes.edit', $attribute->id) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.attributes.destroy', $attribute->id) }}" class="inline" onsubmit="return confirm('Are you sure? This will delete all values too.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <i class="fas fa-tags text-4xl mb-3"></i>
                                    <p>No attributes found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-blue-50 rounded-xl p-6 border border-blue-100">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-info text-blue-600"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-blue-800">What are Attributes?</h4>
                    <p class="text-sm text-blue-600 mt-1">Attributes define product characteristics like Color, Size, Material.</p>
                </div>
            </div>
        </div>
        <div class="bg-green-50 rounded-xl p-6 border border-green-100">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-filter text-green-600"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-green-800">Filterable</h4>
                    <p class="text-sm text-green-600 mt-1">Filterable attributes appear in product filters on the storefront.</p>
                </div>
            </div>
        </div>
        <div class="bg-purple-50 rounded-xl p-6 border border-purple-100">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-layer-group text-purple-600"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-purple-800">Variation</h4>
                    <p class="text-sm text-purple-600 mt-1">Variation attributes are used to create product variants (e.g., Red-Small).</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
