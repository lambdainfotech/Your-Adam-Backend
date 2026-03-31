@extends('admin.layouts.master')

@section('title', 'Activity Log Details')
@section('page-title', 'Activity Log Details')

@section('content')
<div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-6">
            <h2 class="text-xl font-semibold">Log Entry #{{ $activityLog->id }}</h2>
            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">{{ $activityLog->action }}</span>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">User</p>
                <p class="font-medium">{{ $activityLog->user?->name ?? 'System' }}</p>
                @if($activityLog->user)
                    <p class="text-sm text-gray-500">{{ $activityLog->user->email }}</p>
                @endif
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Timestamp</p>
                <p class="font-medium">{{ $activityLog->created_at->format('M d, Y H:i:s') }}</p>
                <p class="text-sm text-gray-500">{{ $activityLog->created_at->diffForHumans() }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Entity Type</p>
                <p class="font-medium capitalize">{{ $activityLog->entity_type }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Entity ID</p>
                <p class="font-medium">{{ $activityLog->entity_id ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="mb-6">
            <p class="text-sm text-gray-500 mb-1">IP Address</p>
            <code class="bg-gray-100 px-3 py-1 rounded">{{ $activityLog->ip_address ?? 'N/A' }}</code>
        </div>

        @if($activityLog->description)
        <div class="mb-6">
            <p class="text-sm text-gray-500 mb-1">Description</p>
            <p class="bg-gray-50 p-4 rounded-lg">{{ $activityLog->description }}</p>
        </div>
        @endif

        @if($activityLog->old_values)
        <div class="mb-6">
            <p class="text-sm text-gray-500 mb-2">Old Values</p>
            <pre class="bg-red-50 p-4 rounded-lg text-sm overflow-x-auto">{{ json_encode($activityLog->old_values, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif

        @if($activityLog->new_values)
        <div class="mb-6">
            <p class="text-sm text-gray-500 mb-2">New Values</p>
            <pre class="bg-green-50 p-4 rounded-lg text-sm overflow-x-auto">{{ json_encode($activityLog->new_values, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif

        <div class="flex items-center gap-4">
            <a href="{{ route('admin.activity-logs.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Back to Logs
            </a>
        </div>
    </div>
</div>
@endsection
