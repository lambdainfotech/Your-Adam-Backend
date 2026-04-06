<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PredefinedDescription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PredefinedDescriptionController extends Controller
{
    public function index()
    {
        $descriptions = PredefinedDescription::descriptions()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
            
        $shortDescriptions = PredefinedDescription::shortDescriptions()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.predefined-descriptions.index', compact('descriptions', 'shortDescriptions'));
    }

    public function create()
    {
        return view('admin.predefined-descriptions.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:description,short_description',
            'name' => 'required|string|max:100|unique:predefined_descriptions',
            'content' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = PredefinedDescription::byType($validated['type'])->max('sort_order') + 1;

        PredefinedDescription::create($validated);

        return redirect()->route('admin.predefined-descriptions.index')
            ->with('success', 'Predefined description created successfully.');
    }

    public function edit(PredefinedDescription $predefinedDescription)
    {
        return view('admin.predefined-descriptions.form', compact('predefinedDescription'));
    }

    public function update(Request $request, PredefinedDescription $predefinedDescription)
    {
        $validated = $request->validate([
            'type' => 'required|in:description,short_description',
            'name' => 'required|string|max:100|unique:predefined_descriptions,name,' . $predefinedDescription->id,
            'content' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $predefinedDescription->update($validated);

        return redirect()->route('admin.predefined-descriptions.index')
            ->with('success', 'Predefined description updated successfully.');
    }

    public function destroy(PredefinedDescription $predefinedDescription)
    {
        // Check if any products are using this description
        $productsCount = $predefinedDescription->products_count;
        
        if ($productsCount > 0) {
            return redirect()->route('admin.predefined-descriptions.index')
                ->with('error', "Cannot delete. This description is used by {$productsCount} product(s).");
        }

        $predefinedDescription->delete();

        return redirect()->route('admin.predefined-descriptions.index')
            ->with('success', 'Predefined description deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:predefined_descriptions,id',
            'items.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['items'] as $item) {
            PredefinedDescription::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    public function getByType(Request $request)
    {
        $type = $request->get('type', 'description');
        
        $descriptions = PredefinedDescription::byType($type)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'content']);

        return response()->json($descriptions);
    }
}
