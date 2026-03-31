@extends('admin.layouts.master')

@section('title', 'View Attribute')
@section('page-title', 'Attribute Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.attributes.index') }}" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Back to Attributes
        </a>
    </div>

    <!-- Attribute Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-start mb-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-tag text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $attribute->name }}</h2>
                    <p class="text-gray-500">Code: <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $attribute->code }}</code></p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.attributes.edit', $attribute->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Type</p>
                <p class="font-medium capitalize">{{ $attribute->type }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Values Count</p>
                <p class="font-medium">{{ count($values) }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Filterable</p>
                <p class="font-medium">
                    @if($attribute->is_filterable)
                        <span class="text-green-600"><i class="fas fa-check mr-1"></i>Yes</span>
                    @else
                        <span class="text-gray-400"><i class="fas fa-times mr-1"></i>No</span>
                    @endif
                </p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">For Variations</p>
                <p class="font-medium">
                    @if($attribute->is_variation)
                        <span class="text-green-600"><i class="fas fa-check mr-1"></i>Yes</span>
                    @else
                        <span class="text-gray-400"><i class="fas fa-times mr-1"></i>No</span>
                    @endif
                </p>
            </div>
        </div>

        @if($attribute->sort_order)
        <div class="mt-4 bg-gray-50 p-4 rounded-lg inline-block">
            <p class="text-sm text-gray-500">Sort Order</p>
            <p class="font-medium">{{ $attribute->sort_order }}</p>
        </div>
        @endif
    </div>

    <!-- Attribute Values -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Attribute Values</h3>
            <p class="text-sm text-gray-500">Manage values for this attribute</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Value</th>
                        @if($attribute->type === 'color')
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Color Code</th>
                        @endif
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Sort Order</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($values as $index => $value)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($attribute->type === 'color' && $value->color_code)
                                        <span class="w-6 h-6 rounded-full mr-2 border border-gray-200" style="background-color: {{ $value->color_code }}"></span>
                                    @endif
                                    <span class="font-medium text-gray-800">{{ $value->value }}</span>
                                </div>
                            </td>
                            @if($attribute->type === 'color')
                            <td class="px-6 py-4">
                                @if($value->color_code)
                                    <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $value->color_code }}</code>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            @endif
                            <td class="px-6 py-4 text-gray-600">{{ $value->sort_order }}</td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.attributes.values.delete', $value->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this value?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $attribute->type === 'color' ? 5 : 4 }}" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <i class="fas fa-list text-4xl mb-3"></i>
                                    <p>No values found for this attribute</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add New Value -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Add New Value</h3>
        <form method="POST" action="{{ route('admin.attributes.values.add', $attribute->id) }}" class="flex flex-wrap items-end gap-4">
            @csrf
            <div class="flex-1 min-w-[200px]">
                <label for="value" class="block text-sm font-medium text-gray-700 mb-2">Value *</label>
                <input type="text" name="value" id="value" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="e.g., Red, Large, Cotton">
            </div>
            
            @if($attribute->type === 'color')
            <div class="w-40">
                <label for="color_code" class="block text-sm font-medium text-gray-700 mb-2">Color Code</label>
                <input type="text" name="color_code" id="color_code"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="#FF0000">
            </div>
            @endif
            
            <div class="w-32">
                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" min="0" value="0"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Add Value
            </button>
        </form>
    </div>
</div>
@endsection
