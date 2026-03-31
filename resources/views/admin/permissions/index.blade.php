@extends('admin.layouts.master')

@section('title', 'Permissions')
@section('page-title', 'Permission Management')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search permissions..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <select name="module" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Modules</option>
                @foreach($modules as $mod)
                    <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"><i class="fas fa-search"></i></button>
        </form>
        <a href="{{ route('admin.permissions.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"><i class="fas fa-plus mr-2"></i>Add Permission</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Slug</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Module</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Action</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Roles</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($permissions as $permission)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ $permission->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $permission->slug }}</td>
                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full bg-gray-100">{{ $permission->module }}</span></td>
                    <td class="px-6 py-4">{{ $permission->action }}</td>
                    <td class="px-6 py-4">{{ $permission->roles_count }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.permissions.edit', $permission) }}" class="text-yellow-600 hover:text-yellow-800"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button></form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No permissions found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200">{{ $permissions->links() }}</div>
</div>
@endsection
