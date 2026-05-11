<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $query = District::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $districts = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.districts.index', compact('districts'));
    }

    public function create()
    {
        return view('admin.districts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:districts',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        District::create($validated);

        return redirect()->route('admin.districts.index')
            ->with('success', 'District created successfully.');
    }

    public function edit(District $district)
    {
        return view('admin.districts.edit', compact('district'));
    }

    public function update(Request $request, District $district)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:districts,name,' . $district->id,
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $district->update($validated);

        return redirect()->route('admin.districts.index')
            ->with('success', 'District updated successfully.');
    }

    public function destroy(District $district)
    {
        $district->delete();

        return redirect()->route('admin.districts.index')
            ->with('success', 'District deleted successfully.');
    }

    public function toggleStatus(District $district)
    {
        $district->update(['is_active' => !$district->is_active]);

        $status = $district->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "District {$status} successfully.");
    }
}
