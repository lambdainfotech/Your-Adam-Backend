@extends('admin.layouts.master')

@section('title', 'Campaigns')
@section('page-title', 'Campaign Management')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search campaigns..." 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>Running</option>
                <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>Ended</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                <i class="fas fa-search"></i> Filter
            </button>
        </form>
        <a href="{{ route('admin.campaigns.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Campaign
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Discount</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Period</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Apply To</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($campaigns as $campaign)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $campaign->name }}</div>
                        <div class="text-sm text-gray-500">{{ $campaign->slug }}</div>
                    </td>
                    <td class="px-6 py-4">
                        {{ $campaign->discount_type === 'percentage' ? $campaign->discount_value . '%' : '৳' . number_format($campaign->discount_value, 2) }}
                    </td>
                    <td class="px-6 py-4 text-sm">
                        {{ $campaign->starts_at?->format('M d') }} - {{ $campaign->ends_at?->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4">
                        @if($campaign->apply_type === 'all')
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">All Products</span>
                        @elseif($campaign->apply_type === 'products')
                            <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">{{ $campaign->products_count }} Products</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800">{{ $campaign->categories_count }} Categories</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($campaign->is_running)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Running</span>
                        @elseif($campaign->is_expired)
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Ended</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Upcoming</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.campaigns.show', $campaign) }}" class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="text-yellow-600 hover:text-yellow-800"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('admin.campaigns.products', $campaign) }}" class="text-purple-600 hover:text-purple-800" title="Manage Products"><i class="fas fa-boxes"></i></a>
                            <form action="{{ route('admin.campaigns.toggle-status', $campaign) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-{{ $campaign->is_active ? 'red' : 'green' }}-600 hover:text-{{ $campaign->is_active ? 'red' : 'green' }}-800">
                                    <i class="fas fa-{{ $campaign->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.campaigns.destroy', $campaign) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No campaigns found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200">{{ $campaigns->links() }}</div>
</div>
@endsection
