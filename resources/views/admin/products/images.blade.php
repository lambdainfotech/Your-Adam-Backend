@extends('admin.layouts.master')

@section('title', 'Product Images')
@section('page-title', 'Manage Images: ' . $product->name)

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.products.show', $product) }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Product
            </a>
        </div>
        <div class="flex items-center space-x-3">
            <span class="text-gray-500 text-sm">
                {{ $images->count() }} image{{ $images->count() !== 1 ? 's' : '' }}
            </span>
            <a href="{{ route('admin.products.edit', $product) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                <i class="fas fa-edit mr-2"></i>Edit Product
            </a>
        </div>
    </div>

    <!-- Upload Area -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-cloud-upload-alt text-blue-600"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-800">Upload Images</h3>
                <p class="text-sm text-gray-500">Drag & drop or click to select (Max 5MB each, Max 10 images)</p>
            </div>
        </div>

        <form id="uploadForm" action="{{ route('admin.products.images.store', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-pointer">
                <input type="file" name="images[]" id="imageInput" multiple accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden">
                <div id="dropZoneContent">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-600 mb-2">Drag and drop images here</p>
                    <p class="text-gray-400 text-sm mb-4">or</p>
                    <button type="button" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" onclick="document.getElementById('imageInput').click()">
                        <i class="fas fa-folder-open mr-2"></i>Select Files
                    </button>
                </div>
                <div id="uploadPreview" class="hidden">
                    <div id="previewGrid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4"></div>
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-upload mr-2"></i>Upload <span id="uploadCount">0</span> Image(s)
                    </button>
                    <button type="button" id="cancelUpload" class="ml-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </form>

        <!-- Upload Progress -->
        <div id="uploadProgress" class="hidden mt-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-600">Uploading...</span>
                <span id="progressPercent" class="text-sm font-medium text-blue-600">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- Image Gallery -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Image Gallery</h3>
            @if($images->count() > 0)
                <span class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Drag to reorder, click ★ to set main
                </span>
            @endif
        </div>

        @if($images->count() > 0)
            <div class="p-6">
                <div id="imageGallery" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @foreach($images as $image)
                        <div class="image-card group relative bg-gray-100 rounded-lg overflow-hidden cursor-move" data-id="{{ $image->id }}">
                            <!-- Image -->
                            <div class="aspect-square">
                                <img src="{{ $image->thumbnail_url ?? $image->image_url }}" 
                                     alt="{{ $image->alt_text }}" 
                                     class="w-full h-full object-cover">
                            </div>

                            <!-- Main Image Badge -->
                            @if($image->is_main)
                                <div class="absolute top-2 left-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">
                                    <i class="fas fa-star mr-1"></i>Main
                                </div>
                            @endif

                            <!-- Overlay Actions -->
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <div class="flex items-center space-x-2">
                                    <!-- View -->
                                    <button type="button" onclick="viewImage('{{ $image->image_url }}', '{{ $image->alt_text }}')" 
                                            class="w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-gray-100" title="View">
                                        <i class="fas fa-eye text-gray-700"></i>
                                    </button>
                                    
                                    <!-- Set Main -->
                                    @if(!$image->is_main)
                                        <form action="{{ route('admin.products.images.main', [$product, $image]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center hover:bg-yellow-600" title="Set as Main">
                                                <i class="fas fa-star text-white"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <!-- Edit Alt -->
                                    <button type="button" onclick="editAltText({{ $image->id }}, '{{ $image->alt_text }}')" 
                                            class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center hover:bg-blue-600" title="Edit Alt Text">
                                        <i class="fas fa-edit text-white"></i>
                                    </button>
                                    
                                    <!-- Delete -->
                                    <form action="{{ route('admin.products.images.destroy', [$product, $image]) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this image?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center hover:bg-red-600" title="Delete">
                                            <i class="fas fa-trash text-white"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Drag Handle -->
                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="w-8 h-8 bg-black bg-opacity-50 rounded flex items-center justify-center cursor-move">
                                    <i class="fas fa-grip-vertical text-white"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Reorder Button -->
                <div class="mt-4 text-center">
                    <button type="button" id="saveOrder" class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 hidden">
                        <i class="fas fa-save mr-2"></i>Save Order
                    </button>
                </div>
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-images text-gray-400 text-3xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-800 mb-2">No images yet</h4>
                <p class="text-gray-500">Upload images to showcase your product</p>
            </div>
        @endif
    </div>
</div>

<!-- Image Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-90 hidden items-center justify-center z-50">
    <button type="button" onclick="closePreview()" class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300">
        <i class="fas fa-times"></i>
    </button>
    <img id="previewImage" src="" alt="" class="max-w-[90%] max-h-[90vh] object-contain">
    <p id="previewAlt" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white text-sm"></p>
</div>

<!-- Edit Alt Text Modal -->
<div id="altTextModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Edit Alt Text</h3>
        <form id="altTextForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Alt Text (for SEO & accessibility)</label>
                <input type="text" name="alt_text" id="altTextInput" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Describe the image">
                <p class="text-xs text-gray-500 mt-1">Helps with search engine optimization and screen readers</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeAltModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .image-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .image-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .image-card.sortable-ghost {
        opacity: 0.5;
    }
    .image-card.sortable-drag {
        opacity: 1;
        transform: rotate(3deg);
    }
    #dropZone.dragover {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    // Drop Zone Handling
    const dropZone = document.getElementById('dropZone');
    const imageInput = document.getElementById('imageInput');
    const dropZoneContent = document.getElementById('dropZoneContent');
    const uploadPreview = document.getElementById('uploadPreview');
    const previewGrid = document.getElementById('previewGrid');
    const uploadCount = document.getElementById('uploadCount');
    let selectedFiles = [];

    // Click to select
    dropZone.addEventListener('click', (e) => {
        if (e.target !== imageInput && !e.target.closest('button')) {
            imageInput.click();
        }
    });

    // Drag & Drop events
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('dragover');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        handleFiles(files);
    }, false);

    imageInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    function handleFiles(files) {
        selectedFiles = Array.from(files).filter(file => file.type.startsWith('image/'));
        
        if (selectedFiles.length > 0) {
            showPreview();
        }
    }

    function showPreview() {
        dropZoneContent.classList.add('hidden');
        uploadPreview.classList.remove('hidden');
        previewGrid.innerHTML = '';
        uploadCount.textContent = selectedFiles.length;

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const div = document.createElement('div');
                div.className = 'relative aspect-square bg-gray-100 rounded-lg overflow-hidden';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <button type="button" onclick="removeFile(${index})" class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                previewGrid.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    document.getElementById('cancelUpload').addEventListener('click', () => {
        selectedFiles = [];
        imageInput.value = '';
        dropZoneContent.classList.remove('hidden');
        uploadPreview.classList.add('hidden');
    });

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        if (selectedFiles.length === 0) {
            document.getElementById('cancelUpload').click();
        } else {
            showPreview();
        }
    }

    // Form submission with progress
    document.getElementById('uploadForm').addEventListener('submit', (e) => {
        e.preventDefault();
        
        const formData = new FormData();
        selectedFiles.forEach(file => {
            formData.append('images[]', file);
        });
        formData.append('_token', '{{ csrf_token() }}');

        const progressDiv = document.getElementById('uploadProgress');
        const progressBar = document.getElementById('progressBar');
        const progressPercent = document.getElementById('progressPercent');
        
        progressDiv.classList.remove('hidden');

        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percentComplete + '%';
                progressPercent.textContent = percentComplete + '%';
            }
        });

        xhr.addEventListener('load', () => {
            if (xhr.status === 200) {
                window.location.reload();
            } else {
                alert('Upload failed. Please try again.');
                progressDiv.classList.add('hidden');
            }
        });

        xhr.addEventListener('error', () => {
            alert('Upload failed. Please try again.');
            progressDiv.classList.add('hidden');
        });

        xhr.open('POST', '{{ route('admin.products.images.store', $product) }}');
        xhr.send(formData);
    });

    // Sortable Gallery
    const gallery = document.getElementById('imageGallery');
    let sortableInstance = null;
    let originalOrder = [];

    if (gallery) {
        originalOrder = Array.from(gallery.children).map(card => card.dataset.id);
        
        sortableInstance = new Sortable(gallery, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            handle: '.image-card',
            onEnd: function() {
                const currentOrder = Array.from(gallery.children).map(card => card.dataset.id);
                const hasChanged = JSON.stringify(originalOrder) !== JSON.stringify(currentOrder);
                
                document.getElementById('saveOrder').classList.toggle('hidden', !hasChanged);
            }
        });

        document.getElementById('saveOrder').addEventListener('click', () => {
            const order = Array.from(gallery.children).map(card => parseInt(card.dataset.id));
            
            fetch('{{ route('admin.products.images.reorder', $product) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ order })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    originalOrder = order.map(String);
                    document.getElementById('saveOrder').classList.add('hidden');
                    alert('Order saved successfully!');
                }
            })
            .catch(() => {
                alert('Failed to save order. Please try again.');
            });
        });
    }

    // Image Preview Modal
    function viewImage(url, alt) {
        document.getElementById('previewImage').src = url;
        document.getElementById('previewAlt').textContent = alt || '';
        document.getElementById('previewModal').classList.remove('hidden');
        document.getElementById('previewModal').classList.add('flex');
    }

    function closePreview() {
        document.getElementById('previewModal').classList.add('hidden');
        document.getElementById('previewModal').classList.remove('flex');
    }

    // Alt Text Modal
    function editAltText(imageId, currentAlt) {
        const form = document.getElementById('altTextForm');
        form.action = `{{ url('admin/products/' . $product->id . '/images') }}/${imageId}`;
        document.getElementById('altTextInput').value = currentAlt || '';
        document.getElementById('altTextModal').classList.remove('hidden');
        document.getElementById('altTextModal').classList.add('flex');
    }

    function closeAltModal() {
        document.getElementById('altTextModal').classList.add('hidden');
        document.getElementById('altTextModal').classList.remove('flex');
    }

    // Close modals on outside click
    document.getElementById('previewModal').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) closePreview();
    });

    document.getElementById('altTextModal').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) closeAltModal();
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closePreview();
            closeAltModal();
        }
    });
</script>
@endpush
