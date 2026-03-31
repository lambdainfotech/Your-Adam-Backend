@extends('admin.layouts.master')

@section('title', 'Notification Details')
@section('page-title', 'Notification Details')

@section('content')
<div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">{{ $notification->type }}</span>
                <h2 class="text-xl font-semibold mt-2">{{ $notification->title }}</h2>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">{{ $notification->created_at->format('M d, Y H:i') }}</p>
                @if($notification->is_read)
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs mt-1 inline-block">Read</span>
                @else
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs mt-1 inline-block">Unread</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Recipient</p>
                <p class="font-medium">{{ $notification->user?->name ?? 'All Users' }}</p>
                @if($notification->user)
                    <p class="text-sm text-gray-500">{{ $notification->user->email }}</p>
                @endif
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Read At</p>
                <p class="font-medium">{{ $notification->read_at?->format('M d, Y H:i') ?? 'Not read yet' }}</p>
            </div>
        </div>

        @if($notification->image_url)
        <div class="mb-6">
            <img src="{{ $notification->image_url }}" alt="" class="w-full h-48 object-cover rounded-lg">
        </div>
        @endif

        <div class="mb-6">
            <p class="text-sm text-gray-500 mb-2">Message</p>
            <div class="bg-gray-50 p-4 rounded-lg whitespace-pre-wrap">{{ $notification->body }}</div>
        </div>

        @if($notification->action_url)
        <div class="mb-6">
            <p class="text-sm text-gray-500 mb-1">Action URL</p>
            <a href="{{ $notification->action_url }}" target="_blank" class="text-blue-600 hover:underline">{{ $notification->action_url }}</a>
        </div>
        @endif

        @if($notification->data)
        <div class="mb-6">
            <p class="text-sm text-gray-500 mb-2">Additional Data</p>
            <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto">{{ json_encode($notification->data, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif

        <div class="flex items-center gap-4">
            <a href="{{ route('admin.notifications.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Back to Notifications
            </a>
            <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i>Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
