@extends('admin.layouts.master')

@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <select name="action" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                @endforeach
            </select>
            <select name="entity_type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Entities</option>
                @foreach($entityTypes as $type)
                    <option value="{{ $type }}" {{ request('entity_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"><i class="fas fa-search"></i> Filter</button>
            @if(request()->hasAny(['action', 'entity_type', 'date_from', 'date_to']))
                <a href="{{ route('admin.activity-logs.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i> Clear</a>
            @endif
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Time</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Action</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Entity</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm">{{ $log->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-6 py-4">{{ $log->user?->name ?? 'System' }}</td>
                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">{{ $log->action }}</span></td>
                    <td class="px-6 py-4 text-sm">{{ $log->entity_type }} #{{ $log->entity_id }}</td>
                    <td class="px-6 py-4 text-sm">{{ Str::limit($log->description, 50) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $log->ip_address }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No activity logs found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200">{{ $logs->links() }}</div>
</div>
@endsection
