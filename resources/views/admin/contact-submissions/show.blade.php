@extends('admin.layouts.master')

@section('title', 'Contact Submission Details')
@section('page-title', 'Contact Submission Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.contact-submissions.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Back to Submissions
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <div class="flex items-center gap-3">
                @if($submission->read_at)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check mr-1"></i> Read
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        <i class="fas fa-envelope mr-1"></i> Unread
                    </span>
                @endif
                <span class="text-sm text-gray-500">Submitted on {{ $submission->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="flex gap-2">
                @if(!$submission->read_at)
                <form action="{{ route('admin.contact-submissions.mark-read', $submission) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 text-sm">
                        <i class="fas fa-check mr-1"></i>Mark as Read
                    </button>
                </form>
                @endif
                <form action="{{ route('admin.contact-submissions.destroy', $submission) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this submission?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm">
                        <i class="fas fa-trash mr-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>

        <!-- Details -->
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Name</label>
                    <p class="text-lg font-medium text-gray-900">{{ $submission->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                    <p class="text-lg font-medium text-gray-900">
                        <a href="mailto:{{ $submission->email }}" class="text-blue-600 hover:underline">{{ $submission->email }}</a>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Phone</label>
                    <p class="text-lg font-medium text-gray-900">
                        @if($submission->phone)
                            <a href="tel:{{ $submission->phone }}" class="text-blue-600 hover:underline">{{ $submission->phone }}</a>
                        @else
                            <span class="text-gray-400">Not provided</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Subject</label>
                    <p class="text-lg font-medium text-gray-900">{{ $submission->subject }}</p>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <label class="block text-sm font-medium text-gray-500 mb-2">Message</label>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-800 whitespace-pre-wrap">{{ $submission->message }}</p>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <label class="block text-sm font-medium text-gray-500 mb-2">Technical Info</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-500">
                    <div>
                        <span class="font-medium">IP Address:</span> {{ $submission->ip_address ?? 'N/A' }}
                    </div>
                    <div>
                        <span class="font-medium">User Agent:</span> {{ $submission->user_agent ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Reply -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h3 class="text-lg font-semibold mb-4">Quick Reply</h3>
        <a href="mailto:{{ $submission->email }}?subject=Re: {{ $submission->subject }}" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-reply mr-2"></i>Reply via Email
        </a>
    </div>
</div>
@endsection
