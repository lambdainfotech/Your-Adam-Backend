@extends('admin.layouts.master')

@section('title', 'Guests')
@section('page-title', 'Guest Customers')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Guest Customers</h2>
            <p class="text-gray-500 mt-1">Unique guest customers grouped by email</p>
        </div>
        <div class="bg-white rounded-lg px-4 py-2 border border-gray-200">
            <span class="text-sm text-gray-500">Total Unique Guests:</span>
            <span class="text-lg font-bold text-blue-600 ml-2">{{ $guests->total() }}</span>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <form method="GET" action="{{ route('admin.guests.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Search by name, email or phone...">
            </div>
            <button type="submit" class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">
                <i class="fas fa-search mr-2"></i>Search
            </button>
            @if(!empty($search))
                <a href="{{ route('admin.guests.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-center hover:bg-gray-300">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Guests Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Orders</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Total Spent</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Last Order</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Records</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($guests as $guest)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center text-orange-600 text-sm font-semibold">
                                        {{ substr($guest->name, 0, 1) }}
                                    </div>
                                    <span class="font-medium text-gray-800">{{ $guest->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $guest->email ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $guest->phone ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if($guest->total_orders > 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        {{ $guest->total_orders }} order{{ $guest->total_orders > 1 ? 's' : '' }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800">
                                @if($guest->total_spent > 0)
                                    ৳{{ number_format($guest->total_spent, 2) }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-sm">
                                {{ \Carbon\Carbon::parse($guest->last_order_date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($guest->guest_records > 1)
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800" title="Multiple guest records for same email">
                                        {{ $guest->guest_records }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">1</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('admin.guests.show', urlencode($guest->email)) }}"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-medium">No guests found</p>
                                <p class="text-sm mt-1">Guest customers will appear here after they place an order.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($guests->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $guests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
