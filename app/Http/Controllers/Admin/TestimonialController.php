<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    protected ?string $permissionModule = 'testimonials';

    protected array $customActionMap = [
        'toggleStatus' => 'edit',
    ];

    public function index()
    {
        if ($redirect = $this->authorizeAction()) {
            return $redirect;
        }

        $testimonials = Testimonial::orderBy('sort_order')->get();
        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        if ($redirect = $this->authorizeAction()) {
            return $redirect;
        }

        return view('admin.testimonials.create');
    }

    public function store(Request $request)
    {
        if ($redirect = $this->authorizeAction()) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'avatar' => 'nullable|url|max:500',
            'content' => 'required|string',
            'rating' => 'required|numeric|min:1|max:5',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Testimonial::create($validated);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial created successfully.');
    }

    public function edit(Testimonial $testimonial)
    {
        if ($redirect = $this->authorizeAction()) {
            return $redirect;
        }

        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        if ($redirect = $this->authorizeAction()) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'avatar' => 'nullable|url|max:500',
            'content' => 'required|string',
            'rating' => 'required|numeric|min:1|max:5',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $testimonial->update($validated);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial)
    {
        if ($redirect = $this->authorizeAction()) {
            return $redirect;
        }

        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial deleted successfully.');
    }

    public function toggleStatus(Testimonial $testimonial)
    {
        if ($redirect = $this->authorizeAction()) {
            return $redirect;
        }

        $testimonial->is_active = !$testimonial->is_active;
        $testimonial->save();

        $status = $testimonial->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.testimonials.index')
            ->with('success', "Testimonial {$status} successfully.");
    }
}
