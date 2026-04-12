@extends('admin.layouts.master')

@section('title', 'Testimonials')
@section('page-title', 'Testimonials Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Customer Testimonials</h3>
            <p class="text-sm text-gray-500">Manage homepage customer reviews</p>
        </div>
        <a href="{{ route('admin.testimonials.create') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Testimonial
        </a>
    </div>

    <!-- Testimonials Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Rating</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Content</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($testimonials as $testimonial)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($testimonial->avatar)
                                        <img src="{{ $testimonial->avatar }}" alt="{{ $testimonial->name }}" class="w-10 h-10 rounded-full object-cover mr-3">
                                    @else
                                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </div>
                                    @endif
                                    <span class="font-medium text-gray-800">{{ $testimonial->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $testimonial->role ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex text-yellow-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $testimonial->rating ? '' : 'text-gray-300' }}"></i>
                                    @endfor
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 max-w-xs truncate">{{ Str::limit($testimonial->content, 50) }}</td>
                            <td class="px-6 py-4">{{ $testimonial->sort_order }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $testimonial->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $testimonial->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.testimonials.toggle-status', $testimonial) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="{{ $testimonial->is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}" title="{{ $testimonial->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas {{ $testimonial->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" class="inline" onsubmit="return confirm('Are you sure?');">
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
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <i class="fas fa-comments text-4xl mb-3"></i>
                                    <p>No testimonials found</p>
                                    <a href="{{ route('admin.testimonials.create') }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">Add your first testimonial</a>
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
