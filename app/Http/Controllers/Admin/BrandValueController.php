<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandValue;
use Illuminate\Http\Request;

class BrandValueController extends Controller
{
    public function index()
    {
        $brandValues = BrandValue::orderBy('sort_order')->get();
        return view('admin.brand-values.index', compact('brandValues'));
    }

    public function create()
    {
        return view('admin.brand-values.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'icon' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        BrandValue::create($validated);

        return redirect()->route('admin.brand-values.index')
            ->with('success', 'Brand value created successfully.');
    }

    public function edit(BrandValue $brandValue)
    {
        return view('admin.brand-values.edit', compact('brandValue'));
    }

    public function update(Request $request, BrandValue $brandValue)
    {
        $validated = $request->validate([
            'icon' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $brandValue->update($validated);

        return redirect()->route('admin.brand-values.index')
            ->with('success', 'Brand value updated successfully.');
    }

    public function destroy(BrandValue $brandValue)
    {
        $brandValue->delete();

        return redirect()->route('admin.brand-values.index')
            ->with('success', 'Brand value deleted successfully.');
    }

    public function toggleStatus(BrandValue $brandValue)
    {
        $brandValue->is_active = !$brandValue->is_active;
        $brandValue->save();

        $status = $brandValue->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.brand-values.index')
            ->with('success', "Brand value {$status} successfully.");
    }
}
