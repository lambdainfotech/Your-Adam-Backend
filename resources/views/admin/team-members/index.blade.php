@extends('admin.layouts.master')

@section('title', 'Team Members')
@section('page-title', 'Team Members')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Team Members</h2>
            <p class="text-sm text-gray-500 mt-1">Manage team members displayed on the about page</p>
        </div>
        <a href="{{ route('admin.team-members.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Member
        </a>
    </div>

    <!-- Members Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="members-list">
        @forelse($teamMembers as $member)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden member-card" data-id="{{ $member->id }}">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                            @if($member->photo)
                                <img src="{{ $member->photo }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-user text-2xl text-gray-400"></i>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $member->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $member->designation }}</p>
                        </div>
                    </div>
                    <span class="drag-handle cursor-move text-gray-400 hover:text-gray-600">
                        <i class="fas fa-grip-vertical"></i>
                    </span>
                </div>

                @if($member->bio)
                <p class="mt-4 text-sm text-gray-600 line-clamp-3">{{ $member->bio }}</p>
                @endif

                <div class="mt-4 flex items-center gap-3">
                    @if($member->email)
                    <a href="mailto:{{ $member->email }}" class="text-gray-400 hover:text-blue-600">
                        <i class="fas fa-envelope"></i>
                    </a>
                    @endif
                    @if($member->linkedin)
                    <a href="{{ $member->linkedin }}" target="_blank" class="text-gray-400 hover:text-blue-600">
                        <i class="fab fa-linkedin"></i>
                    </a>
                    @endif
                    @if($member->twitter)
                    <a href="{{ $member->twitter }}" target="_blank" class="text-gray-400 hover:text-blue-400">
                        <i class="fab fa-twitter"></i>
                    </a>
                    @endif
                </div>

                <div class="mt-4 flex items-center justify-between pt-4 border-t border-gray-100">
                    <span class="px-2 py-1 text-xs rounded-full {{ $member->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $member->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.team-members.edit', $member) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.team-members.toggle-status', $member) }}" class="inline">
                            @csrf
                            <button type="submit" class="{{ $member->is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                <i class="fas {{ $member->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.team-members.destroy', $member) }}" class="inline" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="md:col-span-2 lg:col-span-3 py-12 text-center text-gray-400">
            <i class="fas fa-users text-4xl mb-3"></i>
            <p>No team members found</p>
            <a href="{{ route('admin.team-members.create') }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">Add your first team member</a>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
    .sortable-ghost { opacity: 0.5; }
    .sortable-drag { opacity: 0.8; transform: scale(1.02); }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    const list = document.getElementById('members-list');
    if (list && list.children.length > 1) {
        new Sortable(list, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function() {
                const items = [];
                document.querySelectorAll('#members-list .member-card').forEach((card, index) => {
                    items.push({ id: card.dataset.id, sort_order: index + 1 });
                });

                fetch('{{ route('admin.team-members.reorder') }}', {
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
