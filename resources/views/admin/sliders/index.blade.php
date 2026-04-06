@extends('admin.layouts.master')

@section('title', 'Sliders')
@section('page-title', 'Slider Management')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <h2 class="text-xl font-semibold text-gray-800">All Sliders</h2>
        <a href="{{ route('admin.sliders.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Slider
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Preview</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Title & Subtitle</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Button</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sliders as $slider)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        @if($slider->banner_image_url)
                            <img src="{{ $slider->banner_image_url }}" alt="{{ $slider->title }}" 
                                class="h-16 w-32 object-cover rounded-lg">
                        @else
                            <div class="h-16 w-32 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $slider->title }}</div>
                        @if($slider->subtitle)
                            <div class="text-sm text-gray-500">{{ Str::limit($slider->subtitle, 50) }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($slider->has_button)
                            <span class="px-3 py-1 text-xs rounded-lg" 
                                style="color: {{ $slider->button_text_color }}; background-color: {{ $slider->button_bg_color }}">
                                {{ $slider->button_text }}
                            </span>
                        @else
                            <span class="text-gray-400 text-sm">No button</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($slider->is_active)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs bg-gray-100 rounded">{{ $slider->sort_order }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.sliders.edit', $slider) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.sliders.toggle-status', $slider) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-{{ $slider->is_active ? 'red' : 'green' }}-600 hover:text-{{ $slider->is_active ? 'red' : 'green' }}-800" title="{{ $slider->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $slider->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this slider?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-images text-4xl mb-4 text-gray-300"></i>
                        <p>No sliders found</p>
                        <a href="{{ route('admin.sliders.create') }}" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">Create your first slider</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200">{{ $sliders->links() }}</div>
</div>
@endsection
