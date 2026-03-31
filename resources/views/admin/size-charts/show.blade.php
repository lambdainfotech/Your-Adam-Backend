@extends('admin.layouts.master')

@section('title', 'View Size Chart')
@section('page-title', 'Size Chart Details')

@section('content')
<div>
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-xl font-semibold">{{ $sizeChart->name }}</h2>
                <p class="text-gray-500">{{ $sizeChart->category?->name }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.size-charts.edit', $sizeChart) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Unit</p>
                <p class="font-medium">{{ $sizeChart->unit === 'inch' ? 'Inch' : 'Centimeter' }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Number of Sizes</p>
                <p class="font-medium">{{ $sizeChart->rows->count() }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Status</p>
                <span class="px-2 py-1 text-xs rounded-full {{ $sizeChart->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $sizeChart->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>

        @if($sizeChart->description)
        <div class="mb-6">
            <p class="text-sm text-gray-500 mb-1">Description</p>
            <p>{{ $sizeChart->description }}</p>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="font-semibold">Size Measurements ({{ $sizeChart->unit }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Size</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Chest</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Length</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Shoulder</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Sleeve</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sizeChart->rows as $row)
                    <tr>
                        <td class="px-6 py-4 font-medium">{{ $row->size_name }}</td>
                        <td class="px-6 py-4">{{ $row->measurements['chest'] ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $row->measurements['length'] ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $row->measurements['shoulder'] ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $row->measurements['sleeve'] ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No size measurements added</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
