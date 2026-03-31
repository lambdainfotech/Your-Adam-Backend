<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::with('creator')->withCount('products');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            if ($request->status === 'running') {
                $query->active();
            } elseif ($request->status === 'upcoming') {
                $query->upcoming();
            } elseif ($request->status === 'ended') {
                $query->ended();
            }
        }
        
        $campaigns = $query->orderBy('starts_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        return view('admin.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $discountTypes = ['percentage' => 'Percentage', 'fixed' => 'Fixed Amount'];
        return view('admin.campaigns.create', compact('discountTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|string|max:500',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'apply_to_all' => 'boolean',
            'is_active' => 'boolean',
        ]);
        
        $validated['slug'] = Str::slug($validated['name']) . '-' . time();
        $validated['created_by'] = auth()->id();
        $validated['apply_to_all'] = $request->boolean('apply_to_all', false);
        $validated['is_active'] = $request->boolean('is_active', true);
        
        Campaign::create($validated);
        
        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campaign created successfully.');
    }

    public function show(Campaign $campaign)
    {
        $campaign->load(['products', 'creator']);
        return view('admin.campaigns.show', compact('campaign'));
    }

    public function edit(Campaign $campaign)
    {
        $discountTypes = ['percentage' => 'Percentage', 'fixed' => 'Fixed Amount'];
        return view('admin.campaigns.edit', compact('campaign', 'discountTypes'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|string|max:500',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'apply_to_all' => 'boolean',
            'is_active' => 'boolean',
        ]);
        
        $validated['apply_to_all'] = $request->boolean('apply_to_all', false);
        $validated['is_active'] = $request->boolean('is_active', true);
        
        $campaign->update($validated);
        
        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campaign updated successfully.');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        
        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }

    public function manageProducts(Campaign $campaign)
    {
        $campaign->load('products');
        $products = Product::active()->select('id', 'name', 'base_price')->get();
        
        return view('admin.campaigns.products', compact('campaign', 'products'));
    }

    public function updateProducts(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.special_price' => 'nullable|numeric|min:0',
        ]);
        
        $syncData = [];
        foreach ($validated['products'] as $product) {
            $syncData[$product['id']] = [
                'special_price' => $product['special_price'] ?? null,
            ];
        }
        
        $campaign->products()->sync($syncData);
        
        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campaign products updated successfully.');
    }

    public function toggleStatus(Campaign $campaign)
    {
        $campaign->update(['is_active' => !$campaign->is_active]);
        
        $status = $campaign->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Campaign {$status} successfully.");
    }
}
