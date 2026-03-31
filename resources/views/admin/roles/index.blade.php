@extends('admin.layouts.master')

@section('title', 'Roles')
@section('page-title', 'Role Management')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search roles..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"><i class="fas fa-search"></i></button>
        </form>
        <a href="{{ route('admin.roles.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"><i class="fas fa-plus mr-2"></i>Add Role</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Slug</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Level</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Users</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($roles as $role)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ $role->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $role->slug }}</td>
                    <td class="px-6 py-4">{{ $role->level }}</td>
                    <td class="px-6 py-4">{{ $role->users_count }}</td>
                    <td class="px-6 py-4">
                        @if($role->is_system)
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">System</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Custom</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.roles.show', $role) }}" class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye"></i></a>
                            @if(!$role->is_system)
                            <a href="{{ route('admin.roles.edit', $role) }}" class="text-yellow-600 hover:text-yellow-800"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button></form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No roles found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200">{{ $roles->links() }}</div>
</div>
@endsection
