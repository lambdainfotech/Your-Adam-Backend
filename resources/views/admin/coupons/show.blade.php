@extends('admin.layouts.master')

@section('title', 'View Coupon')
@section('page-title', 'Coupon Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold">Coupon Information</h2>
                <div class="flex gap-2">
                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Code</p>
                        <p class="font-medium text-lg">{{ $coupon->code }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Type</p>
                        <p class="font-medium">{{ $coupon->type === 'percentage' ? 'Percentage' : 'Fixed Amount' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Value</p>
                        <p class="font-medium">{{ $coupon->type === 'percentage' ? $coupon->value . '%' : '৳' . number_format($coupon->value, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <span class="px-2 py-1 text-xs rounded-full {{ $coupon->is_valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $coupon->is_valid ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Usage</p>
                        <p class="font-medium">{{ $coupon->usages->count() }} / {{ $coupon->total_usage_limit ?? '∞' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Per User Limit</p>
                        <p class="font-medium">{{ $coupon->usage_limit_per_user }}</p>
                    </div>
                </div>
                @if($coupon->description)
                <div class="mt-4">
                    <p class="text-sm text-gray-500">Description</p>
                    <p>{{ $coupon->description }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Usage History ({{ $coupon->usages->count() }})</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500">User</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500">Order ID</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500">Discount</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500">Used At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($coupon->usages as $usage)
                        <tr>
                            <td class="px-6 py-4">{{ $usage->user?->name ?? 'Guest' }}</td>
                            <td class="px-6 py-4">#{{ $usage->order_id }}</td>
                            <td class="px-6 py-4">৳{{ number_format($usage->discount_amount, 2) }}</td>
                            <td class="px-6 py-4">{{ $usage->used_at?->format('M d, Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">No usage history</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold mb-4">Validity Period</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500">Starts At</p>
                    <p>{{ $coupon->starts_at?->format('M d, Y H:i') ?? 'Immediately' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Expires At</p>
                    <p>{{ $coupon->expires_at?->format('M d, Y H:i') ?? 'Never' }}</p>
                </div>
            </div>
            <hr class="my-4">
            <h3 class="font-semibold mb-4">Restrictions</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500">Min Purchase</p>
                    <p>৳{{ number_format($coupon->min_purchase_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Max Discount</p>
                    <p>{{ $coupon->max_discount_amount ? '৳' . number_format($coupon->max_discount_amount, 2) : 'No limit' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
