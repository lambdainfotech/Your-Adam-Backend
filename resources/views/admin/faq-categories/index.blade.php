@extends('admin.layouts.master')

@section('title', 'FAQ Categories')
@section('page-title', 'FAQ Categories')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">FAQ Categories</h2>
            <p class="text-sm text-gray-500 mt-1">Manage FAQ categories and their order</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.faqs.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                <i class="fas fa-question-circle mr-2"></i>FAQ Items
            </a>
            <a href="{{ route('admin.faq-categories.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Add Category
            </a>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-12">Order</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">FAQs</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="categories-list">
                    @forelse($categories as $category)
                    <tr class="hover:bg-gray-50 category-row" data-id="{{ $category->id }}">
                        <td class="px-6 py-4">
                            <span class="drag-handle cursor-move text-gray-400 hover:text-gray-600">
                                <i class="fas fa-grip-vertical"></i>
                            </span>
                            <span class="sort-order text-xs text-gray-400 ml-2">{{ $category->sort_order }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($category->icon)
                                    <i class="fas fa-{{ $category->icon }} text-blue-500 w-5 text-center"></i>
                                @else
                                    <i class="fas fa-folder text-gray-400 w-5 text-center"></i>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $category->name }}</p>
                                    @if($category->description)
                                        <p class="text-xs text-gray-500 truncate max-w-xs">{{ $category->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $category->slug }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $category->faqs_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('admin.faq-categories.edit', $category) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.faq-categories.toggle-status', $category) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="{{ $category->is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}" title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas {{ $category->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.faq-categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Are you sure? This category must be empty to delete.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-folder-open text-4xl mb-3"></i>
                            <p>No FAQ categories found</p>
                            <a href="{{ route('admin.faq-categories.create') }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">Create your first category</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .sortable-ghost { opacity: 0.5; background-color: #f3f4f6; }
    .sortable-drag { opacity: 0.8; transform: scale(1.02); }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    const list = document.getElementById('categories-list');
    if (list && list.children.length > 1) {
        new Sortable(list, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function() {
                const items = [];
                document.querySelectorAll('#categories-list .category-row').forEach((row, index) => {
                    items.push({ id: row.dataset.id, sort_order: index + 1 });
                    row.querySelector('.sort-order').textContent = index + 1;
                });

                fetch('{{ route('admin.faq-categories.reorder') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ items: items })
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          Toast.show('Order updated successfully', 'success');
                      }
                  });
            }
        });
    }
</script>
@endpush
