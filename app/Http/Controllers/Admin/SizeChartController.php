<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SizeChart;
use App\Models\Category;
use Illuminate\Http\Request;

class SizeChartController extends Controller
{
    public function index(Request $request)
    {
        $query = SizeChart::with(['category', 'subCategory']);
        
        if ($request->filled('category_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('category_id', $request->category_id)
                  ->orWhere('sub_category_id', $request->category_id);
            });
        }
        
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $sizeCharts = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Category::where('is_active', true)->with('children')->whereNull('parent_id')->get();
        
        return view('admin.size-charts.index', compact('sizeCharts', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->with('children')->whereNull('parent_id')->get();
        $units = ['inch' => 'Inch', 'cm' => 'Centimeter'];
        $sizeTypes = ['asian' => 'Asian Size', 'european' => 'European Size'];
        
        return view('admin.size-charts.create', compact('categories', 'units', 'sizeTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:100',
            'unit' => 'required|in:inch,cm',
            'size_type' => 'required|in:asian,european',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'rows' => 'required|array|min:1',
            'rows.*.size_name' => 'required|string|max:50',
            'rows.*.measurements' => 'required|array',
        ]);
        
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated = $this->resolveCategoryIds($validated);
        
        $sizeChart = SizeChart::create($validated);
        
        // Create rows
        foreach ($validated['rows'] as $index => $row) {
            $sizeChart->rows()->create([
                'size_name' => $row['size_name'],
                'measurements' => $row['measurements'],
                'sort_order' => $index,
            ]);
        }
        
        return redirect()->route('admin.size-charts.index')
            ->with('success', 'Size chart created successfully.');
    }

    public function show(SizeChart $sizeChart)
    {
        $sizeChart->load(['category', 'rows']);
        return view('admin.size-charts.show', compact('sizeChart'));
    }

    public function edit(SizeChart $sizeChart)
    {
        $sizeChart->load('rows');
        $categories = Category::where('is_active', true)->with('children')->whereNull('parent_id')->get();
        $units = ['inch' => 'Inch', 'cm' => 'Centimeter'];
        $sizeTypes = ['asian' => 'Asian Size', 'european' => 'European Size'];
        
        return view('admin.size-charts.edit', compact('sizeChart', 'categories', 'units', 'sizeTypes'));
    }

    public function update(Request $request, SizeChart $sizeChart)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:100',
            'unit' => 'required|in:inch,cm',
            'size_type' => 'required|in:asian,european',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'rows' => 'required|array|min:1',
            'rows.*.size_name' => 'required|string|max:50',
            'rows.*.measurements' => 'required|array',
        ]);
        
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated = $this->resolveCategoryIds($validated);
        
        $sizeChart->update($validated);
        
        // Sync rows
        $sizeChart->rows()->delete();
        foreach ($validated['rows'] as $index => $row) {
            $sizeChart->rows()->create([
                'size_name' => $row['size_name'],
                'measurements' => $row['measurements'],
                'sort_order' => $index,
            ]);
        }
        
        return redirect()->route('admin.size-charts.index')
            ->with('success', 'Size chart updated successfully.');
    }

    public function destroy(SizeChart $sizeChart)
    {
        $sizeChart->delete();
        
        return redirect()->route('admin.size-charts.index')
            ->with('success', 'Size chart deleted successfully.');
    }

    public function toggleStatus(SizeChart $sizeChart)
    {
        $sizeChart->update(['is_active' => !$sizeChart->is_active]);
        
        $status = $sizeChart->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Size chart {$status} successfully.");
    }

    /**
     * Resolve category_id and sub_category_id from the selected category.
     * If user selects a sub-category, set category_id to parent and sub_category_id to selected.
     * If user selects a leaf main category, keep category_id and set sub_category_id to null.
     */
    private function resolveCategoryIds(array $validated): array
    {
        $selectedCategoryId = $validated['category_id'];
        $category = Category::find($selectedCategoryId);

        if ($category && $category->parent_id !== null) {
            $validated['category_id'] = $category->parent_id;
            $validated['sub_category_id'] = $selectedCategoryId;
        } else {
            $validated['sub_category_id'] = null;
        }

        return $validated;
    }
}
