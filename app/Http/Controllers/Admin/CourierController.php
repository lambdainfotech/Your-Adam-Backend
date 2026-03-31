<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function index(Request $request)
    {
        $query = Courier::withCount('assignments');
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $couriers = $query->ordered()->paginate(20)->withQueryString();
        
        return view('admin.couriers.index', compact('couriers'));
    }

    public function create()
    {
        return view('admin.couriers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:couriers',
            'logo' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'tracking_url_template' => 'nullable|string|max:500',
            'api_config' => 'nullable|array',
            'is_active' => 'boolean',
        ]);
        
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', true);
        
        Courier::create($validated);
        
        return redirect()->route('admin.couriers.index')
            ->with('success', 'Courier created successfully.');
    }

    public function show(Courier $courier)
    {
        $courier->load(['assignments.order']);
        return view('admin.couriers.show', compact('courier'));
    }

    public function edit(Courier $courier)
    {
        return view('admin.couriers.edit', compact('courier'));
    }

    public function update(Request $request, Courier $courier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:couriers,code,' . $courier->id,
            'logo' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'tracking_url_template' => 'nullable|string|max:500',
            'api_config' => 'nullable|array',
            'is_active' => 'boolean',
        ]);
        
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', true);
        
        $courier->update($validated);
        
        return redirect()->route('admin.couriers.index')
            ->with('success', 'Courier updated successfully.');
    }

    public function destroy(Courier $courier)
    {
        if ($courier->assignments()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete courier with existing assignments.');
        }
        
        $courier->delete();
        
        return redirect()->route('admin.couriers.index')
            ->with('success', 'Courier deleted successfully.');
    }

    public function toggleStatus(Courier $courier)
    {
        $courier->update(['is_active' => !$courier->is_active]);
        
        $status = $courier->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Courier {$status} successfully.");
    }
}
