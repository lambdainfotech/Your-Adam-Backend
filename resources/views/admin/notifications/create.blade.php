@extends('admin.layouts.master')

@section('title', 'Send Notification')
@section('page-title', 'Send Notification')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.notifications.store') }}" method="POST">
        @csrf
        <div class="mb-6">
            <label class="flex items-center mb-4">
                <input type="checkbox" name="send_to_all" value="1" class="w-4 h-4 text-blue-600 rounded" onchange="document.getElementById('user-select').classList.toggle('hidden', this.checked)">
                <span class="ml-2 font-medium">Send to all users</span>
            </label>
            <div id="user-select">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Users</label>
                <select name="user_ids[]" multiple class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" size="5">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                <p class="text-sm text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type *</label>
                <select name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    @foreach($types as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Image URL</label>
                <input type="text" name="image_url" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
            <input type="text" name="title" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
            <textarea name="body" required rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Action URL</label>
            <input type="text" name="action_url" placeholder="https://example.com/page" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-center gap-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Send Notification</button>
            <a href="{{ route('admin.notifications.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
