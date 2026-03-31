@extends('admin.layouts.master')

@section('title', 'Size Charts')
@section('page-title', 'Size Charts')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex items-center gap-2">
            <select name="category_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"><i class="fas fa-search"></i></button>
        </form>
        <a href="{{ route('admin.size-charts.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"><i class="fas fa-plus mr-2"></i>Add Size Chart</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Unit</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Sizes</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sizeCharts as $chart)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ $chart->name }}</td>
                    <td class="px-6 py-4">{{ $chart->category?->name }}</td>
                    <td class="px-6 py-4">{{ $chart->unit }}</td>
                    <td class="px-6 py-4">{{ $chart->rows->count() }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $chart->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $chart->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.size-charts.show', $chart) }}" class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.size-charts.edit', $chart) }}" class="text-yellow-600 hover:text-yellow-800"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.size-charts.toggle-status', $chart) }}" method="POST" class="inline">@csrf<button type="submit" class="text-{{ $chart->is_active ? 'red' : 'green' }}-600 hover:text-{{ $chart->is_active ? 'red' : 'green' }}-800"><i class="fas fa-{{ $chart->is_active ? 'ban' : 'check' }}"></i></button></form>
                            <form action="{{ route('admin.size-charts.destroy', $chart) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button></form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No size charts found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200">{{ $sizeCharts->links() }}</div>
</div>
@endsection
