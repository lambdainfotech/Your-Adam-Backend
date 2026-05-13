@extends('admin.layouts.master')

@section('title', 'FAQs')
@section('page-title', 'FAQs')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">FAQ Items</h2>
            <p class="text-sm text-gray-500 mt-1">Manage frequently asked questions</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.faq-categories.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                <i class="fas fa-folder mr-2"></i>Categories
            </a>
            <a href="{{ route('admin.faqs.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Add FAQ
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
        <form action="{{ route('admin.faqs.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Question or answer..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </div>
            @if(request()->hasAny(['search', 'category']))
            <div>
                <a href="{{ route('admin.faqs.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- FAQs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-12">Order</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Question</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="faqs-list">
                    @forelse($faqs as $faq)
                    <tr class="hover:bg-gray-50 faq-row" data-id="{{ $faq->id }}">
                        <td class="px-6 py-4">
                            <span class="drag-handle cursor-move text-gray-400 hover:text-gray-600">
                                <i class="fas fa-grip-vertical"></i>
                            </span>
                            <span class="sort-order text-xs text-gray-400 ml-2">{{ $faq->sort_order }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-900">{{ $faq->question }}</p>
                            <p class="text-xs text-gray-500 truncate max-w-md">{{ strip_tags($faq->answer) }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($faq->category)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    @if($faq->category->icon)
                                        <i class="fas fa-{{ $faq->category->icon }} mr-1 text-xs"></i>
                                    @endif
                                    {{ $faq->category->name }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">Uncategorized</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $faq->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $faq->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('admin.faqs.edit', $faq) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.faqs.toggle-status', $faq) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="{{ $faq->is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}" title="{{ $faq->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas {{ $faq->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" class="inline" onsubmit="return confirm('Are you sure?');">
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
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-question-circle text-4xl mb-3"></i>
                            <p>No FAQs found</p>
                            <a href="{{ route('admin.faqs.create') }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">Create your first FAQ</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($faqs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $faqs->links() }}
        </div>
        @endif
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
    const list = document.getElementById('faqs-list');
    if (list && list.children.length > 1) {
        new Sortable(list, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function() {
                const items = [];
                document.querySelectorAll('#faqs-list .faq-row').forEach((row, index) => {
                    items.push({ id: row.dataset.id, sort_order: index + 1 });
                    row.querySelector('.sort-order').textContent = index + 1;
                });

                fetch('{{ route('admin.faqs.reorder') }}', {
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
