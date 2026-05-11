<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = District::query();

        // Filter by active status (default: only active)
        if ($request->boolean('all', false)) {
            // Return all districts including inactive
        } else {
            $query->where('is_active', true);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $districts = $query->orderBy('name')->get(['id', 'name', 'is_active']);

        return response()->json([
            'success' => true,
            'message' => 'Districts retrieved successfully',
            'data' => $districts,
        ]);
    }
}
