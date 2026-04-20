<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SizeChart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SizeChartController extends Controller
{
    /**
     * List all active size charts
     */
    public function index(Request $request): JsonResponse
    {
        $query = SizeChart::with(['category', 'rows'])
            ->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('category_slug')) {
            $category = Category::where('slug', $request->category_slug)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        $sizeCharts = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $sizeCharts->map(fn ($chart) => $this->transformSizeChart($chart)),
        ]);
    }

    /**
     * Get single size chart with full rows
     */
    public function show(int $id): JsonResponse
    {
        $sizeChart = SizeChart::with(['category', 'rows'])
            ->where('is_active', true)
            ->find($id);

        if (!$sizeChart) {
            return response()->json([
                'success' => false,
                'message' => 'Size chart not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformSizeChart($sizeChart, true),
        ]);
    }

    /**
     * Get size charts by category slug
     */
    public function byCategory(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $sizeCharts = SizeChart::with(['rows'])
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
                'sizeCharts' => $sizeCharts->map(fn ($chart) => $this->transformSizeChart($chart, true)),
            ],
        ]);
    }

    /**
     * Transform size chart to API format
     */
    private function transformSizeChart(SizeChart $chart, bool $withRows = false): array
    {
        $data = [
            'id' => $chart->id,
            'name' => $chart->name,
            'unit' => $chart->unit,
            'sizeType' => $chart->size_type,
            'description' => $chart->description,
            'isActive' => $chart->is_active,
            'category' => $chart->category ? [
                'id' => $chart->category->id,
                'name' => $chart->category->name,
                'slug' => $chart->category->slug,
            ] : null,
        ];

        if ($withRows && $chart->relationLoaded('rows')) {
            $data['rows'] = $chart->rows->map(function ($row) {
                return [
                    'id' => $row->id,
                    'sizeName' => $row->size_name,
                    'measurements' => $row->measurements,
                    'sortOrder' => $row->sort_order,
                ];
            });

            // Extract measurement keys (column headers) from first row
            if ($chart->rows->isNotEmpty()) {
                $firstRowMeasurements = $chart->rows->first()->measurements ?? [];
                $data['measurementColumns'] = array_keys((array) $firstRowMeasurements);
            } else {
                $data['measurementColumns'] = [];
            }
        }

        return $data;
    }
}
