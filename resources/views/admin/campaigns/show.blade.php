@extends('admin.layouts.master')

@section('title', 'View Campaign')
@section('page-title', 'Campaign Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-xl font-semibold">{{ $campaign->name }}</h2>
                    <p class="text-gray-500">{{ $campaign->slug }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                </div>
            </div>

            @if($campaign->banner_image)
            <div class="mb-6">
                <img src="{{ $campaign->banner_image }}" alt="{{ $campaign->name }}" class="w-full h-48 object-cover rounded-lg">
            </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Discount</p>
                    <p class="font-medium">{{ $campaign->discount_type === 'percentage' ? $campaign->discount_value . '%' : '৳' . number_format($campaign->discount_value, 2) }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Products</p>
                    <p class="font-medium">{{ $campaign->products->count() }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Status</p>
                    <span class="px-2 py-1 text-xs rounded-full {{ $campaign->is_running ? 'bg-green-100 text-green-800' : ($campaign->is_expired ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ $campaign->is_running ? 'Running' : ($campaign->is_expired ? 'Ended' : 'Upcoming') }}
                    </span>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Apply to All</p>
                    <p class="font-medium">{{ $campaign->apply_to_all ? 'Yes' : 'No' }}</p>
                </div>
            </div>

            @if($campaign->description)
            <div class="mb-6">
                <p class="text-sm text-gray-500 mb-1">Description</p>
                <p>{{ $campaign->description }}</p>
            </div>
            @endif
        </div>

        @if(!$campaign->apply_to_all && $campaign->products->count() > 0)
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="font-semibold">Campaign Products ({{ $campaign->products->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500">Product</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500">Regular Price</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500">Campaign Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($campaign->products as $product)
                        <tr>
                            <td class="px-6 py-4">{{ $product->name }}</td>
                            <td class="px-6 py-4">৳{{ number_format($product->base_price, 2) }}</td>
                            <td class="px-6 py-4">
                                @if($product->pivot->special_price)
                                    ৳{{ number_format($product->pivot->special_price, 2) }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <div>
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="font-semibold mb-4">Campaign Period</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500">Starts At</p>
                    <p>{{ $campaign->starts_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Ends At</p>
                    <p>{{ $campaign->ends_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Duration</p>
                    <p>{{ $campaign->starts_at->diffInDays($campaign->ends_at) }} days</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold mb-4">Restrictions</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500">Min Purchase</p>
                    <p>৳{{ number_format($campaign->min_purchase_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Max Discount</p>
                    <p>{{ $campaign->max_discount_amount ? '৳' . number_format($campaign->max_discount_amount, 2) : 'No limit' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
