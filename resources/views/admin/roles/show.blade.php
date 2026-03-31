@extends('admin.layouts.master')

@section('title', 'View Role')
@section('page-title', 'Role Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-xl font-semibold">{{ $role->name }}</h2>
                    <p class="text-gray-500">{{ $role->slug }}</p>
                </div>
                <div class="flex gap-2">
                    @if(!$role->is_system)
                    <a href="{{ route('admin.roles.edit', $role) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Level</p>
                    <p class="font-medium">{{ $role->level }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Users</p>
                    <p class="font-medium">{{ $role->users->count() }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Type</p>
                    <span class="px-2 py-1 text-xs rounded-full {{ $role->is_system ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $role->is_system ? 'System' : 'Custom' }}
                    </span>
                </div>
            </div>

            @if($role->description)
            <div class="mb-6">
                <p class="text-sm text-gray-500 mb-1">Description</p>
                <p>{{ $role->description }}</p>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="font-semibold">Permissions ({{ $role->permissions->count() }})</h3>
            </div>
            <div class="p-6">
                @php
                    $groupedPermissions = $role->permissions->groupBy('module');
                @endphp
                
                @foreach($groupedPermissions as $module => $permissions)
                <div class="mb-4">
                    <h4 class="font-medium text-gray-700 capitalize mb-2">{{ $module }}</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($permissions as $permission)
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                            {{ $permission->name }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div>
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="font-semibold">Users with this Role</h3>
            </div>
            <div class="p-6">
                @if($role->users->count() > 0)
                    <div class="space-y-3">
                        @foreach($role->users->take(10) as $user)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-medium">{{ $user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($role->users->count() > 10)
                        <p class="text-center text-gray-500 mt-4">+ {{ $role->users->count() - 10 }} more</p>
                    @endif
                @else
                    <p class="text-gray-500 text-center py-4">No users assigned</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
