@extends('admin.layouts.master')

@section('title', 'Coupons')
@section('page-title', 'Coupon Management')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4 flex-wrap">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Search coupons..." 
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                
                <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Types</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                
                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    <i class="fas fa-search"></i> Filter
                </button>
                
                @if(request()->hasAny(['search', 'type', 'status']))
                    <a href="{{ route('admin.coupons.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </form>
        </div>
        
        <a href="{{ route('admin.coupons.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Coupon
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Value</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Usage</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Valid Period</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($coupons as $coupon)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $coupon->code }}</div>
                        @if($coupon->description)
                            <div class="text-sm text-gray-500">{{ Str::limit($coupon->description, 40) }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $coupon->type === 'percentage' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $types[$coupon->type] ?? $coupon->type }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($coupon->type === 'percentage')
                            {{ $coupon->value }}%
                        @else
                            ৳{{ number_format($coupon->value, 2) }}
                        @endif
                        @if($coupon->max_discount_amount)
                            <div class="text-xs text-gray-500">Max: ৳{{ number_format($coupon->max_discount_amount, 2) }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm">
                            {{ $coupon->usages_count }} / {{ $coupon->total_usage_limit ?? '∞' }}
                        </div>
                        <div class="text-xs text-gray-500">
                            Per user: {{ $coupon->usage_limit_per_user }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($coupon->starts_at)
                            <div>{{ $coupon->starts_at->format('M d, Y') }}</div>
                        @endif
                        @if($coupon->expires_at)
                            <div class="text-gray-500">to {{ $coupon->expires_at->format('M d, Y') }}</div>
                        @else
                            <span class="text-gray-400">No expiry</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($coupon->is_valid)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                        @elseif($coupon->is_expired)
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Expired</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.coupons.show', $coupon) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.coupons.toggle-status', $coupon) }}" method="POST" class="inline">
                                @csrf
                                @method('POST')
                                <button type="submit" class="text-{{ $coupon->is_active ? 'red' : 'green' }}-600 hover:text-{{ $coupon->is_active ? 'red' : 'green' }}-800" title="{{ $coupon->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $coupon->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
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
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-ticket-alt text-4xl mb-4 text-gray-300"></i>
                        <p>No coupons found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-200">
        {{ $coupons->links() }}
    </div>
</div>
@endsection
