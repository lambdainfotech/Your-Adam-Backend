@extends('admin.layouts.master')

@section('title', 'User Details')
@section('page-title', 'User: ' . $user->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Back to Users
        </a>
        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
            @csrf
            <button type="submit" class="px-4 py-2 {{ $user->status === 1 ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} text-white rounded-lg">
                <i class="fas {{ $user->status === 1 ? 'fa-ban' : 'fa-check' }} mr-2"></i>{{ $user->status === 1 ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
    </div>
    
    <!-- User Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="text-center">
                <div class="w-24 h-24 bg-blue-500 rounded-full flex items-center justify-center text-white text-4xl font-bold mx-auto mb-4">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <h2 class="text-xl font-bold text-gray-800">{{ $user->name }}</h2>
                <p class="text-gray-500">{{ $user->email }}</p>
                <span class="mt-2 inline-block px-3 py-1 text-sm rounded-full {{ $user->status === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $user->status === 1 ? 'Active' : 'Inactive' }}
                </span>
            </div>
            
            <div class="mt-6 space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Role</span>
                    <span class="font-medium">{{ $user->role->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Mobile</span>
                    <span class="font-medium">{{ $user->mobile ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Joined</span>
                    <span class="font-medium">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Orders</span>
                    <span class="font-medium">{{ $user->orders->count() }}</span>
                </div>
            </div>
        </div>
        
        <!-- Addresses -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="font-semibold text-gray-800 mb-4">Addresses</h3>
            @if($user->addresses->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($user->addresses as $address)
                        <div class="p-4 border border-gray-200 rounded-lg {{ $address->is_default ? 'bg-blue-50 border-blue-200' : '' }}">
                            @if($address->is_default)
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded mb-2 inline-block">Default</span>
                            @endif
                            <address class="not-italic text-gray-600 text-sm">
                                {{ $address->address_line_1 }}<br>
                                @if($address->address_line_2)
                                    {{ $address->address_line_2 }}<br>
                                @endif
                                {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}<br>
                                {{ $address->country }}
                            </address>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400">No addresses found</p>
            @endif
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Recent Orders</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Order #</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($user->orders->take(10) as $order)
                        <tr>
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:underline">#{{ $order->order_number }}</a>
                            </td>
                            <td class="px-6 py-3">${{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                ">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $order->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">No orders found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
