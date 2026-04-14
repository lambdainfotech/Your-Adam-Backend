@extends('admin.layouts.master')

@section('title', 'Social Media Settings')
@section('page-title', 'Social Media Settings')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-1">
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200 font-semibold">Settings</div>
            <nav class="p-2">
                <a href="{{ route('admin.settings.general') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-cog w-6"></i> General</a>
                <a href="{{ route('admin.settings.store') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-store w-6"></i> Store Information</a>
                <a href="{{ route('admin.settings.email') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-envelope w-6"></i> Email Configuration</a>
                <a href="{{ route('admin.settings.sms') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 {{ request()->routeIs('admin.settings.sms') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}"><i class="fas fa-sms w-6"></i> SMS Configuration</a>
                <a href="{{ route('admin.settings.payment') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-credit-card w-6"></i> Payment Settings</a>
                <a href="{{ route('admin.settings.shipping') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-shipping-fast w-6"></i> Shipping Settings</a>
                <a href="{{ route('admin.settings.seo') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-search w-6"></i> SEO Settings</a>
                <a href="{{ route('admin.settings.social') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 bg-blue-50 text-blue-600"><i class="fas fa-share-alt w-6"></i> Social Media</a>
                <a href="{{ route('admin.settings.footer') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-shoe-prints w-6"></i> Footer Settings</a>
            </nav>
        </div>
    </div>

    <div class="md:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold">Social Media Links</h2>
                <button type="button" onclick="addSocialLink()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-plus mr-2"></i>Add New
                </button>
            </div>
            
            <form action="{{ route('admin.settings.update') }}" method="POST" id="socialForm">
                @csrf
                <div id="socialLinksContainer">
                    @forelse($socialLinks as $platform => $url)
                    <div class="social-link-row grid grid-cols-12 gap-4 mb-4 items-center">
                        <div class="col-span-4">
                            <input type="text" name="social_platforms[]" value="{{ $platform }}" placeholder="Platform (e.g., facebook)" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="col-span-7">
                            <input type="url" name="social_urls[]" value="{{ $url }}" placeholder="https://..." 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="col-span-1">
                            <button type="button" onclick="removeSocialLink(this)" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="social-link-row grid grid-cols-12 gap-4 mb-4 items-center">
                        <div class="col-span-4">
                            <input type="text" name="social_platforms[]" placeholder="Platform (e.g., facebook)" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="col-span-7">
                            <input type="url" name="social_urls[]" placeholder="https://facebook.com/youradam" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="col-span-1">
                            <button type="button" onclick="removeSocialLink(this)" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    @endforelse
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-2"><i class="fas fa-info-circle mr-2"></i>Available Platforms</h3>
                    <p class="text-sm text-gray-500">
                        Common platforms: facebook, instagram, twitter, youtube, linkedin, tiktok, discord, telegram, pinterest, snapchat, github
                    </p>
                </div>
                
                <div class="flex items-center gap-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Social Links</button>
                </div>
            </form>
        </div>
        
        <!-- Preview Card -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-lg font-semibold mb-4">Current Social Links</h2>
            <div class="flex flex-wrap gap-4">
                @forelse($socialLinks as $platform => $url)
                    <a href="{{ $url }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        <i class="fab fa-{{ $platform }} mr-2 text-lg"></i>
                        <span class="capitalize">{{ $platform }}</span>
                    </a>
                @empty
                    <p class="text-gray-500">No social links configured yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function addSocialLink() {
    const container = document.getElementById('socialLinksContainer');
    const row = document.createElement('div');
    row.className = 'social-link-row grid grid-cols-12 gap-4 mb-4 items-center';
    row.innerHTML = `
        <div class="col-span-4">
            <input type="text" name="social_platforms[]" placeholder="Platform (e.g., facebook)" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div class="col-span-7">
            <input type="url" name="social_urls[]" placeholder="https://..." 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div class="col-span-1">
            <button type="button" onclick="removeSocialLink(this)" class="text-red-500 hover:text-red-700">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
}

function removeSocialLink(button) {
    const rows = document.querySelectorAll('.social-link-row');
    if (rows.length > 1) {
        button.closest('.social-link-row').remove();
    } else {
        alert('At least one social link row is required. Clear the fields instead.');
    }
}

// Transform form data before submit
document.getElementById('socialForm').addEventListener('submit', function(e) {
    const platforms = document.querySelectorAll('input[name="social_platforms[]"]');
    const urls = document.querySelectorAll('input[name="social_urls[]"]');
    
    platforms.forEach((platform, index) => {
        if (platform.value && urls[index].value) {
            // Create hidden input for settings[social_platform]
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `settings[social_${platform.value.toLowerCase().trim()}]`;
            hiddenInput.value = urls[index].value.trim();
            this.appendChild(hiddenInput);
        }
    });
    
    // Remove the original arrays so they don't conflict
    platforms.forEach(p => p.removeAttribute('name'));
    urls.forEach(u => u.removeAttribute('name'));
});
</script>
@endpush
