@extends('admin.layouts.master')

@section('title', 'Open POS Session')
@section('page-title', 'Open POS Session')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-cash-register text-white text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Open POS Session</h2>
            <p class="text-gray-500 mt-1">Enter opening cash amount to start</p>
        </div>

        <form action="{{ route('admin.pos.session.store') }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Opening Cash Amount (৳)</label>
                <input type="number" name="opening_amount" required min="0" step="0.01"
                    class="w-full px-4 py-3 text-2xl font-bold text-center border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                    placeholder="0.00"
                    autofocus>
                <p class="text-sm text-gray-500 mt-2">Enter the cash amount in your drawer</p>
                @error('opening_amount')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Opening Note (Optional)</label>
                <textarea name="opening_note" rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Any notes about this session..."></textarea>
            </div>

            <button type="submit" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold text-lg">
                <i class="fas fa-play mr-2"></i>Start Session
            </button>

            <div class="mt-4 text-center">
                <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Back to Dashboard
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    body { background-color: #f3f4f6; }
</style>
@endpush
