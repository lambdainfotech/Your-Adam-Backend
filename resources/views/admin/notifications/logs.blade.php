@extends('admin.layouts.master')

@section('title', 'Notification Logs')
@section('page-title', 'Notification Logs')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Email Logs -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="font-semibold"><i class="fas fa-envelope mr-2 text-blue-600"></i>Email Logs (Recent 50)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500">To</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500">Subject</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($emailLogs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $log->to_email }}</td>
                        <td class="px-4 py-3">{{ Str::limit($log->subject, 30) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ $log->status === 'sent' ? 'bg-green-100 text-green-800' : ($log->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $log->created_at->format('M d, H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No email logs</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SMS Logs -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="font-semibold"><i class="fas fa-sms mr-2 text-green-600"></i>SMS Logs (Recent 50)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500">Mobile</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500">Template</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($smsLogs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $log->mobile }}</td>
                        <td class="px-4 py-3">{{ $log->template }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ $log->status === 'sent' ? 'bg-green-100 text-green-800' : ($log->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $log->created_at->format('M d, H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No SMS logs</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
