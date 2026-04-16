<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        
        $groups = [
            'general' => 'General Settings',
            'store' => 'Store Information',
            'contact' => 'Contact Details',
            'social' => 'Social Media',
            'email' => 'Email Configuration',
            'sms' => 'SMS Configuration',
            'payment' => 'Payment Settings',
            'shipping' => 'Shipping Settings',
            'seo' => 'SEO Settings',
        ];
        
        return view('admin.settings.index', compact('settings', 'groups'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable',
        ]);
        
        foreach ($validated['settings'] as $key => $value) {
            // Handle array values (like payment methods)
            if (is_array($value)) {
                $value = json_encode($value);
            }
            Setting::set($key, $value);
        }
        
        // Clear settings cache
        Cache::forget('app_settings');
        
        $redirect = $request->input('redirect', url()->previous());
        return redirect($redirect)->with('success', 'Settings updated successfully.');
    }

    public function general()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.general', compact('settings'));
    }

    public function store()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.store', compact('settings'));
    }

    public function email()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.email', compact('settings'));
    }

    public function sms()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.sms', compact('settings'));
    }

    public function payment()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.payment', compact('settings'));
    }

    public function shipping()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.shipping', compact('settings'));
    }

    public function seo()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.seo', compact('settings'));
    }

    public function social()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        
        // Get all social links from settings
        $socialLinks = [];
        foreach ($settings as $key => $value) {
            if (str_starts_with($key, 'social_') && !empty($value)) {
                $platform = substr($key, 7);
                $socialLinks[$platform] = $value;
            }
        }
        
        return view('admin.settings.social', compact('settings', 'socialLinks'));
    }

    public function footer()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        
        // Parse footer links from settings (stored as JSON)
        $footerSupportLinks = json_decode($settings['footer_support_links'] ?? '[]', true);
        $footerCompanyLinks = json_decode($settings['footer_company_links'] ?? '[]', true);
        $footerTrustBadges = json_decode($settings['footer_trust_badges'] ?? '[]', true);
        $footerPaymentMethods = json_decode($settings['footer_payment_methods'] ?? '["Visa","Mastercard","bKash","Nagad"]', true);
        
        return view('admin.settings.footer', compact(
            'settings', 
            'footerSupportLinks', 
            'footerCompanyLinks',
            'footerTrustBadges',
            'footerPaymentMethods'
        ));
    }

    /**
     * Send test SMS from admin panel
     */
    public function testSms(Request $request)
    {
        $validated = $request->validate([
            'mobile' => 'required|string|min:10|max:15',
            'message' => 'required|string|max:255',
        ]);

        $smsService = app(\App\Services\MuthobartaSMSService::class);
        $result = $smsService->sendSMS($validated['mobile'], $validated['message']);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'SMS queued successfully',
                'data' => $result['data'] ?? null,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to send SMS',
        ], 500);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        // Delete old logo file if it exists locally
        $oldUrl = Setting::get('site_logo_url');
        if ($oldUrl && str_contains($oldUrl, '/storage/')) {
            $oldPath = str_replace(Storage::disk('public')->url(''), '', $oldUrl);
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $path = $request->file('logo')->store('settings', 'public');
        $url = parse_url(Storage::disk('public')->url($path), PHP_URL_PATH);

        Setting::set('site_logo_url', $url, 'site');

        return redirect()->back()->with('success', 'Logo uploaded successfully.');
    }

    public function uploadFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|mimes:ico,png,jpg,svg,webp|max:512',
        ]);

        // Delete old favicon file if it exists locally
        $oldUrl = Setting::get('site_favicon');
        if ($oldUrl && str_contains($oldUrl, '/storage/')) {
            $oldPath = str_replace(Storage::disk('public')->url(''), '', $oldUrl);
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $path = $request->file('favicon')->store('settings', 'public');
        $url = parse_url(Storage::disk('public')->url($path), PHP_URL_PATH);

        Setting::set('site_favicon', $url, 'site');

        return redirect()->back()->with('success', 'Favicon uploaded successfully.');
    }

    public function clearCache()
    {
        Cache::forget('app_settings');
        
        return redirect()->back()->with('success', 'Cache cleared successfully.');
    }
}
