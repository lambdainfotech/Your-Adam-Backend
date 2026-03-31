@extends('admin.layouts.master')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex items-center gap-2">
            <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                <option value="system" {{ request('type') == 'system' ? 'selected' : '' }}>System</option>
                <option value="order" {{ request('type') == 'order' ? 'selected' : '' }}>Order</option>
                <option value="promotion" {{ request('type') == 'promotion' ? 'selected' : '' }}>Promotion</option>
            </select>
            <select name="is_read" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="0" {{ request('is_read') === '0' ? 'selected' : '' }}>Unread</option>
                <option value="1" {{ request('is_read') === '1' ? 'selected' : '' }}>Read</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"><i class="fas fa-search"></i></button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('admin.notifications.logs') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"><i class="fas fa-history mr-2"></i>Logs</a>
            <a href="{{ route('admin.notifications.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"><i class="fas fa-plus mr-2"></i>Send Notification</a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($notifications as $notification)
                <tr class="hover:bg-gray-50 {{ !$notification->is_read ? 'bg-blue-50' : '' }}">
                    <td class="px-6 py-4 text-sm">{{ $notification->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-6 py-4">{{ $notification->user?->name ?? 'All Users' }}</td>
                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full bg-gray-100">{{ $notification->type }}</span></td>
                    <td class="px-6 py-4">{{ Str::limit($notification->title, 40) }}</td>
                    <td class="px-6 py-4">
                        @if($notification->is_read)
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Read</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Unread</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.notifications.show', $notification) }}" class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No notifications found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200">{{ $notifications->links() }}</div>
</div>
@endsection
