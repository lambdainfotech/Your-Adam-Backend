<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::ordered()->paginate(20);
        return view('admin.sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('admin.sliders.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'title_color' => 'required|string|max:7',
            'subtitle_color' => 'required|string|max:7',
            'banner_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'button_text' => 'nullable|string|max:50',
            'button_url' => 'nullable|string|max:500',
            'button_text_color' => 'required|string|max:7',
            'button_bg_color' => 'required|string|max:7',
            'is_active' => 'boolean',
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('sliders', 'public');
            $validated['banner_image'] = $path;
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = Slider::max('sort_order') + 1;

        Slider::create($validated);

        return redirect()->route('admin.sliders.index')
            ->with('success', 'Slider created successfully.');
    }

    public function edit(Slider $slider)
    {
        return view('admin.sliders.form', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'title_color' => 'required|string|max:7',
            'subtitle_color' => 'required|string|max:7',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'button_text' => 'nullable|string|max:50',
            'button_url' => 'nullable|string|max:500',
            'button_text_color' => 'required|string|max:7',
            'button_bg_color' => 'required|string|max:7',
            'is_active' => 'boolean',
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            // Delete old image
            if ($slider->banner_image) {
                Storage::disk('public')->delete($slider->banner_image);
            }
            $path = $request->file('banner_image')->store('sliders', 'public');
            $validated['banner_image'] = $path;
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $slider->update($validated);

        return redirect()->route('admin.sliders.index')
            ->with('success', 'Slider updated successfully.');
    }

    public function destroy(Slider $slider)
    {
        // Delete banner image
        if ($slider->banner_image) {
            Storage::disk('public')->delete($slider->banner_image);
        }

        $slider->delete();

        return redirect()->route('admin.sliders.index')
            ->with('success', 'Slider deleted successfully.');
    }

    public function toggleStatus(Slider $slider)
    {
        $slider->update(['is_active' => !$slider->is_active]);

        $status = $slider->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Slider {$status} successfully.");
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:sliders,id',
            'items.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['items'] as $item) {
            Slider::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
