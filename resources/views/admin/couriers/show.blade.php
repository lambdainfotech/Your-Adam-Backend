@extends('admin.layouts.master')

@section('title', 'View Courier')
@section('page-title', 'Courier Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start mb-6">
                <div class="flex items-center gap-4">
                    @if($courier->logo)
                        <img src="{{ $courier->logo }}" alt="" class="w-16 h-16 object-contain">
                    @endif
                    <div>
                        <h2 class="text-xl font-semibold">{{ $courier->name }}</h2>
                        <p class="text-gray-500">{{ $courier->code }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.couriers.edit', $courier) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="font-medium">{{ $courier->phone ?? 'N/A' }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium">{{ $courier->email ?? 'N/A' }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Status</p>
                    <span class="px-2 py-1 text-xs rounded-full {{ $courier->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $courier->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Assignments</p>
                    <p class="font-medium">{{ $courier->assignments_count }}</p>
                </div>
            </div>

            @if($courier->website)
            <div class="mb-4">
                <p class="text-sm text-gray-500 mb-1">Website</p>
                <a href="{{ $courier->website }}" target="_blank" class="text-blue-600 hover:underline">{{ $courier->website }}</a>
            </div>
            @endif

            @if($courier->tracking_url_template)
            <div class="mb-4">
                <p class="text-sm text-gray-500 mb-1">Tracking URL Template</p>
                <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $courier->tracking_url_template }}</code>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="font-semibold">Recent Assignments ({{ $courier->assignments->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500">Order</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500">Tracking #</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500">Status</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($courier->assignments->take(10) as $assignment)
                        <tr>
                            <td class="px-6 py-4">{{ $assignment->order?->order_number ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $assignment->tracking_number }}</td>
                            <td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full bg-gray-100">{{ $assignment->status }}</span></td>
                            <td class="px-6 py-4 text-sm">{{ $assignment->created_at?->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">No assignments yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold mb-4">API Configuration</h3>
            @if($courier->api_config)
                <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto">{{ json_encode($courier->api_config, JSON_PRETTY_PRINT) }}</pre>
            @else
                <p class="text-gray-500">No API configuration set</p>
            @endif
        </div>
    </div>
</div>
@endsection
