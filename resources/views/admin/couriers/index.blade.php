@extends('admin.layouts.master')

@section('title', 'Couriers')
@section('page-title', 'Courier Management')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search couriers..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"><i class="fas fa-search"></i></button>
        </form>
        <a href="{{ route('admin.couriers.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"><i class="fas fa-plus mr-2"></i>Add Courier</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Courier</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Assignments</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($couriers as $courier)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($courier->logo)
                                <img src="{{ $courier->logo }}" alt="" class="w-10 h-10 object-contain">
                            @endif
                            <span class="font-medium">{{ $courier->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">{{ $courier->code }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($courier->phone)<div>{{ $courier->phone }}</div>@endif
                        @if($courier->email)<div class="text-gray-500">{{ $courier->email }}</div>@endif
                    </td>
                    <td class="px-6 py-4">{{ $courier->assignments_count }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $courier->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $courier->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.couriers.show', $courier) }}" class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.couriers.edit', $courier) }}" class="text-yellow-600 hover:text-yellow-800"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.couriers.toggle-status', $courier) }}" method="POST" class="inline">@csrf<button type="submit" class="text-{{ $courier->is_active ? 'red' : 'green' }}-600 hover:text-{{ $courier->is_active ? 'red' : 'green' }}-800"><i class="fas fa-{{ $courier->is_active ? 'ban' : 'check' }}"></i></button></form>
                            <form action="{{ route('admin.couriers.destroy', $courier) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button></form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No couriers found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200">{{ $couriers->links() }}</div>
</div>
@endsection
