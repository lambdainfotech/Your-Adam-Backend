@extends('admin.layouts.master')

@section('title', 'SMS Settings')
@section('page-title', 'SMS Configuration')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-1">
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200 font-semibold">Settings</div>
            <nav class="p-2">
                <a href="{{ route('admin.settings.general') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-cog w-6"></i> General</a>
                <a href="{{ route('admin.settings.store') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-store w-6"></i> Store Information</a>
                <a href="{{ route('admin.settings.email') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-envelope w-6"></i> Email Configuration</a>
                <a href="{{ route('admin.settings.sms') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 bg-blue-50 text-blue-600"><i class="fas fa-sms w-6"></i> SMS Configuration</a>
                <a href="{{ route('admin.settings.payment') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-credit-card w-6"></i> Payment Settings</a>
                <a href="{{ route('admin.settings.shipping') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-shipping-fast w-6"></i> Shipping Settings</a>
                <a href="{{ route('admin.settings.seo') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-search w-6"></i> SEO Settings</a>
                <a href="{{ route('admin.settings.social') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-share-alt w-6"></i> Social Media</a>
                <a href="{{ route('admin.settings.footer') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fas fa-shoe-prints w-6"></i> Footer Settings</a>
            </nav>
        </div>
    </div>

    <div class="md:col-span-2 space-y-6">
        <!-- SMS Gateway Configuration -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Muthobarta SMS Gateway</h2>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('admin.settings.sms') }}">
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">API Key <span class="text-red-500">*</span></label>
                    <input type="text" name="settings[sms_muthobarta_api_key]" value="{{ $settings['sms_muthobarta_api_key'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Your Muthobarta API Key" required>
                    <p class="text-xs text-gray-500 mt-1">Get this from your <a href="https://sysadmin.muthobarta.com" target="_blank" class="text-blue-600 hover:underline">Muthobarta Dashboard</a></p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sender ID</label>
                    <input type="text" name="settings[sms_sender_id]" value="{{ $settings['sms_sender_id'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="e.g., 1234">
                    <p class="text-xs text-gray-500 mt-1">Optional sender ID for branded SMS</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">OTP Message Template</label>
                    <textarea name="settings[sms_otp_template]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Your verification code is: {code}. It will expire in 5 minutes.">{{ $settings['sms_otp_template'] ?? 'Your verification code is: {code}. It will expire in 5 minutes.' }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Use <code>{code}</code> as placeholder for the OTP code</p>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Settings</button>
                </div>
            </form>
        </div>

        <!-- Test SMS -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-6">Send Test SMS</h2>
            <form id="testSmsForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Number</label>
                    <input type="text" id="testMobile" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="017XXXXXXXX">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea id="testMessage" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">This is a test SMS from your e-commerce store.</textarea>
                </div>
                <button type="button" onclick="sendTestSms()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-paper-plane mr-2"></i>Send Test
                </button>
                <div id="testResult" class="hidden mt-4 p-4 rounded-lg"></div>
            </form>
        </div>
    </div>
</div>

<script>
async function sendTestSms() {
    const mobile = document.getElementById('testMobile').value;
    const message = document.getElementById('testMessage').value;
    const resultDiv = document.getElementById('testResult');
    
    if (!mobile || !message) {
        resultDiv.className = 'mt-4 p-4 rounded-lg bg-red-50 text-red-700';
        resultDiv.textContent = 'Please enter both mobile number and message.';
        resultDiv.classList.remove('hidden');
        return;
    }
    
    resultDiv.className = 'mt-4 p-4 rounded-lg bg-blue-50 text-blue-700';
    resultDiv.textContent = 'Sending...';
    resultDiv.classList.remove('hidden');
    
    try {
        const response = await fetch('/admin/settings/sms/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            },
            body: JSON.stringify({ mobile, message })
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 rounded-lg bg-green-50 text-green-700';
            resultDiv.textContent = 'SMS sent successfully! ' + (data.message || '');
        } else {
            resultDiv.className = 'mt-4 p-4 rounded-lg bg-red-50 text-red-700';
            resultDiv.textContent = 'Failed: ' + (data.message || 'Unknown error');
        }
    } catch (error) {
        resultDiv.className = 'mt-4 p-4 rounded-lg bg-red-50 text-red-700';
        resultDiv.textContent = 'Error: ' + error.message;
    }
}
</script>
@endsection
