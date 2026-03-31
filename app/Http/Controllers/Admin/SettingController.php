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
            Setting::set($key, $value);
        }
        
        // Clear settings cache
        Cache::forget('app_settings');
        
        return redirect()->back()->with('success', 'Settings updated successfully.');
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

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        
        $path = $request->file('logo')->store('public/settings');
        $url = Storage::url($path);
        
        Setting::set('store_logo', $url);
        
        return redirect()->back()->with('success', 'Logo uploaded successfully.');
    }

    public function uploadFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|image|mimes:ico,png|max:512',
        ]);
        
        $path = $request->file('favicon')->store('public/settings');
        $url = Storage::url($path);
        
        Setting::set('store_favicon', $url);
        
        return redirect()->back()->with('success', 'Favicon uploaded successfully.');
    }

    public function clearCache()
    {
        Cache::forget('app_settings');
        
        return redirect()->back()->with('success', 'Cache cleared successfully.');
    }
}
