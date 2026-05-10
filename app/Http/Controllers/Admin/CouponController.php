<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::with('creator')->withCount('usages');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'expired') {
                $query->expired();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        $coupons = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        $types = ['percentage' => 'Percentage', 'fixed' => 'Fixed Amount'];
        
        return view('admin.coupons.index', compact('coupons', 'types'));
    }

    public function create()
    {
        $types = ['percentage' => 'Percentage', 'fixed' => 'Fixed Amount'];
        return view('admin.coupons.create', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit_per_user' => 'required|integer|min:1',
            'total_usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
        ]);
        
        $validated['code'] = Str::upper($validated['code']);
        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active', true);
        
        Coupon::create($validated);
        
        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully.');
    }

    public function show(Coupon $coupon)
    {
        $coupon->load(['usages.user', 'creator']);
        return view('admin.coupons.show', compact('coupon'));
    }

    public function edit(Coupon $coupon)
    {
        $types = ['percentage' => 'Percentage', 'fixed' => 'Fixed Amount'];
        return view('admin.coupons.edit', compact('coupon', 'types'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit_per_user' => 'required|integer|min:1',
            'total_usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
        ]);
        
        $validated['code'] = Str::upper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', true);
        
        $coupon->update($validated);
        
        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        
        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully.');
    }

    public function toggleStatus(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);
        
        $status = $coupon->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Coupon {$status} successfully.");
    }
}
