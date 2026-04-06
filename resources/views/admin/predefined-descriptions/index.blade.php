@extends('admin.layouts.master')

@section('title', 'Predefined Descriptions')

@push('styles')
<style>
    .description-card {
        transition: all 0.2s;
        cursor: grab;
    }
    .description-card:active {
        cursor: grabbing;
    }
    .description-card.sortable-ghost {
        opacity: 0.5;
        background-color: #f3f4f6;
    }
    .description-card.sortable-drag {
        opacity: 0.8;
        transform: scale(1.02);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .content-preview {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Predefined Descriptions</h1>
            <p class="text-gray-600 mt-1">Manage reusable product descriptions and short descriptions</p>
        </div>
        <a href="{{ route('admin.predefined-descriptions.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add New
        </a>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-t-lg border-b border-gray-200">
        <nav class="flex">
            <button type="button" onclick="switchTab('descriptions')" id="tab-descriptions" 
                class="px-6 py-3 text-sm font-medium border-b-2 border-blue-500 text-blue-600 bg-blue-50">
                <i class="fas fa-align-left mr-2"></i>Descriptions
                <span class="ml-2 bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full">{{ $descriptions->count() }}</span>
            </button>
            <button type="button" onclick="switchTab('short-descriptions')" id="tab-short-descriptions"
                class="px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                <i class="fas fa-align-justify mr-2"></i>Short Descriptions
                <span class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ $shortDescriptions->count() }}</span>
            </button>
        </nav>
    </div>

    <!-- Descriptions Tab -->
    <div id="panel-descriptions" class="bg-white rounded-b-lg shadow-sm p-6">
        @if($descriptions->count() > 0)
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Drag and drop to reorder. Click on a card to edit.
                </p>
                <span class="text-xs text-gray-400">{{ $descriptions->count() }} item(s)</span>
            </div>
            
            <div id="descriptions-list" class="space-y-3">
                @foreach($descriptions as $description)
                <div class="description-card bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md" data-id="{{ $description->id }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 pr-4">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="drag-handle cursor-grab text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-grip-vertical"></i>
                                </span>
                                <h3 class="font-medium text-gray-900">{{ $description->name }}</h3>
                                @if(!$description->is_active)
                                    <span class="px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded">Inactive</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 content-preview">{{ $description->content_preview }}</p>
                            <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                                <span><i class="fas fa-box mr-1"></i>{{ $description->products_count }} product(s) using this</span>
                                <span><i class="fas fa-clock mr-1"></i>{{ $description->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.predefined-descriptions.edit', $description) }}" 
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.predefined-descriptions.destroy', $description) }}" method="POST" class="inline" 
                                onsubmit="return confirm('Are you sure you want to delete this?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-align-left text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No descriptions yet</h3>
                <p class="text-gray-500 mb-4">Create predefined descriptions for quick product creation</p>
                <a href="{{ route('admin.predefined-descriptions.create') }}?type=description" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Create Description
                </a>
            </div>
        @endif
    </div>

    <!-- Short Descriptions Tab -->
    <div id="panel-short-descriptions" class="bg-white rounded-b-lg shadow-sm p-6 hidden">
        @if($shortDescriptions->count() > 0)
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Drag and drop to reorder. Click on a card to edit.
                </p>
                <span class="text-xs text-gray-400">{{ $shortDescriptions->count() }} item(s)</span>
            </div>
            
            <div id="short-descriptions-list" class="space-y-3">
                @foreach($shortDescriptions as $description)
                <div class="description-card bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md" data-id="{{ $description->id }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 pr-4">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="drag-handle cursor-grab text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-grip-vertical"></i>
                                </span>
                                <h3 class="font-medium text-gray-900">{{ $description->name }}</h3>
                                @if(!$description->is_active)
                                    <span class="px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded">Inactive</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 content-preview">{{ $description->content_preview }}</p>
                            <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                                <span><i class="fas fa-box mr-1"></i>{{ $description->products_count }} product(s) using this</span>
                                <span><i class="fas fa-clock mr-1"></i>{{ $description->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.predefined-descriptions.edit', $description) }}" 
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.predefined-descriptions.destroy', $description) }}" method="POST" class="inline" 
                                onsubmit="return confirm('Are you sure you want to delete this?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-align-justify text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No short descriptions yet</h3>
                <p class="text-gray-500 mb-4">Create predefined short descriptions for quick product creation</p>
                <a href="{{ route('admin.predefined-descriptions.create') }}?type=short_description" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Create Short Description
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    // Tab switching
    function switchTab(tab) {
        // Hide all panels
        document.getElementById('panel-descriptions').classList.add('hidden');
        document.getElementById('panel-short-descriptions').classList.add('hidden');
        
        // Reset all tab styles
        document.getElementById('tab-descriptions').classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50');
        document.getElementById('tab-descriptions').classList.add('border-transparent', 'text-gray-500');
        document.getElementById('tab-short-descriptions').classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50');
        document.getElementById('tab-short-descriptions').classList.add('border-transparent', 'text-gray-500');
        
        // Show selected panel and style tab
        document.getElementById('panel-' + tab).classList.remove('hidden');
        const activeTab = document.getElementById('tab-' + tab);
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        activeTab.classList.add('border-blue-500', 'text-blue-600', 'bg-blue-50');
    }

    // Initialize sortable for both lists
    document.addEventListener('DOMContentLoaded', function() {
        const descriptionsList = document.getElementById('descriptions-list');
        const shortDescriptionsList = document.getElementById('short-descriptions-list');
        
        if (descriptionsList) {
            new Sortable(descriptionsList, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function() {
                    updateOrder('descriptions-list');
                }
            });
        }
        
        if (shortDescriptionsList) {
            new Sortable(shortDescriptionsList, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function() {
                    updateOrder('short-descriptions-list');
                }
            });
        }
    });

    // Update sort order via AJAX
    function updateOrder(listId) {
        const items = [];
        document.querySelectorAll('#' + listId + ' .description-card').forEach((card, index) => {
            items.push({
                id: card.dataset.id,
                sort_order: index + 1
            });
        });

        fetch('{{ route('admin.predefined-descriptions.reorder') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ items: items })
        });
    }
</script>
@endpush
