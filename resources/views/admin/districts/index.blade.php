@extends('admin.layouts.master')

@section('title', 'Districts')
@section('page-title', 'District Management')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4 flex-wrap">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search districts..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    <i class="fas fa-search"></i> Filter
                </button>

                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.districts.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </form>
        </div>

        <a href="{{ route('admin.districts.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add District
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Name</th>

                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($districts as $district)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $district->name }}</td>

                    <td class="px-6 py-4">
                        @if($district->is_active)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.districts.edit', $district) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.districts.toggle-status', $district) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-{{ $district->is_active ? 'red' : 'green' }}-600 hover:text-{{ $district->is_active ? 'red' : 'green' }}-800" title="{{ $district->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $district->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.districts.destroy', $district) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
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
                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-map-marker-alt text-4xl mb-4 text-gray-300"></i>
                        <p>No districts found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-200">
        {{ $districts->links() }}
    </div>
</div>
@endsection
