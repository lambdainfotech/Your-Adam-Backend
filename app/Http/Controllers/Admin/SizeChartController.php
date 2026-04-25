<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SizeChart;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class SizeChartController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }
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
        $categories = $this->categoryService->getHierarchicalCategories();
        
        return view('admin.size-charts.index', compact('sizeCharts', 'categories'));
    }

    public function create()
    {
        $categories = $this->categoryService->getHierarchicalCategories();
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
        $validated = $this->categoryService->resolveCategoryIds($validated);
        
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
        $categories = $this->categoryService->getHierarchicalCategories();
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
        $validated = $this->categoryService->resolveCategoryIds($validated);
        
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


}
