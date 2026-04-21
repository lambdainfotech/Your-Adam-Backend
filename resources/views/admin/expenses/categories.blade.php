@extends('admin.layouts.master')

@section('title', 'Expense Categories')
@section('page-title', 'Expense Categories')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Expense Categories</h2>
            <p class="text-gray-500 mt-1">Manage expense categories</p>
        </div>
    </div>

    <!-- Add Category Form -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Add New Category</h3>
        <form action="{{ route('admin.expenses.categories.store') }}" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <input type="text" name="description" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Icon (FontAwesome)</label>
                <input type="text" name="icon" placeholder="fa-building" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                <input type="color" name="color" value="#3B82F6" class="w-16 h-10 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                <input type="number" name="sort_order" value="0" min="0" class="w-24 px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Add
            </button>
        </form>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Icon</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Color</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sort</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($categories as $category)
                        <tr>
                            <td class="px-6 py-3">
                                <div class="font-medium text-gray-900">{{ $category->name }}</div>
                                <div class="text-xs text-gray-500">{{ $category->description }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <i class="fas {{ $category->icon }} text-lg" style="color: {{ $category->color }}"></i>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded" style="background-color: {{ $category->color }}"></div>
                                    <span class="text-sm text-gray-600">{{ $category->color }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3">{{ $category->sort_order }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <button onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->description }}', '{{ $category->icon }}', '{{ $category->color }}', {{ $category->sort_order }}, {{ $category->is_active ? 1 : 0 }})"
                                        class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.expenses.categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg max-w-lg w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Edit Category</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_category_id" name="category_id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                <input type="text" id="edit_name" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <input type="text" id="edit_description" name="description" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Icon</label>
                    <input type="text" id="edit_icon" name="icon" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                    <input type="color" id="edit_color" name="color" class="w-full h-10 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort</label>
                    <input type="number" id="edit_sort_order" name="sort_order" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                <label for="edit_is_active" class="ml-2 text-sm text-gray-700">Active</label>
            </div>
            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update</button>
                <button type="button" onclick="closeEditModal()" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function editCategory(id, name, description, icon, color, sortOrder, isActive) {
        const form = document.getElementById('editForm');
        form.action = `/admin/expenses/categories/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description || '';
        document.getElementById('edit_icon').value = icon || '';
        document.getElementById('edit_color').value = color || '#3B82F6';
        document.getElementById('edit_sort_order').value = sortOrder;
        document.getElementById('edit_is_active').checked = isActive === 1;
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    }
</script>
@endpush
@endsection
