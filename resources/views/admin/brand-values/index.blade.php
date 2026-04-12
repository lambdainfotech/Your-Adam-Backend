@extends('admin.layouts.master')

@section('title', 'Brand Values')
@section('page-title', 'Brand Values Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Brand Values</h3>
            <p class="text-sm text-gray-500">Manage homepage brand value propositions</p>
        </div>
        <a href="{{ route('admin.brand-values.create') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Brand Value
        </a>
    </div>

    <!-- Brand Values Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Icon</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Title</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($brandValues as $value)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-{{ strtolower($value->icon) }} text-blue-600"></i>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $value->title }}</td>
                            <td class="px-6 py-4 text-gray-600 max-w-xs truncate">{{ $value->description }}</td>
                            <td class="px-6 py-4">{{ $value->sort_order }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $value->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $value->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.brand-values.edit', $value) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.brand-values.toggle-status', $value) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="{{ $value->is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}" title="{{ $value->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas {{ $value->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.brand-values.destroy', $value) }}" class="inline" onsubmit="return confirm('Are you sure?');">
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
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <i class="fas fa-gem text-4xl mb-3"></i>
                                    <p>No brand values found</p>
                                    <a href="{{ route('admin.brand-values.create') }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">Add your first brand value</a>
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
