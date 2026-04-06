<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::with('creator')->withCount(['products', 'categories']);
        
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
        $products = Product::active()->select('id', 'name', 'base_price')->get();
        $categories = Category::active()->select('id', 'name')->get();
        
        return view('admin.campaigns.create', compact('discountTypes', 'products', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'apply_type' => 'required|in:all,products,categories',
            'selected_products' => 'required_if:apply_type,products|array',
            'selected_products.*' => 'exists:products,id',
            'selected_categories' => 'required_if:apply_type,categories|array',
            'selected_categories.*' => 'exists:categories,id',
            'is_active' => 'boolean',
        ]);
        
        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('campaigns', 'public');
            $validated['banner_image'] = $path;
        }
        
        $validated['slug'] = Str::slug($validated['name']) . '-' . time();
        $validated['created_by'] = auth()->id();
        $validated['apply_to_all'] = $validated['apply_type'] === 'all';
        $validated['is_active'] = $request->boolean('is_active', true);
        
        $campaign = Campaign::create($validated);
        
        // Sync products if apply_type is products
        if ($validated['apply_type'] === 'products' && !empty($validated['selected_products'])) {
            $campaign->products()->sync($validated['selected_products']);
        }
        
        // Sync categories if apply_type is categories
        if ($validated['apply_type'] === 'categories' && !empty($validated['selected_categories'])) {
            $campaign->categories()->sync($validated['selected_categories']);
        }
        
        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campaign created successfully.');
    }

    public function show(Campaign $campaign)
    {
        $campaign->load(['products', 'categories', 'creator']);
        return view('admin.campaigns.show', compact('campaign'));
    }

    public function edit(Campaign $campaign)
    {
        $campaign->load(['products', 'categories']);
        $discountTypes = ['percentage' => 'Percentage', 'fixed' => 'Fixed Amount'];
        $products = Product::active()->select('id', 'name', 'base_price')->get();
        $categories = Category::active()->select('id', 'name')->get();
        
        $selectedProducts = $campaign->products->pluck('id')->toArray();
        $selectedCategories = $campaign->categories->pluck('id')->toArray();
        
        return view('admin.campaigns.edit', compact('campaign', 'discountTypes', 'products', 'categories', 'selectedProducts', 'selectedCategories'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'apply_type' => 'required|in:all,products,categories',
            'selected_products' => 'required_if:apply_type,products|array',
            'selected_products.*' => 'exists:products,id',
            'selected_categories' => 'required_if:apply_type,categories|array',
            'selected_categories.*' => 'exists:categories,id',
            'is_active' => 'boolean',
        ]);
        
        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            // Delete old image if exists
            if ($campaign->banner_image) {
                Storage::disk('public')->delete($campaign->banner_image);
            }
            $path = $request->file('banner_image')->store('campaigns', 'public');
            $validated['banner_image'] = $path;
        }
        
        $validated['apply_to_all'] = $validated['apply_type'] === 'all';
        $validated['is_active'] = $request->boolean('is_active', true);
        
        $campaign->update($validated);
        
        // Sync products based on apply_type
        if ($validated['apply_type'] === 'products') {
            $campaign->products()->sync($validated['selected_products'] ?? []);
        } else {
            $campaign->products()->detach();
        }
        
        // Sync categories based on apply_type
        if ($validated['apply_type'] === 'categories') {
            $campaign->categories()->sync($validated['selected_categories'] ?? []);
        } else {
            $campaign->categories()->detach();
        }
        
        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campaign updated successfully.');
    }

    public function destroy(Campaign $campaign)
    {
        // Delete banner image if exists
        if ($campaign->banner_image) {
            Storage::disk('public')->delete($campaign->banner_image);
        }
        
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
