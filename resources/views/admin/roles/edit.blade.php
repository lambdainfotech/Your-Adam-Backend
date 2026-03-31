@extends('admin.layouts.master')

@section('title', 'Edit Role')
@section('page-title', 'Edit Role')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role Name *</label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Level *</label>
                <input type="number" name="level" value="{{ old('level', $role->level) }}" min="1" max="100" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <p class="text-sm text-gray-500 mt-1">Higher level = more access</p>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description" rows="2" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('description', $role->description) }}</textarea>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-4">Permissions</label>
            @foreach($modules as $module => $permissions)
            <div class="mb-4 border rounded-lg p-4">
                <h4 class="font-semibold text-gray-700 mb-2 capitalize">{{ $module }}</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($permissions as $permission)
                    <label class="flex items-center">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                            {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                            class="w-4 h-4 text-blue-600 rounded">
                        <span class="ml-2 text-sm">{{ $permission->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Update Role
            </button>
            <a href="{{ route('admin.roles.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
